#!/bin/sh
# Healthcheck du service fpm : interroge php-fpm en FastCGI, sans passer par
# nginx ni par WordPress. Il prouve que le pool répond, pas que le site
# fonctionne : deploy.sh complète par une vraie requête HTTPS sur /up et /.
set -eu
SCRIPT_NAME=/fpm-ping SCRIPT_FILENAME=/fpm-ping REQUEST_METHOD=GET \
    cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -q pong
