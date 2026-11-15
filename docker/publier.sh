#!/bin/sh
# Copie l'arborescence publique de l'image vers le public/ de l'hôte, que sert
# le nginx de l'hôte.
#
# Lancé par deploy/deploy.sh (et par tests/fumee.sh) dans un conteneur jetable
# de l'image, sous l'uid de l'application, avec le public/ de l'hôte monté sur
# le dossier passé en argument :
#
#   docker run --rm --user 9007:9007 --entrypoint publier.sh \
#     -v /var/www/alghazali-site/public:/dst <image> /dst
#
# Toute l'arborescence est copiée, PHP compris : le `try_files $uri =404` du
# vhost vérifie qu'un .php existe sur le disque de l'hôte avant de le confier
# à php-fpm, qui exécute, lui, le fichier de l'image (voir le Dockerfile).
#
# Ce que la copie ne fait jamais :
#   - effacer : les fichiers d'une version précédente restent. Un .php retiré
#     depuis passe le try_files, mais php-fpm ne le trouve plus dans l'image et
#     répond 404 ;
#   - toucher à wp-content/uploads, qui est sur l'hôte un lien vers ../../uploads
#     (les médias), posé par bootstrap-vps.sh ;
#   - utiliser tar : il réapplique le mode des dossiers existants et efface
#     leur bit setgid. L'uid de l'app n'étant pas membre de www-data, les
#     fichiers créés ensuite n'hériteraient plus de ce groupe et nginx ne
#     pourrait plus les lire. `cp -R`, sans -p, ne touche pas aux dossiers
#     existants.
set -eu

DST="${1:?usage : publier.sh <dossier public/ de l’hôte>}"
SRC=/var/www/app/public

[ -d "$DST" ] || { echo "[publier] ERREUR : $DST n'est pas un dossier." >&2; exit 1; }
# Sans ce dossier, la boucle ci-dessous le créerait sans le lien des médias.
[ -d "$DST/wp-content" ] || { echo "[publier] ERREUR : $DST/wp-content absent (voir bootstrap-vps.sh)." >&2; exit 1; }

umask 022
cd "$SRC"

for f in * .[!.]*; do
    [ -e "$f" ] || continue
    [ "$f" = wp-content ] && continue
    cp -R "$f" "$DST/"
done

for f in wp-content/* wp-content/.[!.]*; do
    [ -e "$f" ] || continue
    [ "$f" = wp-content/uploads ] && continue
    cp -R "$f" "$DST/wp-content/"
done

if [ ! -L "$DST/wp-content/uploads" ]; then
    echo "[publier] ⚠ $DST/wp-content/uploads n'est pas un lien : nginx ne servira pas les médias." >&2
fi
echo "[publier] $(find "$DST" -type f | wc -l) fichiers dans $DST"
