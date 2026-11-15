#!/bin/sh
# Service cron : remplace WP-Cron, désactivé dans wp-config.php
# (DISABLE_WP_CRON).
#
# Laissé actif, WP-Cron se déclenche pendant les visites : une page sur
# quelques-unes paie le prix des tâches planifiées, et un site sans visite n'en
# exécute aucune. Ici, une passe par minute, quel que soit le trafic.
#
# `sleep & wait` plutôt qu'un `sleep` simple : le shell reste interruptible.
# SIGTERM (envoyé par `docker compose up` au moment de la bascule) est traité
# aussitôt, au lieu d'attendre la fin de la minute puis le SIGKILL.
set -u
trap 'exit 0' TERM INT

while :; do
    wp cron event run --due-now --quiet || echo "[cron] passe en échec (code $?)" >&2
    sleep 60 &
    wait $!
done
