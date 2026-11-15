# Déploiement

Même chaîne que cnx-solutions.fr et madrasapp.com (dépôts `cnxsolutions/cnx-solutions` et
`cnxsolutions/madrasapp-site`, `docs/deploiement.md`) : **la CI construit une image Docker, le VPS ne fait que la
tirer.** Le serveur n'a pas de clone git et ne télécharge rien lui-même. Un seul environnement, la production, et le
noyau WordPress n'est jamais versionné : il est téléchargé à la construction de l'image, à la version épinglée dans le
`Dockerfile`, puis vérifié fichier par fichier. Le dépôt ne contient que le thème `al-ghazzali`, l'extension
`al-ghazzali-institut`, deux mu-plugins, le contenu de départ (`tools/seed*.php`) et cette chaîne.

| Déclencheur | Effet |
|---|---|
| push sur `main` | tests, image `ghcr.io/cnxsolutions/alghazali-institut:sha-<7>`, mise en ligne sur alghazali-institut.fr |
| Actions › Déploiement › *Run workflow* | redéploie l'image de son choix : c'est le **retour arrière** |
| idem, case « première installation » | une seule fois : installe WordPress et le contenu de départ sur la base vide |

**Un push sur `main` met en ligne, sans clic.** Les tests sont le seul garde-fou, dont un test de fumée qui démarre
l'image derrière nginx et MySQL, l'installe avec le contenu de départ, puis affiche chaque page du site avant toute
publication (`tests/fumee.sh`). On travaille donc sur une branche (`feat/…`, `fix/…`) et on ouvre une pull request :
les tests tournent à chaque envoi sur la PR. On fusionne quand ils sont verts.

## Ce que fait un déploiement

`deploy/deploy.sh`, lancé sur le VPS par la CI :

1. vérifie que `/var/www/alghazali-site/.env` existe, puis tire l'image (trois essais) ;
2. lance `wp core is-installed` avec la nouvelle image. Si la base est vide ou injoignable, ou si le code a une
   erreur fatale, **il s'arrête là** et l'ancienne version reste en service. Avec « première installation »
   seulement, une base **vide** (aucune table) est installée par `docker/installer.sh` : WordPress en ligne de
   commande, jamais par l'installeur web, puis le contenu de départ (`tools/seed.php`) ;
3. copie l'arborescence publique de l'image dans `public/` (`docker/publier.sh`) ;
4. sauvegarde la base dans `/var/backups/alghazali-site/` (14 jours), puis lance `wp core update-db` **seulement si
   l'image apporte un WordPress plus récent que la base** ;
5. relève, avec la nouvelle image, l'adresse des pages et formations publiées (40 au plus) ;
6. bascule `fpm` et `cron` sur la nouvelle image ;
7. vérifie `/up`, l'accueil, `/formations/` puis chaque page relevée, jusqu'à trois essais à 2 s d'intervalle par
   adresse. Par nginx, en HTTPS, dès que le vhost a son certificat ; avant, en interrogeant php-fpm directement, comme
   le ferait nginx. Si l'une ne répond toujours pas 200, il recopie les fichiers publics de la version précédente et
   la remet en service.

## Le contenu

Contrairement à madrasapp.com, **le contenu vit en base** : formations, emploi du temps, équipe, événements,
articles et pages se modifient dans l'administration (extension `al-ghazzali-institut`). Le contenu de départ
(`tools/seed-content.php`) n'est joué qu'**une fois**, à la première installation, et ne réécrit jamais rien : sous
WP-CLI, `tools/seed.php` refuse de tourner sans `AG_PREMIERE_INSTALLATION=1`, que seul `docker/installer.sh` pose. Un
déploiement ne touche donc jamais au contenu ; seule la sauvegarde de l'étape 4 le protège.

Comme sur le poste, l'équipe et les événements sont créés **en brouillon**, et les horaires, rythmes et niveaux des
formations sont des exemples : à faire valider par l'institut puis à corriger dans l'administration.

## Retour arrière

Actions › **Déploiement** › *Run workflow*, `image_tag` = l'image précédente (`sha-a1b2c3d`, visible dans le résumé
des déploiements précédents ou sur la page du paquet GHCR). Ou bien, sur le VPS, en tant que `deploy`, après un
`docker login ghcr.io` :

```bash
IMAGE=ghcr.io/cnxsolutions/alghazali-institut IMAGE_TAG=sha-a1b2c3d bash /opt/apps/alghazali-site/deploy.sh
```

⚠ Un retour arrière ramène l'ancienne image, **pas l'ancienne base**. Pour revenir aussi sur la base, restaurer la
sauvegarde prise avant le déploiement fautif, en root :

```bash
ls -lt /var/backups/alghazali-site/
gunzip -c /var/backups/alghazali-site/alghazali_site-AAAAMMJJ-HHMMSS-avant-sha-xxxxxxx.sql.gz | mysql alghazali_site
```

## Ce qui fait quoi

| Fichier | Rôle |
|---|---|
| `Dockerfile` | noyau WordPress épinglé et vérifié, WP-CLI, php-fpm 8.3, notre code. Code en lecture seule, propriété de root |
| `docker/wp-config.php` | configuration de production, sans secret : tout vient du `.env` |
| `docker/entrypoint.sh` | refuse de démarrer sans `.env` |
| `docker/installer.sh` | première installation sur une base vide, en ligne de commande, avec le contenu de départ |
| `docker/publier.sh` | copie l'arborescence publique vers `public/` sur l'hôte, sans jamais toucher au lien des médias |
| `docker/cron.sh` | remplace WP-Cron : `wp cron event run --due-now` chaque minute |
| `docker/healthcheck.sh` | ping php-fpm en FastCGI |
| `wp-content/mu-plugins/alghazali-sante.php` | `/up` : base, installation, médias inscriptibles, version de l'image |
| `wp-content/mu-plugins/alghazali-smtp.php` | envoi des courriels par le SMTP du `.env` ; journalise les échecs |
| `tools/seed.php`, `seed-content.php`, `seed-blocks.php` | le contenu de départ, sur le poste comme à la première installation |
| `deploy/alghazali-site.compose.yml` | téléversé en `/opt/apps/alghazali-site/docker-compose.yml` à chaque déploiement |
| `deploy/deploy.sh` | voir plus haut |
| `deploy/bootstrap-vps.sh` | préparation du serveur, une seule fois |
| `deploy/nginx-alghazali-site.conf` | le vhost, posé par `bootstrap-vps.sh`, servi tel quel par le test de fumée |
| `tests/fumee.sh`, `tests/fumee-pages.php` | test de fumée de l'image, en local comme en CI |
| `.github/workflows/tests.yml` | `php -l`, ShellCheck, actionlint, PHPCS (indicatif), test de fumée |
| `.github/workflows/deploy.yml` | la chaîne complète |

Deux conteneurs : `alghazali-site-fpm` (publié sur `127.0.0.1:9007`, derrière le nginx de l'hôte ; 512 Mo, 1 CPU,
6 processus PHP) et `alghazali-site-cron` (256 Mo, ¼ de CPU). Tous deux tournent sous l'uid 9007 (`alghazali-site`),
en lecture seule, sans capacité. MySQL est celui de l'hôte, joint par son socket `/var/run/mysqld/mysqld.sock`,
monté dans les conteneurs. Ports et uid déjà pris sur le VPS : 9000 à 9002 (application Madrasapp), 9003 et 9004
(Remoraid), 9005 (cnx-solutions.fr), 9006 (madrasapp.com) ; `bootstrap-vps.sh` vérifie que 9007 est libre avant de
rien créer.

### Ce qui reste sur le serveur, hors image

| Chemin | Contenu |
|---|---|
| `/var/www/alghazali-site/.env` | accès à la base, clés et sels, `WP_HOME`, SMTP. 600, lisible par `alghazali-site` seul |
| `/var/www/alghazali-site/uploads/` | les médias. Monté dans les conteneurs sur `wp-content/uploads` |
| `/var/www/alghazali-site/public/` | copie de l'arborescence de l'image, servie par nginx. `wp-content/uploads` y est un lien relatif vers `../../uploads` |
| `/opt/apps/alghazali-site/` | compose et `deploy.sh`, réécrits par chaque déploiement |
| `/var/backups/alghazali-site/` | sauvegardes prises avant chaque déploiement, 14 jours, 700 |
| `/etc/nginx/sites-available/alghazali-site` | le vhost, complété pour le TLS par certbot |

Pourquoi `public/` contient aussi le PHP, pourquoi le code vit dans `/var/www/app` avec un lien
`/var/www/alghazali-site`, pourquoi `wp-config.php` lit le `.env` lui-même : mêmes raisons que pour cnx-solutions.fr,
expliquées dans les commentaires du `Dockerfile`, de `docker/publier.sh` et de `docker/wp-config.php`.

## Mise en place initiale (une fois)

Commandes du poste à lancer dans **Git Bash** (PowerShell ne connaît pas `<` et réécrit les secrets en CRLF). `VPS`
est l'adresse du serveur, la même que pour cnx-solutions.fr et madrasapp.com ; elle n'est écrite nulle part dans ce
dépôt, qui est public.

```bash
VPS=<adresse du VPS>
```

1. **DNS** : la zone de `alghazali-institut.fr` est chez Cloudflare. Y créer deux enregistrements A, `@` et `www`,
   vers l'adresse du VPS, en **« DNS only » (nuage gris)**, comme madrasapp.com qui répond directement par l'adresse
   du VPS : le certificat est celui de Let's Encrypt, posé par certbot sur le VPS, et le proxy de Cloudflare
   s'interposerait avec le sien. Les étapes 2 à 5 n'en dépendent pas : seul le certificat (étape 6) attend que le
   DNS réponde.

2. **Clé SSH de la CI**, sur le poste. Elle est dédiée à ce dépôt : la révoquer ne coupe pas les autres applications.

   ```bash
   ssh-keygen -t ed25519 -N "" -C "github-actions@cnxsolutions/alghazali-institut" -f ~/.ssh/alghazali-site-ci
   ```

3. **Sur le VPS, en root** : poser les deux fichiers côte à côte, puis lancer la préparation avec la clé
   **publique**. Elle vérifie d'abord que rien n'occupe la place (uid 9007, dossier, port 9007, base, vhost du
   domaine, socket MySQL joignable par l'uid 9007), et s'arrête au moindre doute. Puis elle crée l'utilisateur
   `alghazali-site`, les dossiers, la base et son utilisateur, le `.env` (clés et sels tirés par openssl), le dossier
   de sauvegardes, et active le vhost en HTTP seul après un `nginx -t`. Elle ajoute enfin la clé aux clés autorisées
   de `deploy`, après une copie de sauvegarde de `authorized_keys`.

   ```bash
   ssh root@$VPS mkdir -p /root/alghazali-bootstrap
   scp deploy/bootstrap-vps.sh deploy/nginx-alghazali-site.conf root@$VPS:/root/alghazali-bootstrap/
   ssh root@$VPS bash /root/alghazali-bootstrap/bootstrap-vps.sh "'$(cat ~/.ssh/alghazali-site-ci.pub)'"
   ```

   Idempotent : la relancer ne change rien à ce qui existe (mots de passe, vhost, clés).

4. **Secrets GitHub**, depuis le poste. La clé privée part dans le secret sans s'afficher. L'empreinte de l'hôte se
   vérifie **avant** d'être posée : c'est la même que pour cnx-solutions.fr et madrasapp.com.

   ```bash
   R=cnxsolutions/alghazali-institut
   gh secret set VPS_SSH_KEY -R $R < ~/.ssh/alghazali-site-ci
   ssh-keyscan -t ed25519 $VPS 2>/dev/null > /tmp/vps-hote
   ssh-keygen -lf /tmp/vps-hote          # comparer l'empreinte avant d'aller plus loin
   gh secret set VPS_KNOWN_HOSTS -R $R < /tmp/vps-hote
   gh secret set VPS_HOST -R $R -b "$VPS"
   gh secret set VPS_USER -R $R -b deploy
   rm ~/.ssh/alghazali-site-ci /tmp/vps-hote   # la clé privée ne sert qu'à GitHub
   ```

5. **Fusionner la PR dans `main`** : la CI teste, construit et publie l'image. Son déploiement s'arrête sur la base
   vide, sans rien toucher (« Base vide : relancer avec la case première installation ») : c'est attendu.

   Puis **première installation** : Actions › Déploiement › *Run workflow*, branche `main`, case « première
   installation » cochée. WordPress s'installe en ligne de commande sur la base vide (compte `alghazali-admin`,
   adresse `contact@alghazali-institut.fr`, mot de passe aléatoire que personne ne voit), le contenu de départ est
   créé, et le site tourne sur le port 9007, vérifié par php-fpm.

6. **Certificat**, en root sur le VPS, dès que `dig +short alghazali-institut.fr` et `www` répondent l'adresse du
   VPS. certbot ajoute l'écoute 443 et la redirection HTTP → HTTPS au vhost, puis recharge nginx :

   ```bash
   certbot --nginx -d alghazali-institut.fr -d www.alghazali-institut.fr --redirect
   curl -s https://alghazali-institut.fr/up
   ```

   Les déploiements suivants vérifient alors le site par nginx en HTTPS, comme un visiteur.

7. **Mot de passe de l'administrateur**, en tant que `deploy` :

   ```bash
   docker exec -it alghazali-site-fpm wp user update alghazali-admin --prompt=user_pass
   ```

   Si l'adresse `contact@alghazali-institut.fr` n'existe pas, mettre la vraie :
   `docker exec alghazali-site-fpm wp user update alghazali-admin --user_email=<adresse>` et
   `docker exec alghazali-site-fpm wp option update admin_email <adresse>`.

8. **Contenu** : relire dans l'administration les formations (horaires, rythmes, niveaux), publier l'équipe et les
   événements, renseigner les liens de la plateforme élèves (Apparence › Personnaliser › Al Ghazzali Institut ›
   Plateforme élèves).

9. **Google** : déclarer `https://alghazali-institut.fr` dans la Search Console et y soumettre `/wp-sitemap.xml`.

## Courriel

L'image n'a pas de sendmail. Tant que `SMTP_HOST` est vide dans le `.env`, **aucun courriel ne part** ; chaque échec
est journalisé (`docker logs alghazali-site-fpm | grep courriel`). Le site n'a aucun formulaire : seuls les courriels
de l'administration sont concernés (mot de passe oublié, alertes de WordPress).

Pour brancher un SMTP, renseigner `SMTP_HOST`, `SMTP_PORT`, `SMTP_SECURE`, `SMTP_USER`, `SMTP_PASSWORD` et
`SMTP_FROM` dans `/var/www/alghazali-site/.env` (en root). L'effet est immédiat. Expédier depuis une adresse
`@alghazali-institut.fr` impose d'abord d'ajouter dans la zone DNS du domaine les enregistrements SPF et DKIM du
fournisseur.

## WP-CLI

```bash
docker exec -it alghazali-site-fpm wp <commande>
docker logs --tail 100 alghazali-site-fpm         # erreurs PHP
```

Préférer `docker exec` à `docker compose exec` : toute commande `docker compose` exige `IMAGE` et `IMAGE_TAG`. Le code
est en lecture seule : `wp plugin install`, `wp core update` ou `wp theme update` échouent, et c'est voulu.

## Mettre à jour

- **WordPress** : changer `WP_VERSION` dans le `Dockerfile`, pousser. Le déploiement sauvegarde la base puis met
  son schéma à jour. Les traductions du noyau suivent (paquet `fr_FR`).
- **WP-CLI** : `WP_CLI_VERSION` et `WP_CLI_SHA512` (la somme publiée avec la version).
- **Le thème, l'extension, les mu-plugins** : une PR, puis la fusion.
- **Une extension tierce** (formulaire, cache…) : à ajouter au dépôt et au `Dockerfile`, jamais depuis
  l'administration (`DISALLOW_FILE_MODS`).

## Ce qui change par rapport au poste de dev

- **Rien ne s'installe ni ne se met à jour depuis l'administration** : le code vient de l'image.
- **WP-Cron ne dépend plus des visites** : le conteneur `alghazali-site-cron` passe chaque minute.
- **Pas de `debug.log`** : les erreurs PHP vont dans `docker logs alghazali-site-fpm`.
- **Pas de `.htaccess`** : nginx sert le site, le vhost tient lieu de règles de réécriture.
- **La compression** est faite par nginx (gzip), pas par PHP.
- **Les fichiers statiques** sont gardés 7 jours par le navigateur ; le CSS et le JS du thème portent leur version
  dans `?ver=…`.

## Ce que ce dispositif ne couvre pas

La sauvegarde prise avant chaque déploiement protège un déploiement, **pas les données**. Il n'y a ni sauvegarde
régulière de la base, qui contient tout le contenu édité par l'institut, ni copie hors du VPS. C'est le plus gros
risque restant : à mettre en place dès que l'institut commence à modifier le contenu, puis à vérifier par une
restauration réelle.
