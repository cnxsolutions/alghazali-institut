#!/bin/sh
# Point d'entrée commun à fpm, cron et aux commandes ponctuelles
# (`docker compose run --rm fpm wp …`).
#
# Le .env de l'hôte est monté en lecture seule sur /var/www/app/.env. Ce n'est
# pas ce script qui le charge mais wp-config.php, à chaque exécution de
# WordPress : `docker exec` ne passe pas par ici, et doit pourtant trouver la
# même configuration. Rien n'est figé dans l'image : un conteneur relit
# toujours le .env du moment.
set -eu

# Si le fichier monté n'existe pas sur l'hôte, Docker crée à sa place un
# DOSSIER .env : `-f` le distingue, `-e` ne le ferait pas. Mieux vaut un
# conteneur qui refuse de démarrer qu'un site qui répond « Configuration
# incomplète » à chaque visiteur.
if [ ! -f /var/www/app/.env ]; then
    echo "[entrypoint] ERREUR : /var/www/app/.env absent. Il doit être monté par le compose." >&2
    exit 1
fi

# Sur stderr : deploy.sh lit la sortie de `wp eval`, qui ne doit contenir que
# ce que la commande imprime.
echo "[entrypoint] alghazali-site ${APP_VERSION:-?} : $*" >&2
exec "$@"
