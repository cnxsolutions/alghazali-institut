#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# Al Ghazali Institut : script de déploiement, exécuté SUR LE VPS
#
# Téléversé dans /opt/apps/alghazali-site/ à chaque passage de la CI, puis lancé
# par .github/workflows/deploy.yml. Il se lance aussi à la main (en tant que
# deploy), après un `docker login ghcr.io` :
#
#   IMAGE=ghcr.io/cnxsolutions/alghazali-institut IMAGE_TAG=sha-a1b2c3d UPDATE_DB=1 bash /opt/apps/alghazali-site/deploy.sh
#
# Même chaîne que madrasapp.com, avec deux différences :
#   - tant que le vhost du site n'a pas son certificat (le DNS du domaine doit
#     d'abord pointer sur le VPS, puis certbot), la nouvelle version est
#     vérifiée en interrogeant php-fpm directement, et non par nginx en HTTPS ;
#   - le contenu vit en base et s'édite dans l'administration : aucune
#     synchronisation de pages après la bascule. Le contenu de départ n'est
#     joué qu'à la première installation (docker/installer.sh).
#
# Ce qu'il garantit :
#   - rien n'est arrêté tant que la nouvelle image n'est pas entièrement tirée ;
#   - aucune bascule sur une base vide non installée ;
#   - les fichiers publics de la nouvelle version sont sur le disque AVANT la
#     bascule (nginx de l'hôte les sert) ;
#   - avec UPDATE_DB=1, la base est sauvegardée, puis son schéma mis à jour si
#     l'image apporte une version de WordPress plus récente ;
#   - une version qui ne répond pas (trois essais par adresse), sur /up ou sur
#     une page publiée, est retirée et la précédente remise en place, fichiers
#     publics compris. Le schéma, lui, n'est PAS ramené en arrière.
# ─────────────────────────────────────────────────────────────────────────────

set -euo pipefail

: "${IMAGE:?IMAGE manquant (ex: ghcr.io/cnxsolutions/alghazali-institut)}"
: "${IMAGE_TAG:?IMAGE_TAG manquant (ex: sha-a1b2c3d)}"
UPDATE_DB="${UPDATE_DB:-0}"
PREMIERE_INSTALLATION="${PREMIERE_INSTALLATION:-0}"

APP=alghazali-site
HOST_DIR=/var/www/alghazali-site
APP_UID=9007
DOMAIN=alghazali-institut.fr
APP_DIR=/opt/apps/$APP
BACKUP_DIR=/var/backups/alghazali-site
VHOST_ACTIF=/etc/nginx/sites-enabled/$APP
FPM=$APP-fpm
HEALTH_TIMEOUT=120
# Pages vérifiées après la bascule, au plus : de quoi couvrir chaque gabarit
# sans allonger le déploiement à mesure que le site grandit.
PAGES_MAX=40
# Client de sauvegarde : même version majeure que le MySQL de l'hôte
# (`mysqld --version`). Un mysqldump plus ancien que le serveur peut refuser
# de lire certains objets du schéma.
MYSQL_IMAGE=mysql:8.0

# Exportées une fois pour toutes : compose interpole `image:` à CHAQUE
# sous-commande, `logs` compris, précisément quand on en a besoin.
export IMAGE IMAGE_TAG

cd "$APP_DIR"
dc() { docker compose -f "$APP_DIR/docker-compose.yml" "$@"; }

# Lance une commande jetable sous l'uid de l'app, sans réseau ni capacité.
# C'est ainsi que deploy (qui n'a pas le droit d'entrer dans $HOST_DIR, en 2750
# pour l'app et www-data) touche aux fichiers de l'app, sans jamais être root.
as_app() { docker run --rm --user "$APP_UID:$APP_UID" --cap-drop ALL \
             --security-opt no-new-privileges:true --network none "$@"; }

# Copie l'arborescence publique d'une image vers $HOST_DIR/public, avec le
# script de l'image elle-même (docker/publier.sh) : il ne touche jamais au lien
# des médias et n'efface rien.
publier() {
  as_app --entrypoint publier.sh -v "$HOST_DIR/public:/dst" "$IMAGE:$1" /dst
}

# WP-CLI dans un conteneur jetable de l'image visée, avec la configuration du
# service fpm (uid, montages, lecture seule) mais sans publier son port.
wp_cli() { dc run --rm --no-deps -T fpm wp "$@"; }

# ─── Garde-fous ──────────────────────────────────────────────────────────────
# Vérifié AVANT tout `up` : si le fichier monté n'existe pas, Docker crée à sa
# place un DOSSIER .env, en root, et WordPress ne démarre plus.
if ! as_app -v "$HOST_DIR:/h:ro" --entrypoint test alpine:3 -f /h/.env; then
  echo "ERREUR : $HOST_DIR/.env est absent (voir docs/deploiement.md)." >&2
  exit 1
fi

PREVIOUS_TAG="$(docker inspect --format '{{.Config.Image}}' "$FPM" 2>/dev/null | sed 's/.*://' || true)"
echo "[deploy] $APP : ${PREVIOUS_TAG:-aucune version en service} -> $IMAGE_TAG"

echo "[deploy] téléchargement de $IMAGE:$IMAGE_TAG"
# Trois essais : la liaison IPv6 du VPS vers GHCR coupe parfois en plein
# transfert (« connection reset by peer », constaté pour cnx-solutions.fr).
for essai in 1 2 3; do
  docker pull --quiet "$IMAGE:$IMAGE_TAG" && break
  [[ "$essai" == 3 ]] && { echo "ERREUR : téléchargement impossible après 3 essais." >&2; exit 1; }
  echo "[deploy] téléchargement interrompu, nouvel essai dans 10 s"
  sleep 10
done

# ─── La nouvelle image démarre-t-elle sur cette base ? ───────────────────────
# WP-CLI charge au passage le noyau, les mu-plugins, les extensions et le
# functions.php du thème : une erreur fatale dans l'un d'eux arrête aussi le
# déploiement ici, avant la bascule, l'ancienne version restant en service.
# WP-CLI sort en 1 dans les deux cas ; son message, juste au-dessus, dit lequel.
if ! wp_cli core is-installed; then
  if [[ "$PREMIERE_INSTALLATION" != 1 ]]; then
    echo "ERREUR : WordPress ne démarre pas avec $IMAGE_TAG : base vide ou injoignable," >&2
    echo "         ou erreur fatale dans le code (message de WP-CLI ci-dessus)." >&2
    echo "         Rien n'a été modifié, ${PREVIOUS_TAG:-aucune version} reste en service." >&2
    echo "         Base vide : relancer avec la case « première installation »" >&2
    echo "         (Actions > Déploiement > Run workflow, docs/deploiement.md)." >&2
    exit 1
  fi
  # La base ne doit contenir aucune table : installer.sh le vérifie, et
  # s'arrête sinon. Rien d'autre n'a encore été touché.
  echo "[deploy] première installation de WordPress sur https://$DOMAIN"
  dc run --rm --no-deps -T fpm installer.sh "https://$DOMAIN"
elif [[ "$PREMIERE_INSTALLATION" == 1 ]]; then
  echo "[deploy] WordPress est déjà installé : « première installation » sans effet"
fi

# ─── Fichiers publics ────────────────────────────────────────────────────────
# nginx sert public/ depuis le disque de l'hôte. On y copie ceux de la nouvelle
# image avant la bascule. Les fichiers de WordPress gardent leur nom d'une
# version à l'autre : pendant les quelques secondes qui précèdent la bascule,
# l'ancien code sert déjà les nouveaux CSS et JS. C'est aussi pourquoi un
# retour arrière les recopie.
echo "[deploy] copie des fichiers publics vers $HOST_DIR/public"
publier "$IMAGE_TAG"

# ─── Sauvegarde et schéma ────────────────────────────────────────────────────
# Le schéma est mis à jour AVANT la bascule, avec la NOUVELLE image : l'ancien
# code tourne quelques secondes sur le nouveau schéma. WordPress garantit cette
# compatibilité d'une version mineure à l'autre ; l'inverse (nouveau code sur
# l'ancien schéma) afficherait « Mise à jour de la base de données nécessaire ».
#
# ⚠ Un retour arrière ramène l'ANCIENNE IMAGE, pas l'ancien schéma. C'est le
# dump ci-dessous qui sert alors.
DUMP=""
if [[ "$UPDATE_DB" == "1" ]]; then
  STAMP="$(date +%Y%m%d-%H%M%S)"
  DUMP="$BACKUP_DIR/alghazali_site-$STAMP-avant-$IMAGE_TAG.sql.gz"
  echo "[deploy] sauvegarde de la base -> $DUMP"
  # mysqldump sous l'uid de l'app pour pouvoir lire son .env, par le socket
  # local et sans réseau. Le mot de passe passe par un fichier d'options dans
  # le /tmp du conteneur jetable, jamais en argument : la ligne de commande d'un
  # conteneur se lit depuis l'hôte avec un simple `ps`.
  #   --single-transaction   instantané cohérent sans verrouiller les tables (InnoDB)
  #   --no-tablespaces       sinon mysqldump réclame le privilège global PROCESS
  #   --set-gtid-purged=OFF  dump restaurable que le serveur active les GTID ou non
  # Le dossier appartient à l'app (700) : le dump contient toute la base. On y
  # garde 14 jours de sauvegardes.
  # shellcheck disable=SC2016  # script exécuté DANS le conteneur : ses $ y sont développés, pas ici
  as_app -v /var/run/mysqld:/var/run/mysqld \
    -v "$HOST_DIR/.env:/app.env:ro" -v "$BACKUP_DIR:/backup" "$MYSQL_IMAGE" bash -euo pipefail -c '
      val() { grep -E "^$1=" /app.env | tail -1 | cut -d= -f2- | sed -e "s/^\"//" -e "s/\"\$//"; }
      out="/backup/$(basename "$1")"
      sock="$(val DB_HOST | sed -n "s/^[^:]*://p")"
      umask 077
      printf "[client]\nuser=\"%s\"\npassword=\"%s\"\nsocket=%s\n" \
        "$(val DB_USER)" "$(val DB_PASSWORD)" "${sock:-/var/run/mysqld/mysqld.sock}" > /tmp/client.cnf
      mysqldump --defaults-extra-file=/tmp/client.cnf --single-transaction --quick \
        --no-tablespaces --set-gtid-purged=OFF --default-character-set=utf8mb4 \
        "$(val DB_NAME)" | gzip -c > "$out"
      echo "[deploy] sauvegarde : $(du -h "$out" | cut -f1)"
      find /backup -name "*.sql.gz" -mtime +14 -delete' bash "$DUMP"

  # Version du schéma attendue par le code de l'image, et version de la base.
  # `wp core update-db` ne tourne que si l'image est PLUS RÉCENTE : lancé par
  # un retour arrière, il réécrirait db_version à la baisse, et la version
  # suivante rejouerait des étapes de mise à jour déjà faites.
  # shellcheck disable=SC2016  # code PHP, pas du shell
  REVS="$(wp_cli eval 'global $wp_db_version; echo $wp_db_version, " ", get_option( "db_version" );')"
  read -r CODE_REV BASE_REV <<< "$REVS"
  if [[ ! "$CODE_REV" =~ ^[0-9]+$ || ! "$BASE_REV" =~ ^[0-9]+$ ]]; then
    echo "ERREUR : versions du schéma illisibles (« $REVS »)." >&2
    exit 1
  fi
  if (( CODE_REV > BASE_REV )); then
    echo "[deploy] schéma $BASE_REV -> $CODE_REV"
    wp_cli core update-db
  elif (( CODE_REV < BASE_REV )); then
    echo "[deploy] ⚠ base ($BASE_REV) plus récente que l'image ($CODE_REV) : retour arrière, schéma laissé tel quel"
  else
    echo "[deploy] schéma à jour ($BASE_REV)"
  fi
fi

# ─── Pages à vérifier ────────────────────────────────────────────────────────
# Les pages et les formations publiées (les gabarits du site), relevées avec la
# NOUVELLE image sur la base en service, plus les listes du site. Un gabarit
# cassé sur une page en ligne déclenche ainsi le retour arrière. Les adresses
# sont réduites à leur chemin : l'accueil devient « / ».
PAGES="$(wp_cli post list --post_type=page,ag_course --post_status=publish \
  --orderby=menu_order --order=ASC --posts_per_page="$PAGES_MAX" --field=url 2>/dev/null \
  | sed -E 's#^https?://[^/]+##' | grep -E '^/([a-z0-9][a-z0-9/-]*/)?$' || true)"
PAGES="$(printf '%s\n/formations/\n' "$PAGES")"
echo "[deploy] pages publiées à vérifier : $(grep -c . <<< "$PAGES" || true)"

# ─── Bascule ─────────────────────────────────────────────────────────────────
# `up -d` recrée fpm et cron sur la nouvelle image.
echo "[deploy] bascule sur $IMAGE_TAG"
dc up -d --remove-orphans

# Par nginx en HTTPS, comme un visiteur, dès que le vhost du site est activé
# ET a reçu son certificat (certbot y ajoute ssl_certificate). Avant, le
# domaine ne répond pas encore en HTTPS ici : c'est php-fpm qu'on interroge
# directement, comme le ferait nginx.
if [[ -L "$VHOST_ACTIF" ]] && grep -q ssl_certificate "$VHOST_ACTIF"; then
  VERIFICATION=nginx
else
  VERIFICATION=fpm
  echo "[deploy] pas encore de HTTPS pour $DOMAIN : vérification par php-fpm (certbot, voir docs/deploiement.md)"
fi

# Code HTTP d'un chemin du site, une fois.
code_une_fois() {
  if [[ "$VERIFICATION" == nginx ]]; then
    curl -s -o /dev/null -w '%{http_code}' --max-time 15 \
      --resolve "$DOMAIN:443:127.0.0.1" "https://$DOMAIN$1" || true
    return
  fi
  # La requête que nginx enverrait : SCRIPT_FILENAME au chemin de l'HÔTE, que
  # l'image résout par son lien. HTTPS=on, sinon WordPress redirigerait vers
  # https://. Une réponse sans en-tête « Status » avant la ligne vide qui clôt
  # les en-têtes est un 200 ; une réponse vide ou tronquée, un échec (000).
  local sortie code
  sortie="$(docker exec -e SCRIPT_FILENAME="$HOST_DIR/public/index.php" -e SCRIPT_NAME=/index.php \
    -e DOCUMENT_ROOT="$HOST_DIR/public" -e REQUEST_METHOD=GET -e REQUEST_URI="$1" \
    -e QUERY_STRING= -e SERVER_NAME="$DOMAIN" -e HTTP_HOST="$DOMAIN" \
    -e SERVER_PORT=443 -e HTTPS=on "$FPM" \
    cgi-fcgi -bind -connect 127.0.0.1:9000 2>/dev/null | tr -d '\r')" || { echo 000; return; }
  code="$(awk '/^Status:/ { print $2; exit } /^$/ { print 200; exit }' <<< "$sortie")"
  echo "${code:-000}"
}

# Trois essais à 2 s d'intervalle tant que la réponse n'est pas 200 : juste
# après la bascule, une page peut dépasser le délai ou rendre un 502 passager
# (opcache vide). Un seul raté ne doit pas ramener l'ancienne version.
code_page() {
  local code essai
  for essai in 1 2 3; do
    code="$(code_une_fois "$1")"
    [[ "$code" == 200 ]] && break
    if (( essai < 3 )); then
      echo "[deploy] $1 -> $code, nouvel essai dans 2 s" >&2
      sleep 2
    fi
  done
  echo "$code"
}

healthy() {
  local status
  for _ in $(seq 1 $((HEALTH_TIMEOUT / 2))); do
    status="$(docker inspect --format '{{.State.Health.Status}}' "$FPM" 2>/dev/null || echo absent)"
    case "$status" in
      healthy) break ;;
      unhealthy) echo "[deploy] healthcheck php-fpm en échec"; return 1 ;;
      *) sleep 2 ;;
    esac
  done
  [[ "$status" == healthy ]] || { echo "[deploy] php-fpm jamais healthy"; return 1; }

  # php-fpm qui répond ne prouve pas que WordPress fonctionne : /up (base,
  # installation, thème et extensions chargés, médias inscriptibles, voir
  # mu-plugins/alghazali-sante.php), puis chaque page publiée. Un 301 sur
  # l'accueil (WP_HOME faux) compte comme un échec.
  local chemin code
  while IFS= read -r chemin; do
    [[ -n "$chemin" ]] || continue
    code="$(code_page "$chemin")"
    echo "[deploy] $chemin -> $code ($VERIFICATION)"
    [[ "$code" == 200 ]] || return 1
  done <<< "$(printf '/up\n/\n%s\n' "$PAGES" | awk '!vu[$0]++')"
}

echo "[deploy] vérification (max ${HEALTH_TIMEOUT}s)"
if ! healthy; then
  echo ""
  echo "ERREUR : $IMAGE_TAG ne répond pas correctement. Dernières lignes :"
  dc logs --tail 60 fpm || true
  echo ""
  if [[ -n "$PREVIOUS_TAG" ]]; then
    echo "[deploy] retour arrière vers $PREVIOUS_TAG"
    publier "$PREVIOUS_TAG" || echo "[deploy] ⚠ fichiers publics de $PREVIOUS_TAG non recopiés"
    IMAGE_TAG="$PREVIOUS_TAG" dc up -d
    [[ -n "$DUMP" ]] && echo "[deploy] ⚠ le schéma n'est PAS ramené en arrière. Dump : $DUMP"
    echo "[deploy] $PREVIOUS_TAG remis en service. Le déploiement est ANNULÉ."
  else
    echo "[deploy] aucune version précédente : le site reste indisponible."
  fi
  exit 1
fi

if [[ "$VERIFICATION" == nginx ]]; then
  echo "[deploy] $IMAGE_TAG est en service sur https://$DOMAIN"
else
  echo "[deploy] $IMAGE_TAG tourne et répond ; https://$DOMAIN attend encore son DNS ou son certificat."
  echo "[deploy] Étape suivante, en root, une fois le DNS pointé : certbot --nginx -d $DOMAIN -d www.$DOMAIN"
fi

# Une semaine d'images gardée : un retour arrière reste instantané (l'image est
# déjà sur le disque) sans remplir celui du VPS.
docker image prune -f --filter "until=168h" >/dev/null 2>&1 || true
