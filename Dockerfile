# ─────────────────────────────────────────────────────────────────────────────
# Al Ghazali Institut : image de production (php-fpm + noyau WordPress + notre
# code)
#
# Même chaîne que cnx-solutions.fr et madrasapp.com : construite par la CI
# (.github/workflows/deploy.yml), publiée sur GHCR, puis seulement TIRÉE par
# le VPS. Le serveur n'a pas de clone git et ne télécharge rien lui-même. Sur
# le serveur, il ne reste que le .env, les médias (uploads) et la copie
# publique que sert nginx.
#
# Le noyau WordPress n'est jamais versionné : il est téléchargé ici, à la
# version épinglée ci-dessous, puis vérifié fichier par fichier contre les
# sommes publiées par wordpress.org. Le dépôt ne garde que ce qui nous
# appartient : le thème, l'extension, les mu-plugins, wp-config.php et le
# contenu de départ (tools/seed*.php).
#
# Monter de version : changer WP_VERSION, pousser. deploy.sh met le schéma à
# jour (wp core update-db) après avoir sauvegardé la base.
#
# Deux étapes : le noyau et WP-CLI, puis le runtime. Pas d'étape d'assets : le
# thème n'a ni npm ni Sass, son CSS et son JS sont écrits à la main.
# ─────────────────────────────────────────────────────────────────────────────

# ─── 1. Noyau WordPress et WP-CLI ────────────────────────────────────────────
FROM php:8.3-cli-bookworm AS wordpress

ARG WP_VERSION=7.1.3
ARG WP_LOCALE=fr_FR
ARG WP_CLI_VERSION=2.12.0
# Somme publiée avec la version (wp-cli-<version>.phar.sha512), recopiée ici :
# la vérifier contre un fichier téléchargé au même endroit que le phar ne
# protégerait de rien.
ARG WP_CLI_SHA512=be928f6b8ca1e8dfb9d2f4b75a13aa4aee0896f8a9a0a1c45cd5d2c98605e6172e6d014dda2e27f88c98befc16c040cbb2bd1bfa121510ea5cdf5f6a30fe8832

# 128 Mo, la limite par défaut de PHP en ligne de commande, ne suffisent pas à
# WP-CLI pour décompresser l'archive du noyau.
RUN echo 'memory_limit = 512M' > /usr/local/etc/php/conf.d/memory.ini \
    && curl -fsSLo /usr/local/bin/wp \
        "https://github.com/wp-cli/wp-cli/releases/download/v${WP_CLI_VERSION}/wp-cli-${WP_CLI_VERSION}.phar" \
    && echo "${WP_CLI_SHA512}  /usr/local/bin/wp" | sha512sum -c - \
    && chmod 755 /usr/local/bin/wp

WORKDIR /build
# WP-CLI télécharge l'archive et vérifie son empreinte md5, mais c'est `tar`
# qui la décompresse (--no-extract) : l'extracteur interne de WP-CLI (PharData)
# perd EN SILENCE les fichiers aux chemins de plus de 100 caractères. Le
# noyau 7.x en contient (wp-includes/php-ai-client/…) : le site aurait été
# incomplet, et seul verify-checksums l'a vu (constaté sur cnx-solutions.fr).
#
# Le paquet fr_FR contient aussi les traductions du noyau (wp-content/languages).
# Les thèmes et extensions livrés avec le noyau sont retirés APRÈS la
# vérification. Elle ignore wp-content, mais pas readme.html, license.txt ni
# wp-config-sample.php : ils restent pour qu'elle puisse être rejouée sur
# l'image, et le vhost refuse de les servir.
RUN wp core download --version="$WP_VERSION" --locale="$WP_LOCALE" --path=archive \
        --no-extract --allow-root \
    && tar -xzf archive/*.tar.gz -C /build \
    && mv /build/wordpress /build/public \
    && rm -rf archive \
    && wp core verify-checksums --version="$WP_VERSION" --locale="$WP_LOCALE" --path=public --allow-root \
    && rm -rf public/wp-content/themes/twenty* public/wp-content/plugins/akismet \
        public/wp-content/plugins/hello.php

# ─── 2. Runtime ──────────────────────────────────────────────────────────────
FROM php:8.3-fpm-bookworm AS runtime

ARG DEBIAN_FRONTEND=noninteractive

# Ce que WordPress réclame (mysqli) ou recommande dans « Santé du site » : gd
# pour les vignettes (avec WebP), exif pour l'orientation des photos, intl,
# zip. Ni imagick ni redis : pas de cache objet en place.
# Les -dev sont conservées : purger avec --auto-remove emporterait libicu et
# casserait intl à l'exécution.
# libfcgi-bin fournit cgi-fcgi, qu'utilisent le healthcheck et deploy.sh pour
# interroger php-fpm directement (voir docker/healthcheck.sh).
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
        libwebp-dev libfcgi-bin \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" mysqli gd exif intl zip opcache \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
# Remplace la conf par défaut, qui fixe user/group www-data : le conteneur est
# démarré sous l'uid de l'application, php-fpm ne doit pas changer d'identité.
COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/entrypoint.sh docker/healthcheck.sh docker/cron.sh docker/publier.sh \
     docker/installer.sh /usr/local/bin/
# WP-CLI reste dans l'image : le service cron s'en sert chaque minute, et
# deploy.sh pour installer, vérifier l'installation et mettre le schéma à jour.
COPY --from=wordpress /usr/local/bin/wp /usr/local/bin/wp

WORKDIR /var/www/app
COPY --from=wordpress /build/public ./public
COPY docker/wp-config.php docker/wp-cli.yml ./
COPY wp-content/themes/al-ghazzali ./public/wp-content/themes/al-ghazzali
COPY wp-content/plugins/al-ghazzali-institut ./public/wp-content/plugins/al-ghazzali-institut
COPY wp-content/mu-plugins ./public/wp-content/mu-plugins
# Le contenu de départ, joué UNE fois par docker/installer.sh. Hors de public/ :
# publier.sh ne le copie pas chez nginx, aucun visiteur ne peut l'appeler.
COPY tools/seed.php tools/seed-blocks.php tools/seed-content.php ./tools/

# Le code appartient à root et reste en lecture seule pour l'uid applicatif :
# une faille dans le site ne peut pas réécrire le code qui le sert. Seuls
# wp-content/uploads (monté depuis l'hôte) et /tmp (tmpfs) s'écrivent.
#
# wp-content/uploads est créé vide : c'est le point de montage des médias.
#
# Le lien /var/www/alghazali-site existe parce que le nginx de l'hôte envoie
# SCRIPT_FILENAME=$realpath_root$fastcgi_script_name, donc le chemin réel SUR
# L'HÔTE (/var/www/alghazali-site/public/index.php). Ce chemin doit résoudre
# à l'identique dans le conteneur, sans quoi php-fpm répond « File not found ».
#
# Les modes sont fixés plutôt qu'hérités : une construction depuis Windows
# marque tous les fichiers exécutables, et publier.sh les recopierait tels
# quels sur l'hôte.
RUN mkdir -p public/wp-content/uploads \
    && chown -R root:root /var/www/app \
    && find /var/www/app -type d -exec chmod 755 {} + \
    && find /var/www/app -type f -exec chmod 644 {} + \
    && chmod 755 /usr/local/bin/entrypoint.sh /usr/local/bin/healthcheck.sh \
        /usr/local/bin/cron.sh /usr/local/bin/publier.sh /usr/local/bin/installer.sh \
        /usr/local/bin/wp \
    && ln -s /var/www/app /var/www/alghazali-site

# WP-CLI écrit son cache sous $HOME, qui n'existe pas pour l'uid de l'app et
# serait de toute façon en lecture seule : on l'envoie dans /tmp. Et il ne
# cherche pas de nouvelle version : sa version est celle de l'image.
ENV WP_CLI_CACHE_DIR=/tmp/wp-cli-cache \
    WP_CLI_DISABLE_AUTO_CHECK_UPDATE=1

# Version affichée dans les journaux, par /up, et lisible par `docker inspect`.
ARG APP_VERSION=dev
ENV APP_VERSION=${APP_VERSION}
LABEL org.opencontainers.image.source="https://github.com/cnxsolutions/alghazali-institut"

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm", "-F"]
