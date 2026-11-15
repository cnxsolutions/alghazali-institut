#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# Test de fumée de l'image : le seul filet avant la production. Il n'y a pas
# d'environnement de dev, un push sur main met en ligne.
#
#   bash tests/fumee.sh <image>        ex. bash tests/fumee.sh alghazali-site:local
#
# Lancé par .github/workflows/tests.yml sur l'image qu'il vient de construire,
# et à la main sur un poste avec Docker (Linux, ou Windows avec Git Bash).
# Même test que celui de madrasapp.com, adapté au site.
#
# Il reproduit la disposition du VPS dans des VOLUMES Docker plutôt que sur le
# disque du poste : un montage depuis Windows ne connaît ni les propriétaires
# ni le bit setgid, qui sont justement ce qu'on vérifie.
#   - MySQL 8.0, joint par son socket partagé dans un volume (/var/run/mysqld) ;
#   - public/ et uploads/ en 2750, groupe www-data, setgid, avec le lien
#     public/wp-content/uploads -> ../../uploads (comme bootstrap-vps.sh) ;
#   - la copie publique faite par docker/publier.sh, comme deploy.sh ;
#   - l'installation par docker/installer.sh, comme deploy.sh en « première
#     installation » : WordPress en ligne de commande, le contenu de départ ;
#   - fpm et cron sous l'uid 9007, en lecture seule, sans capacité ;
#   - nginx avec deploy/nginx-alghazali-site.conf lui-même, devant fpm ;
#   - chaque contenu publié rendu par nginx (tests/fumee-pages.php).
# Il ne dépend d'aucun fichier hors du dépôt.
# ─────────────────────────────────────────────────────────────────────────────
set -euo pipefail

IMAGE_REF="${1:?usage : bash tests/fumee.sh <image>}"

# Git Bash convertit en chemins Windows les arguments qui commencent par « / » :
# /var/www/app deviendrait C:/Program Files/Git/var/www/app.
export MSYS_NO_PATHCONV=1

ICI="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RACINE="$(dirname "$ICI")"
P="alghazali-fumee-$$"
APP_UID=9007
WWW_GID=33                      # www-data sous Debian et Ubuntu
DOMAIN=alghazali-institut.fr
THEME=/wp-content/themes/al-ghazzali
MYSQL_IMAGE=mysql:8.0
NGINX_IMAGE=nginx:1.24          # la version d'Ubuntu 24.04, image Debian (www-data = 33)
CURL_IMAGE=curlimages/curl:8.10.1
TMP="$(mktemp -d)"

nettoyer() {
  local code=$?
  if [[ $code -ne 0 ]]; then
    echo ""
    echo "── journaux (échec, code $code)"
    for c in fpm nginx mysql; do
      echo "--- $c"; docker logs --tail 30 "$P-$c" 2>&1 || true
    done
  fi
  docker rm -f "$P-mysql" "$P-fpm" "$P-cron" "$P-nginx" >/dev/null 2>&1 || true
  docker volume rm "$P-mysqld" "$P-public" "$P-uploads" >/dev/null 2>&1 || true
  docker network rm "$P" >/dev/null 2>&1 || true
  rm -rf "$TMP"
}
trap nettoyer EXIT

etape() { echo ""; echo "── $*"; }
ok()    { echo "   ok  $*"; }
echec() { echo "   ÉCHEC  $*" >&2; exit 1; }
alea()  { od -An -tx1 -N"${1:-16}" /dev/urandom | tr -d ' \n'; }

# Chemin d'un fichier du poste tel que le démon Docker le comprend.
chemin_hote() {
  if command -v cygpath >/dev/null 2>&1; then cygpath -m "$1"; else printf '%s' "$1"; fi
}

# Regarde dans les volumes « hôte » avec les droits de root.
hote() {
  docker run --rm -v "$P-public:/h/public:nocopy" -v "$P-uploads:/h/uploads:nocopy" \
    alpine:3 sh -euc "$1"
}

# Comme un service du compose : uid de l'app, lecture seule, sans capacité,
# mêmes montages. `:nocopy` : sans lui, Docker recopierait sur un volume vide
# le propriétaire du point de montage de l'image (root), et l'app ne pourrait
# plus écrire dans uploads/.
APP=(--user "$APP_UID:$APP_UID" --init --read-only --tmpfs "/tmp:mode=1777,size=64m"
     --cap-drop ALL --security-opt no-new-privileges:true --workdir /var/www/app
     --network "$P"
     -v "$(chemin_hote "$TMP/.env"):/var/www/app/.env:ro"
     -v "$P-uploads:/var/www/app/public/wp-content/uploads:nocopy"
     -v "$P-mysqld:/var/run/mysqld:nocopy")
wp()  { docker run --rm "${APP[@]}" "$IMAGE_REF" wp "$@"; }
web() { docker run --rm --network "$P" "$CURL_IMAGE" -s --max-time 20 "$@"; }

# Code HTTP d'une adresse, servie par nginx.
code_http() { web -o /dev/null -w '%{http_code}' "$1"; }
attendu() {
  local code
  code="$(code_http "$1")"
  [[ "$code" == "$2" ]] || echec "$1 -> $code (attendu $2)"
  ok "$1 -> $code"
}

echo "Test de fumée de $IMAGE_REF"
# Tirées d'avance et en silence : leurs barres de progression noieraient le
# résultat dans le journal de la CI.
for img in "$MYSQL_IMAGE" "$NGINX_IMAGE" "$CURL_IMAGE" alpine:3; do
  docker image inspect "$img" >/dev/null 2>&1 || docker pull -q "$img" >/dev/null
done

# ─────────────────────────────────────────────────────────────────────────────
etape "Disposition de l'hôte"
docker network create "$P" >/dev/null
docker volume create "$P-mysqld" >/dev/null
docker volume create "$P-public" >/dev/null
docker volume create "$P-uploads" >/dev/null
hote "
  mkdir -p /h/public/wp-content
  chown $APP_UID:$WWW_GID /h/public /h/public/wp-content /h/uploads
  chmod 2750 /h/public /h/public/wp-content /h/uploads
  ln -s ../../uploads /h/public/wp-content/uploads
  chown -h $APP_UID:$WWW_GID /h/public/wp-content/uploads"
ok "public/ et uploads/ en 2750, groupe www-data, lien des médias posé"

DB_PASS="$(alea)"
{
  echo "# .env de test : mêmes clés que celui de bootstrap-vps.sh"
  echo "DB_NAME=alghazali_site"
  echo "DB_USER=alghazali_site"
  echo "DB_PASSWORD=$DB_PASS"
  echo "DB_HOST=localhost:/var/run/mysqld/mysqld.sock"
  echo "WP_HOME=http://$DOMAIN"
  echo "WP_SITEURL=http://$DOMAIN"
  for k in AUTH_KEY SECURE_AUTH_KEY LOGGED_IN_KEY NONCE_KEY AUTH_SALT SECURE_AUTH_SALT LOGGED_IN_SALT NONCE_SALT; do
    echo "$k=$(alea 32)"
  done
  echo "SMTP_HOST="
  # Doit arriver tel quel dans getenv() : wp-config.php lit, il n'exécute pas.
  # shellcheck disable=SC2016  # c'est précisément la chaîne littérale qu'on veut
  echo 'ALGHAZALI_FUMEE=$(touch /tmp/execute)'
  echo 'ALGHAZALI_GUILLEMETS="deux mots"'
} > "$TMP/.env"
# 644 : le fichier de test appartient à l'utilisateur du poste, l'uid 9007
# doit pouvoir le lire. Sur le VPS, c'est 600 et l'uid de l'app.
chmod 644 "$TMP/.env"

# ─────────────────────────────────────────────────────────────────────────────
etape "MySQL 8.0, par le socket"
docker run -d --name "$P-mysql" --network "$P" -e MYSQL_ROOT_PASSWORD=racine \
  -v "$P-mysqld:/var/run/mysqld" "$MYSQL_IMAGE" >/dev/null
# Par TCP et non par le socket : pendant son initialisation, l'image démarre
# un serveur temporaire SANS réseau, qui répondrait déjà sur le socket.
for i in $(seq 1 90); do
  docker exec "$P-mysql" mysqladmin ping -h127.0.0.1 -uroot -pracine --silent >/dev/null 2>&1 && break
  [[ $i == 90 ]] && echec "MySQL ne démarre pas"
  sleep 1
done
# 'localhost' comme sur le VPS : pour MySQL, c'est le socket Unix.
docker exec -i "$P-mysql" mysql -h127.0.0.1 -uroot -pracine 2>/dev/null <<SQL
CREATE DATABASE alghazali_site CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'alghazali_site'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON alghazali_site.* TO 'alghazali_site'@'localhost';
SQL
ok "base alghazali_site et utilisateur alghazali_site@localhost"

# ─────────────────────────────────────────────────────────────────────────────
etape "Copie publique (docker/publier.sh, comme deploy.sh)"
for _ in 1 2; do
  docker run --rm --user "$APP_UID:$APP_UID" --cap-drop ALL \
    --security-opt no-new-privileges:true --network none --entrypoint publier.sh \
    -v "$P-public:/dst:nocopy" "$IMAGE_REF" /dst >/dev/null
done
ok "deux passes de suite"
# shellcheck disable=SC2016  # $(…) pour le shell du conteneur
hote '
  test -f /h/public/index.php
  test -f /h/public/wp-admin/index.php
  test -f /h/public/wp-content/themes/al-ghazzali/style.css
  test -f /h/public/wp-content/plugins/al-ghazzali-institut/al-ghazzali-institut.php
  test -f /h/public/wp-content/mu-plugins/alghazali-sante.php
  test -f /h/public/wp-content/mu-plugins/alghazali-smtp.php
  test ! -e /h/public/wp-config.php
  test ! -e /h/public/tools
  test "$(readlink /h/public/wp-content/uploads)" = ../../uploads
  test -z "$(find /h/public ! -group '"$WWW_GID"' | head -1)"
  test -z "$(find /h/public -type d ! -perm -2000 | head -1)"' \
  || echec "copie publique incomplète, ou lien des médias touché, ou groupe www-data perdu"
ok "noyau, thème, extension et mu-plugins copiés, sans wp-config.php ni contenu de départ"
ok "lien des médias intact, tout en groupe www-data, dossiers en setgid"

# ─────────────────────────────────────────────────────────────────────────────
etape "Entrypoint"
if docker run --rm --user "$APP_UID:$APP_UID" --read-only "$IMAGE_REF" true 2>/dev/null; then
  echec "sans .env, le conteneur aurait dû refuser de démarrer"
fi
ok "sans .env : refus de démarrer"

# ─────────────────────────────────────────────────────────────────────────────
etape "Première installation (docker/installer.sh, comme deploy.sh)"
if wp core is-installed 2>/dev/null; then
  echec "is-installed répond oui sur une base vide"
fi
ok "base vide : is-installed répond non (c'est le garde-fou de deploy.sh)"
INSTALLATION="$(docker run --rm "${APP[@]}" "$IMAGE_REF" installer.sh "http://$DOMAIN" 2>&1)" \
  || echec "installer.sh : $INSTALLATION"
while IFS= read -r ligne; do echo "   | $ligne"; done <<< "$INSTALLATION"
[[ "$INSTALLATION" != *"assword"* ]] || echec "un mot de passe apparaît dans la sortie de l'installation"
ok "aucun mot de passe dans la sortie"
if grep -qE '(PHP )?(Fatal|Parse|Warning|Notice|Deprecated).*(themes/al-ghazzali|plugins/al-ghazzali-institut|mu-plugins/alghazali|tools/seed)' <<< "$INSTALLATION"; then
  echec "erreurs PHP du thème, de l'extension ou du contenu de départ pendant l'installation (ci-dessus)"
fi
ok "aucune erreur PHP de notre code pendant l'installation"
wp core is-installed 2>/dev/null || echec "is-installed répond non après l'installation"
ok "wp core is-installed"
DEUXIEME="$(docker run --rm "${APP[@]}" "$IMAGE_REF" installer.sh "http://$DOMAIN" 2>&1)" \
  || echec "2e passage de installer.sh : $DEUXIEME"
[[ "$DEUXIEME" == *"déjà installé"* ]] || echec "2e passage de installer.sh : $DEUXIEME"
ok "2e passage : déjà installé, rien à faire"

# Le contenu de départ ne se rejoue pas sur un site en service : il
# réécrirait les réglages et les menus de l'institut.
REJEU="$(wp eval-file /var/www/app/tools/seed.php 2>&1 || true)"
[[ "$REJEU" == *"Refusé"* ]] || echec "le contenu de départ s'est rejoué sans AG_PREMIERE_INSTALLATION : $REJEU"
ok "contenu de départ : refusé hors première installation"

# Le .env est lu par wp-config.php, jamais exécuté.
# shellcheck disable=SC2016  # code PHP
[[ "$(wp eval 'echo getenv("ALGHAZALI_FUMEE");' 2>/dev/null)" == '$(touch /tmp/execute)' ]] \
  || echec "une valeur du .env a été interprétée"
ok "valeur du .env reçue telle quelle, jamais exécutée"
# shellcheck disable=SC2016  # code PHP
[[ "$(wp eval 'echo getenv("ALGHAZALI_GUILLEMETS");' 2>/dev/null)" == 'deux mots' ]] \
  || echec "guillemets du .env mal retirés"
ok "guillemets englobants retirés"

# ─────────────────────────────────────────────────────────────────────────────
etape "Ce que l'installation a laissé en base"
# shellcheck disable=SC2016  # code PHP
ETAT="$(wp eval '
  $slug = static fn( $option ) => (string) get_post_field( "post_name", (int) get_option( $option ) );
  echo implode( " ", array(
    get_option( "show_on_front" ), $slug( "page_on_front" ), $slug( "page_for_posts" ),
    $slug( "wp_page_for_privacy_policy" ), get_option( "permalink_structure" ),
    get_option( "WPLANG" ), get_option( "timezone_string" ), "public=" . get_option( "blog_public" ),
    get_stylesheet(),
    in_array( "al-ghazzali-institut/al-ghazzali-institut.php", (array) get_option( "active_plugins" ), true ) ? "extension" : "sans-extension",
    "menus=" . count( array_filter( (array) get_theme_mod( "nav_menu_locations" ) ) ),
    "formations=" . ( wp_count_posts( "ag_course" )->publish > 0 ? "oui" : "non" ),
    "exemples=" . count( get_posts( array( "post_type" => array( "post", "page" ), "post_status" => "any", "post_name__in" => array( "bonjour-tout-le-monde", "page-d-exemple" ) ) ) ) ) );' 2>/dev/null)"
ATTENDU="page accueil blog politique-de-confidentialite /%postname%/ fr_FR Europe/Paris public=1 al-ghazzali extension menus=3 formations=oui exemples=0"
[[ "$ETAT" == "$ATTENDU" ]] || echec "état après installation : « $ETAT » (attendu « $ATTENDU »)"
ok "$ETAT"

# ─────────────────────────────────────────────────────────────────────────────
etape "php-fpm, sous l'uid $APP_UID, en lecture seule"
docker run -d --name "$P-fpm" "${APP[@]}" --network-alias fpm \
  --health-cmd healthcheck.sh --health-interval 2s --health-retries 30 \
  "$IMAGE_REF" >/dev/null
for i in $(seq 1 60); do
  [[ "$(docker inspect --format '{{.State.Health.Status}}' "$P-fpm")" == healthy ]] && break
  [[ $i == 60 ]] && echec "php-fpm jamais healthy"
  sleep 1
done
ok "healthcheck (ping FastCGI)"

if docker exec "$P-fpm" sh -c 'touch /var/www/app/public/index.php' 2>/dev/null; then
  echec "le code est modifiable depuis le conteneur"
fi
ok "le code refuse l'écriture"

# `docker exec` ne passe pas par l'entrypoint : la configuration doit venir
# quand même, c'est ainsi que la documentation fait lancer WP-CLI.
[[ "$(docker exec "$P-fpm" wp option get home 2>/dev/null)" == "http://$DOMAIN" ]] \
  || echec "docker exec … wp ne trouve pas sa configuration"
ok "docker exec … wp option get home"

# /up servi par php-fpm directement, avec le chemin de l'HÔTE que nginx envoie
# (sans le lien /var/www/alghazali-site de l'image, « File not found ») : c'est
# ainsi que deploy.sh vérifie le site tant qu'il n'a pas son certificat.
docker exec \
  -e SCRIPT_FILENAME=/var/www/alghazali-site/public/index.php -e SCRIPT_NAME=/index.php \
  -e DOCUMENT_ROOT=/var/www/alghazali-site/public -e REQUEST_METHOD=GET -e REQUEST_URI=/up \
  -e QUERY_STRING= -e SERVER_NAME="$DOMAIN" -e HTTP_HOST="$DOMAIN" -e SERVER_PORT=80 \
  "$P-fpm" cgi-fcgi -bind -connect 127.0.0.1:9000 | tr -d '\r' > "$TMP/up.txt"
grep -q '"statut":"ok"' "$TMP/up.txt" || echec "/up par cgi-fcgi : $(head -c 300 "$TMP/up.txt")"
ok "/up par cgi-fcgi, SCRIPT_FILENAME=/var/www/alghazali-site/public/index.php : ok"

# Les adresses que deploy.sh vérifie après la bascule : la même commande.
CHEMINS="$(wp post list --post_type=page,ag_course --post_status=publish \
  --orderby=menu_order --order=ASC --posts_per_page=40 --field=url 2>/dev/null \
  | sed -E 's#^https?://[^/]+##' | grep -E '^/([a-z0-9][a-z0-9/-]*/)?$' || true)"
if [[ "$(grep -c . <<< "$CHEMINS")" -lt 5 ]] || ! grep -qx / <<< "$CHEMINS"; then
  echec "pages relevées comme dans deploy.sh : $CHEMINS"
fi
ok "pages relevées comme dans deploy.sh : $(grep -c . <<< "$CHEMINS") adresses, l'accueil en « / »"

# ─────────────────────────────────────────────────────────────────────────────
etape "nginx avec deploy/nginx-alghazali-site.conf"
# Seuls changent l'adresse de php-fpm (le conteneur, au lieu du port publié sur
# l'hôte) et l'écoute IPv6, absente des réseaux Docker par défaut.
sed -e 's/127\.0\.0\.1:9007/fpm:9000/' -e '/listen \[::\]/d' \
  "$RACINE/deploy/nginx-alghazali-site.conf" > "$TMP/alghazali-site.conf"
# shellcheck disable=SC2016  # $… pour le shell du conteneur
docker create --name "$P-nginx" --network "$P" \
  --network-alias "$DOMAIN" --network-alias "www.$DOMAIN" \
  -v "$P-public:/var/www/alghazali-site/public:nocopy" \
  -v "$P-uploads:/var/www/alghazali-site/uploads:nocopy" \
  "$NGINX_IMAGE" sh -c "sed -i 's/^user .*/user www-data;/' /etc/nginx/nginx.conf \
    && rm -f /etc/nginx/conf.d/default.conf && exec nginx -g 'daemon off;'" >/dev/null
docker cp "$(chemin_hote "$TMP/alghazali-site.conf")" "$P-nginx:/etc/nginx/conf.d/alghazali-site.conf"
docker start "$P-nginx" >/dev/null
for i in $(seq 1 30); do
  [[ "$(code_http "http://$DOMAIN/up" || true)" != 000 ]] && break
  [[ $i == 30 ]] && echec "nginx ne répond pas"
  sleep 1
done

attendu "http://$DOMAIN/" 200
SANTE="$(web "http://$DOMAIN/up")"
[[ "$SANTE" == *'"statut":"ok"'* ]] || echec "/up : $SANTE"
ok "/up -> $SANTE"
while IFS= read -r chemin; do
  attendu "http://$DOMAIN$chemin" 200
done <<< "$CHEMINS"
attendu "http://$DOMAIN/formations/" 200
attendu "http://$DOMAIN/robots.txt" 200
attendu "http://$DOMAIN/wp-sitemap.xml" 200
attendu "http://$DOMAIN/page-qui-n-existe-pas/" 404
attendu "http://$DOMAIN$THEME/assets/js/app.js" 200
attendu "http://$DOMAIN$THEME/assets/css/main.css" 200
attendu "http://$DOMAIN$THEME/assets/fonts/fraunces-latin-wght-normal.woff2" 200
attendu "http://$DOMAIN/wp-includes/css/dashicons.min.css" 200
# Le contenu de départ reste dans l'image, hors de public/.
attendu "http://$DOMAIN/tools/seed.php" 404

REDIR="$(web -o /dev/null -w '%{http_code} %{redirect_url}' "http://www.$DOMAIN/contact/?a=1")"
[[ "$REDIR" == "301 http://$DOMAIN/contact/?a=1" ]] || echec "www : $REDIR"
ok "www -> $REDIR"
REDIR="$(web -o /dev/null -w '%{http_code} %{redirect_url}' "http://$DOMAIN/wp-login.php")"
[[ "$REDIR" == "302 https://$DOMAIN/wp-login.php" ]] || echec "FORCE_SSL_ADMIN : $REDIR"
ok "connexion forcée en HTTPS : $REDIR"

for refuse in /xmlrpc.php /readme.html /license.txt /wp-config.php /wp-config-sample.php \
              /wp-admin/install.php /wp-admin/setup-config.php /.env /.git/config; do
  attendu "http://$DOMAIN$refuse" 403
done

# Un .php glissé dans les médias ne doit jamais être exécuté.
hote "printf '<?php echo \"EXECUTE\";' > /h/uploads/fumee.php && chown $APP_UID:$WWW_GID /h/uploads/fumee.php"
attendu "http://$DOMAIN/wp-content/uploads/fumee.php" 403
hote "rm /h/uploads/fumee.php"

# ─────────────────────────────────────────────────────────────────────────────
etape "Chaque contenu publié, rendu par nginx"
docker run --rm -i "${APP[@]}" "$IMAGE_REF" sh -c 'cat > /tmp/pages.php && wp eval-file /tmp/pages.php' \
  < "$ICI/fumee-pages.php" || echec "rendu des contenus publiés (détail ci-dessus)"
ok "chaque contenu : 200, canonique, un seul H1, JSON-LD lisible, aucune erreur PHP, app.js versionné"

if docker logs "$P-fpm" 2>&1 | grep -E 'PHP (Fatal|Parse|Warning|Notice|Deprecated).*(themes/al-ghazzali|plugins/al-ghazzali-institut|mu-plugins/alghazali)'; then
  echec "erreurs PHP du thème ou de l'extension pendant le rendu (ci-dessus)"
fi
ok "aucune erreur PHP du thème ni de l'extension dans les journaux de php-fpm"

# ─────────────────────────────────────────────────────────────────────────────
etape "Téléversement"
# shellcheck disable=SC2016  # $… pour le shell du conteneur
MEDIA_ID="$(docker run --rm "${APP[@]}" "$IMAGE_REF" sh -euc '
  php -r "imagepng(imagecreatetruecolor(1600, 900), \"/tmp/fumee.png\");"
  wp media import /tmp/fumee.png --porcelain' 2>/dev/null)"
[[ "$MEDIA_ID" =~ ^[0-9]+$ ]] || echec "wp media import : $MEDIA_ID"
MEDIA_URL="$(wp eval "echo wp_get_attachment_url($MEDIA_ID);" 2>/dev/null)"
MEDIA_REL="${MEDIA_URL#http://"$DOMAIN"/wp-content/uploads/}"
ok "média $MEDIA_ID : $MEDIA_REL"
hote "
  f=/h/uploads/$MEDIA_REL
  test -f \"\$f\"
  stat -c '%u:%g' \"\$f\" | grep -qx '$APP_UID:$WWW_GID'
  test -z \"\$(find /h/uploads ! -group $WWW_GID | head -1)\"
  test -z \"\$(find /h/uploads -type d ! -perm -2000 | head -1)\"
  ls \"\$(dirname \"\$f\")\" | grep -q -- '-150x150'" \
  || echec "média absent, vignettes non générées, ou groupe www-data perdu dans uploads/"
ok "fichier en $APP_UID:www-data, vignettes générées, dossiers en setgid"
attendu "$MEDIA_URL" 200

# ─────────────────────────────────────────────────────────────────────────────
etape "Cron"
wp cron event run --due-now 2>/dev/null | tail -1
docker run -d --name "$P-cron" "${APP[@]}" "$IMAGE_REF" cron.sh >/dev/null
sleep 5
[[ "$(docker inspect --format '{{.State.Running}}' "$P-cron")" == true ]] || echec "le service cron s'est arrêté"
if docker logs "$P-cron" 2>&1 | grep -q 'passe en échec'; then
  echec "cron : $(docker logs "$P-cron" 2>&1 | tail -3)"
fi
DEBUT=$SECONDS
docker stop -t 20 "$P-cron" >/dev/null
(( SECONDS - DEBUT < 5 )) || echec "le service cron met $((SECONDS - DEBUT)) s à s'arrêter (SIGTERM ignoré)"
ok "service cron : une passe sans erreur, arrêt immédiat sur SIGTERM"

etape "Noyau"
wp core verify-checksums 2>/dev/null | tail -1

echo ""
echo "Tout est vert."
