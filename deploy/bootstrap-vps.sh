#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# Al Ghazzali Institut : préparation du VPS au déploiement par image (UNE fois, en root)
#
#   ssh root@<vps> mkdir -p /root/alghazali-bootstrap
#   scp deploy/bootstrap-vps.sh deploy/nginx-alghazali-site.conf root@<vps>:/root/alghazali-bootstrap/
#   ssh root@<vps> bash /root/alghazali-bootstrap/bootstrap-vps.sh "ssh-ed25519 AAAA… github-actions@cnxsolutions/alghazali-institut"
#
# L'argument est la clé PUBLIQUE de la CI, générée sur le poste de travail : la
# clé privée ne passe jamais par le serveur (voir docs/deploiement.md).
# nginx-alghazali-site.conf doit être posé À CÔTÉ du script : le serveur n'a pas de
# clone du dépôt.
#
# Avant de modifier quoi que ce soit, il vérifie que rien n'occupe déjà la
# place (uid 9007, dossier, port, base, vhost du domaine) et s'arrête au
# moindre doute : le VPS est partagé avec l'application Madrasapp,
# madrasapp.com, cnx-solutions.fr, Remoraid, seo-engine et Sillonn.
#
# alghazali-institut.fr arrive neuf sur ce VPS : aucun vhost ne doit déjà le
# servir. Le vhost du site est activé en HTTP seul ; le certificat s'ajoute
# avec certbot dès que le DNS du domaine pointe sur le VPS.
#
# Idempotent : le relancer ne casse rien. Ce qui existe est gardé tel quel :
# le .env et ses mots de passe, le vhost (que certbot a peut-être déjà complété
# pour le TLS), les clés SSH déjà autorisées. Il ne démarre AUCUN conteneur ;
# c'est le premier passage de la CI qui le fait.
# ─────────────────────────────────────────────────────────────────────────────
set -euo pipefail

die() { echo "ERREUR : $*" >&2; exit 1; }

[[ $EUID -eq 0 ]] || die "à lancer en root."

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VHOST_SRC="$HERE/nginx-alghazali-site.conf"
CI_PUBKEY="${1:-}"

APP=alghazali-site
APP_UID=9007
FPM_PORT=9007
DOMAIN=alghazali-institut.fr
HOST_DIR=/var/www/alghazali-site
ENV_FILE=$HOST_DIR/.env
APP_DIR=/opt/apps/$APP
BACKUP_DIR=/var/backups/alghazali-site
DB=alghazali_site
DB_USER=alghazali_site
VHOST=/etc/nginx/sites-available/$APP
DEPLOY_USER=deploy
DEPLOY_HOME="/home/$DEPLOY_USER"
REPO=cnxsolutions/alghazali-institut
MYSQL_SOCKET=/var/run/mysqld/mysqld.sock
STAMP="$(date +%Y%m%d-%H%M%S)"

# ─── Préalables : ce script prépare, il n'installe rien ─────────────────────
[[ -n "$CI_PUBKEY" ]] || die "usage : $0 \"<clé publique de la CI>\" (voir l'en-tête)."
[[ -f "$VHOST_SRC" ]] || die "vhost absent : $VHOST_SRC (poser nginx-alghazali-site.conf à côté du script)."
id "$DEPLOY_USER" >/dev/null 2>&1 || die "l'utilisateur $DEPLOY_USER n'existe pas."
id -nG "$DEPLOY_USER" | grep -qw docker || die "$DEPLOY_USER n'est pas membre du groupe docker."
getent group www-data >/dev/null || die "le groupe www-data n'existe pas."
for cmd in mysql nginx docker openssl ssh-keygen ss setpriv; do
  command -v "$cmd" >/dev/null || die "commande introuvable : $cmd"
done
[[ -S "$MYSQL_SOCKET" ]] || die "socket MySQL absent : $MYSQL_SOCKET"
# Les conteneurs joignent MySQL sous l'uid de l'app : il lui faut traverser
# /var/run/mysqld et écrire sur le socket. Sans quoi WordPress ne verrait
# qu'une « erreur de connexion à la base », et deploy.sh refuserait de basculer.
setpriv --reuid="$APP_UID" --regid="$APP_UID" --clear-groups test -w "$MYSQL_SOCKET" \
  || die "l'uid $APP_UID ne peut pas joindre $MYSQL_SOCKET ($(stat -c '%a %U:%G' "$(dirname "$MYSQL_SOCKET")") sur $(dirname "$MYSQL_SOCKET"))."

# Une seule ligne, et une clé publique : une clé privée collée par erreur
# n'a rien à faire dans authorized_keys (ni dans l'historique du shell).
[[ "$CI_PUBKEY" != *$'\n'* && "$CI_PUBKEY" != *PRIVATE* ]] \
  || die "l'argument doit être la clé PUBLIQUE (une ligne « ssh-ed25519 AAAA… »)."
KEY_TMP="$(mktemp)"
trap 'rm -f "$KEY_TMP"' EXIT
printf '%s\n' "$CI_PUBKEY" > "$KEY_TMP"
ssh-keygen -l -f "$KEY_TMP" >/dev/null 2>&1 || die "l'argument n'est pas une clé publique SSH valide."

# Aléa en hexadécimal : ces valeurs passent par le .env, lu ligne à ligne par
# wp-config.php, et par un fichier d'options MySQL. Un « # », un « $ »
# ou un guillemet y casseraient quelque chose quelque part.
rand() { openssl rand -hex "${1:-24}"; }

# Lit une clé d'un fichier .env (guillemets doubles retirés). Vide si absente.
env_get() {
  [[ -f "$1" ]] || return 0
  { grep -E "^$2=" "$1" || true; } | tail -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'
}

# Ajoute KEY=VALUE si la clé est absente ; une clé présente n'est jamais
# réécrite, même vide (une valeur vidée à la main l'a été exprès).
env_default() {
  grep -qE "^$2=" "$1" || printf '%s=%s\n' "$2" "$3" >> "$1"
}

sql_scalar() { mysql --protocol=socket -S "$MYSQL_SOCKET" -N -B -e "$1"; }

# ─── Conflits : tout est vérifié AVANT la première modification ─────────────
echo "== vérifications"
PREMIER_PASSAGE=0
[[ -f "$ENV_FILE" ]] || PREMIER_PASSAGE=1

if id -u "$APP" >/dev/null 2>&1; then
  [[ "$(id -u "$APP")" == "$APP_UID" ]] || die "$APP existe déjà avec un autre uid que $APP_UID."
else
  if getent passwd "$APP_UID" >/dev/null; then
    die "l'uid $APP_UID est déjà pris par $(getent passwd "$APP_UID" | cut -d: -f1)."
  fi
  if getent group "$APP_UID" >/dev/null; then
    die "le gid $APP_UID est déjà pris par le groupe $(getent group "$APP_UID" | cut -d: -f1)."
  fi
fi

# Un dossier qui existe sans appartenir à l'app vient d'ailleurs : on ne le
# reprend pas d'office.
if [[ -e "$HOST_DIR" && "$(stat -c %u "$HOST_DIR")" != "$APP_UID" ]]; then
  die "$HOST_DIR existe déjà et n'appartient pas à $APP ($(stat -c %U "$HOST_DIR")) : à examiner à la main."
fi

# Le port est légitimement pris par notre propre conteneur après un déploiement.
if [[ -n "$(ss -ltnH "( sport = :$FPM_PORT )")" ]] \
   && ! docker ps --format '{{.Names}}' | grep -qx "$APP-fpm"; then
  die "le port $FPM_PORT écoute déjà, et pas pour $APP-fpm : $(ss -ltnpH "( sport = :$FPM_PORT )")"
fi

# Un autre vhost actif qui déclare déjà le domaine ou www : le domaine est
# neuf, ce vhost n'a pas été posé ici. Deux vhosts se disputeraient le nom, et
# nginx servirait l'un ou l'autre selon l'ordre de lecture.
for f in /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*.conf; do
  [[ -f "$f" ]] || continue
  [[ "$(readlink -f "$f")" == "$(readlink -f "$VHOST")" ]] && continue
  if grep -qE "^[^#]*server_name[^;]*[[:space:]](www\.)?${DOMAIN//./\\.}[[:space:];]" "$f"; then
    die "$f sert déjà $DOMAIN : à examiner à la main."
  fi
done

# Au premier passage, la base et son utilisateur ne doivent pas exister : le
# .env est écrit AVANT eux, une base sans .env n'a donc pas été créée ici.
if [[ "$PREMIER_PASSAGE" == 1 ]]; then
  [[ -z "$(sql_scalar "SELECT 1 FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='$DB'")" ]] \
    || die "la base $DB existe déjà alors que $ENV_FILE n'existe pas : à examiner à la main."
  [[ -z "$(sql_scalar "SELECT 1 FROM mysql.user WHERE user='$DB_USER' AND host='localhost'")" ]] \
    || die "l'utilisateur MySQL $DB_USER@localhost existe déjà alors que $ENV_FILE n'existe pas."
fi
echo "  rien ne gêne"

# ─── Utilisateur système ─────────────────────────────────────────────────────
# Les conteneurs tournent sous un uid numérique ; le compte n'existe que pour
# que cet uid ait un nom dans `ls -l` et ne soit jamais attribué à quelqu'un
# d'autre. Pas de shell, pas de home.
echo "== $APP"
if ! id -u "$APP" >/dev/null 2>&1; then
  groupadd --system --gid "$APP_UID" "$APP"
  useradd --system --uid "$APP_UID" --gid "$APP_UID" --no-create-home \
    --home-dir /nonexistent --shell /usr/sbin/nologin "$APP"
  echo "  utilisateur $APP ($APP_UID) créé"
fi

# ─── Dossier hôte : .env, public/, uploads/ ──────────────────────────────────
# 2750, propriétaire l'app, groupe www-data, bit setgid : nginx (www-data) lit
# public/ et uploads/, personne d'autre n'entre. Le setgid fait hériter le
# groupe www-data aux fichiers que l'app crée ensuite (médias, copie publique),
# alors que l'uid de l'app n'est pas membre de ce groupe.
for d in "" /public /public/wp-content /uploads; do
  install -d "$HOST_DIR$d"
  chown "$APP_UID:www-data" "$HOST_DIR$d"
  chmod 2750 "$HOST_DIR$d"
done

# Lien RELATIF vers les médias : nginx sert /wp-content/uploads/… depuis
# uploads/, sans passer par PHP. Les conteneurs, eux, montent uploads/
# directement. docker/publier.sh ne touche jamais à ce lien.
LIEN="$HOST_DIR/public/wp-content/uploads"
if [[ -d "$LIEN" && ! -L "$LIEN" ]]; then
  rmdir "$LIEN" 2>/dev/null || die "$LIEN est un vrai dossier, non vide : les médias doivent être dans $HOST_DIR/uploads."
fi
if [[ ! -L "$LIEN" ]]; then
  ln -s ../../uploads "$LIEN"
  chown -h "$APP_UID:www-data" "$LIEN"
  echo "  lien public/wp-content/uploads -> ../../uploads posé"
elif [[ "$(readlink "$LIEN")" != ../../uploads ]]; then
  echo "  ⚠ $LIEN pointe vers $(readlink "$LIEN") et non ../../uploads"
fi

# ─── .env : ce qui existe est gardé ──────────────────────────────────────────
# Écrit AVANT la base : le mot de passe MySQL vient de lui. Une relance
# réaligne l'utilisateur MySQL sur ce fichier, jamais l'inverse.
if [[ "$PREMIER_PASSAGE" == 1 ]]; then
  cat > "$ENV_FILE" <<EOF
# Généré par deploy/bootstrap-vps.sh le $STAMP.
#
# Lu par wp-config.php à chaque exécution de WordPress : une ligne CLE=valeur,
# sans commentaire en fin de ligne. Une modification vaut dès la requête
# suivante, sans redémarrer les conteneurs.
#
# Les clés et sels ne servent qu'aux sessions : les changer déconnecte tout le
# monde, rien de plus. Les SMTP_* vides laissent les courriels en échec
# (journalisés), voir wp-content/mu-plugins/alghazali-smtp.php.
EOF
  echo "  $ENV_FILE créé"
else
  echo "  $ENV_FILE existe : valeurs gardées, clés manquantes ajoutées"
fi
DB_PASS="$(env_get "$ENV_FILE" DB_PASSWORD)"
[[ -n "$DB_PASS" ]] || DB_PASS="$(rand)"
env_default "$ENV_FILE" DB_NAME "$DB"
env_default "$ENV_FILE" DB_USER "$DB_USER"
env_default "$ENV_FILE" DB_PASSWORD "$DB_PASS"
# localhost:<socket> : WordPress (mysqli) passe alors par le socket Unix monté
# dans les conteneurs, et non par TCP.
env_default "$ENV_FILE" DB_HOST "localhost:$MYSQL_SOCKET"
env_default "$ENV_FILE" WP_HOME "https://$DOMAIN"
env_default "$ENV_FILE" WP_SITEURL "https://$DOMAIN"
for k in AUTH_KEY SECURE_AUTH_KEY LOGGED_IN_KEY NONCE_KEY AUTH_SALT SECURE_AUTH_SALT LOGGED_IN_SALT NONCE_SALT; do
  env_default "$ENV_FILE" "$k" "$(rand 32)"
done
env_default "$ENV_FILE" SMTP_HOST ""
env_default "$ENV_FILE" SMTP_PORT 587
env_default "$ENV_FILE" SMTP_SECURE tls
env_default "$ENV_FILE" SMTP_USER ""
env_default "$ENV_FILE" SMTP_PASSWORD ""
env_default "$ENV_FILE" SMTP_FROM ""
env_default "$ENV_FILE" SMTP_FROM_NAME ""
# Lisible par l'app seule : ni www-data, ni deploy (qui le vérifie par un
# conteneur jetable lancé sous l'uid de l'app, voir deploy.sh).
chown "$APP_UID:$APP_UID" "$ENV_FILE"
chmod 600 "$ENV_FILE"

# ─── Base MySQL ──────────────────────────────────────────────────────────────
# Accès par le socket uniquement : l'utilisateur n'existe que pour
# « localhost », ce qui pour MySQL veut dire le socket Unix, monté dans les
# conteneurs. Aucun port, aucun accès depuis le réseau Docker.
# ALTER USER réaligne le mot de passe sur celui du .env si on relance.
# root se connecte par auth_socket (défaut Ubuntu) ou par /root/.my.cnf.
# Interclassement : celui de wp-config.php (DB_COLLATE).
mysql --protocol=socket -S "$MYSQL_SOCKET" <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB\`.* TO '$DB_USER'@'localhost';
SQL
echo "  base $DB et utilisateur $DB_USER@localhost prêts"

# ─── /opt/apps/alghazali-site : compose et deploy.sh ───────────────────────────────
# La CI (utilisateur deploy) y réécrit le compose et deploy.sh à chaque
# déploiement. Aucun secret ici : le compose n'interpole que IMAGE et IMAGE_TAG.
install -d -m 750 -o "$DEPLOY_USER" -g "$DEPLOY_USER" "$APP_DIR"

# ─── Sauvegardes avant mise à jour du schéma ─────────────────────────────────
# Écrites par un conteneur sous l'uid de l'app, illisibles pour quiconque
# d'autre : un dump contient toute la base.
install -d -m 700 -o "$APP_UID" -g "$APP_UID" "$BACKUP_DIR"

# ─── Vhost nginx, en HTTP seul ───────────────────────────────────────────────
# Jamais réécrit une fois créé : certbot l'aura complété pour le TLS, et le
# remplacer ferait tomber le HTTPS.
if [[ ! -e "$VHOST" ]]; then
  { echo "# Posé par deploy/bootstrap-vps.sh le $STAMP."; cat "$VHOST_SRC"; } > "$VHOST"
  chmod 644 "$VHOST"
  echo "  vhost $VHOST créé (HTTP seul)"
else
  echo "  vhost $VHOST existe : laissé tel quel"
fi
# Activé dès maintenant : personne d'autre ne sert le domaine (vérifié plus
# haut), et certbot a besoin d'un vhost actif pour valider le domaine en HTTP.
# `nginx -t` AVANT le rechargement : une conf invalide ferait tomber tous les
# sites du VPS. En cas d'échec, le lien est retiré et nginx n'est pas rechargé.
if [[ ! -L "/etc/nginx/sites-enabled/$APP" ]]; then
  ln -s "$VHOST" "/etc/nginx/sites-enabled/$APP"
  if ! nginx -t; then
    rm -f "/etc/nginx/sites-enabled/$APP"
    die "configuration nginx invalide avec le vhost du site : lien retiré, nginx non rechargé."
  fi
  systemctl reload nginx
  echo "  vhost activé (HTTP seul)"
else
  echo "  vhost déjà activé"
fi

# ─── Clé SSH de la CI ────────────────────────────────────────────────────────
# Dédiée à ce dépôt : la révoquer ne coupe pas les déploiements des autres
# applications. Ajoutée à côté des clés déjà autorisées, jamais à leur place,
# après une copie de sauvegarde du fichier. Comparée sur son corps (type et
# base64) : la même clé avec un autre commentaire n'est pas ajoutée deux fois.
install -d -m 700 -o "$DEPLOY_USER" -g "$DEPLOY_USER" "$DEPLOY_HOME/.ssh"
AUTH="$DEPLOY_HOME/.ssh/authorized_keys"
KEY_BODY="$(awk '{print $1" "$2}' "$KEY_TMP")"
if [[ -f "$AUTH" ]] && grep -qF "$KEY_BODY" "$AUTH"; then
  echo "  clé de la CI déjà autorisée pour $DEPLOY_USER"
else
  if [[ -f "$AUTH" ]]; then
    cp -p "$AUTH" "$AUTH.bak-$STAMP"
    echo "  sauvegarde : $AUTH.bak-$STAMP"
    # Sans fin de ligne, la clé ajoutée se collerait à la dernière et
    # casserait les deux.
    [[ -s "$AUTH" && -n "$(tail -c1 "$AUTH")" ]] && echo >> "$AUTH"
  fi
  cat "$KEY_TMP" >> "$AUTH"
  chown "$DEPLOY_USER:$DEPLOY_USER" "$AUTH"
  chmod 600 "$AUTH"
  echo "  clé de la CI autorisée : $(ssh-keygen -l -f "$KEY_TMP")"
fi

# ─── Suite ───────────────────────────────────────────────────────────────────
echo ""
echo "════════════════════════════════════════════════════════════════════════"
echo "Terminé. Étapes restantes (détail dans docs/deploiement.md) :"
echo ""
echo "1. Secrets GitHub du dépôt $REPO, depuis le poste de travail :"
echo "     VPS_HOST, VPS_USER ($DEPLOY_USER), VPS_SSH_KEY, VPS_KNOWN_HOSTS"
echo ""
echo "2. Fusionner la PR dans main : la CI construit l'image. Son déploiement"
echo "   s'arrête sur la base vide, sans rien toucher : c'est attendu."
echo ""
echo "3. Actions > Déploiement > Run workflow, case « première installation » :"
echo "   WordPress s'installe en ligne de commande avec le contenu de départ,"
echo "   le site tourne sur le port $FPM_PORT."
echo ""
echo "4. Certificat, dès que le DNS de $DOMAIN et www pointe sur ce VPS :"
echo "     certbot --nginx -d $DOMAIN -d www.$DOMAIN --redirect"
echo ""
echo "5. Mot de passe de l'administrateur, en tant que $DEPLOY_USER :"
echo "     docker exec -it $APP-fpm wp user update alghazali-admin --prompt=user_pass"
if [[ -z "$(env_get "$ENV_FILE" SMTP_HOST)" ]]; then
  echo ""
  echo "⚠ SMTP_HOST est vide dans $ENV_FILE : les courriels du site ne partent pas."
fi
