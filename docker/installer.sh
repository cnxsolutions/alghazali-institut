#!/bin/sh
# Première installation de WordPress, en ligne de commande, sur une base VIDE.
#
#   installer.sh <adresse du site>      ex. installer.sh https://alghazali-institut.fr
#
# Lancé par deploy/deploy.sh (PREMIERE_INSTALLATION=1, voir
# docs/deploiement.md) et par tests/fumee.sh, dans un conteneur de l'image : le
# site de production et celui du test s'installent exactement de la même façon.
#
# Jamais par l'installeur web (/wp-admin/install.php) : sur une base vide, il
# laisserait le premier venu créer son propre compte administrateur. Le vhost
# le refuse, et deploy.sh ne bascule jamais sur une base vide sans passer ici.
#
# Le contenu ne vient pas d'une base importée : c'est le contenu de départ du
# poste (tools/seed.php), joué une seule fois. Ensuite, l'institut le modifie
# depuis l'administration, et plus rien ne le réécrit.
set -eu

URL="${1:?usage : installer.sh <adresse du site, ex. https://alghazali-institut.fr>}"
ADMIN=alghazali-admin
ADMIN_EMAIL=contact@alghazali-institut.fr

if wp core is-installed 2>/dev/null; then
    echo "[installer] WordPress est déjà installé : rien à faire."
    exit 0
fi

# Une base qui a des tables sans installation WordPress lisible (préfixe
# différent, installation interrompue, mauvaise base dans le .env) n'est pas
# une base neuve : on n'y installe rien d'office.
TABLES="$(wp db tables --all-tables 2>/dev/null | grep -c . || true)"
if [ "$TABLES" != 0 ]; then
    echo "[installer] ERREUR : la base contient déjà $TABLES table(s) sans installation WordPress : à examiner à la main." >&2
    exit 1
fi

# Sans --admin_password, WP-CLI tire un mot de passe au hasard et l'affiche
# sur la sortie standard, jetée ici : il ne doit apparaître ni dans un
# terminal ni dans le journal de la CI. Il est de toute façon remplacé plus
# bas par un autre, que personne ne voit : le vrai se définit ensuite avec
# `wp user update alghazali-admin --prompt=user_pass` (docs/deploiement.md).
wp core install --url="$URL" --title="Al Ghazzali Institut" --admin_user="$ADMIN" \
    --admin_email="$ADMIN_EMAIL" --locale=fr_FR --skip-email >/dev/null
# shellcheck disable=SC2016  # code PHP
wp eval 'wp_set_password( wp_generate_password( 40, true, true ), get_user_by( "login", "'"$ADMIN"'" )->ID );'
echo "[installer] WordPress installé sur $URL (compte $ADMIN, mot de passe à définir)"

# L'extension d'abord : elle déclare les formations, l'équipe et les
# événements, que le contenu de départ remplit. Le thème ensuite : le contenu
# de départ se sert de ses champs SEO.
wp plugin activate al-ghazzali-institut --quiet
wp theme activate al-ghazzali --quiet

# Réglages, pages, formations, articles, équipe et événements (en brouillon),
# menus. Le script supprime aussi l'article et la page d'exemple de WordPress.
# Hors de public/ (voir le Dockerfile) ; sous WP-CLI, il refuse de tourner sans
# cette variable : il ne sert qu'ici.
AG_PREMIERE_INSTALLATION=1 wp eval-file /var/www/app/tools/seed.php

# Le script change la structure des permaliens en cours de processus : les
# règles de réécriture sont régénérées ici, dans un processus neuf qui la
# lit, pour que /formations/ et les autres listes répondent dès la bascule.
wp rewrite flush --quiet

# Le contenu de départ a-t-il bien donné un site ? Une page d'accueil statique
# et des pages publiées, sinon l'installation est incomplète.
ACCUEIL="$(wp option get page_on_front)"
PAGES="$(wp post list --post_type=page --post_status=publish --format=count)"
if [ "$(wp option get show_on_front)" != page ] || [ "$ACCUEIL" = 0 ] || [ "$PAGES" = 0 ]; then
    echo "[installer] ERREUR : contenu de départ incomplet (accueil $ACCUEIL, $PAGES page(s) publiée(s))." >&2
    exit 1
fi
echo "[installer] terminé : $PAGES pages publiées, accueil n° $ACCUEIL"
