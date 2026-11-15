<?php
/**
 * Configuration WordPress de l'image de production.
 *
 * Copiée dans /var/www/app/wp-config.php, un niveau AU-DESSUS du noyau
 * (/var/www/app/public) : WordPress l'y trouve tout seul, et elle n'est ainsi
 * jamais copiée dans le public/ de l'hôte que sert nginx.
 *
 * Elle ne contient aucun secret. Tout ce qui varie ou doit rester caché vient
 * de l'environnement (getenv), alimenté par le .env de l'hôte, monté en
 * lecture seule dans /var/www/app/.env. Ce .env est créé par
 * deploy/bootstrap-vps.sh et ne passe jamais par git ni par la CI.
 *
 * Le wp-config.php à la racine du dépôt est celui du poste de développement
 * (Laragon) : il reste hors de git et hors de l'image.
 *
 * @package AlGhazzali
 */

/*
 * Le .env est chargé ICI, et non par l'entrypoint du conteneur : `docker exec`
 * ne passe pas par l'entrypoint, et `docker exec alghazali-site-fpm wp …` se
 * retrouverait sans base ni clés. Lu à chaque requête, il fait moins d'un Ko.
 *
 * Lu ligne à ligne, jamais exécuté : une valeur contenant `$(…)` reste une
 * chaîne. Les guillemets doubles qui entourent une valeur sont retirés, comme
 * le fait la lecture du .env dans deploy/deploy.sh ; et comme elle, une clé
 * répétée vaut sa dernière valeur. Une variable déjà présente dans
 * l'environnement du processus garde la sienne.
 */
( function ( $fichier ) {
	if ( ! is_readable( $fichier ) ) {
		return; // ag_env() dira ce qui manque.
	}

	$valeurs = array();

	foreach ( file( $fichier, FILE_IGNORE_NEW_LINES ) as $ligne ) {
		$ligne = rtrim( $ligne, "\r" );

		if ( '' === $ligne || '#' === $ligne[0] || false === strpos( $ligne, '=' ) ) {
			continue;
		}

		list( $cle, $valeur ) = explode( '=', $ligne, 2 );

		if ( ! preg_match( '/^[A-Za-z_][A-Za-z0-9_]*$/', $cle ) ) {
			continue;
		}

		if ( strlen( $valeur ) >= 2 && '"' === $valeur[0] && '"' === substr( $valeur, -1 ) ) {
			$valeur = substr( $valeur, 1, -1 );
		}

		$valeurs[ $cle ] = $valeur;
	}

	foreach ( $valeurs as $cle => $valeur ) {
		if ( false === getenv( $cle ) ) {
			putenv( "$cle=$valeur" ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- c'est précisément son rôle ici.
		}
	}
} )( __DIR__ . '/.env' );

/**
 * Lit une variable d'environnement obligatoire.
 *
 * Une variable absente arrête tout plutôt que de laisser WordPress démarrer à
 * moitié configuré : sans base, il proposerait son installeur ; sans clés, il
 * en générerait de nouvelles à chaque requête et déconnecterait tout le monde.
 *
 * @param string $nom Nom de la variable.
 * @return string
 */
function ag_env( $nom ) {
	$valeur = getenv( $nom );

	if ( false === $valeur || '' === $valeur ) {
		error_log( "[wp-config] variable d'environnement manquante : $nom" ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions

		if ( 'cli' === PHP_SAPI ) {
			fwrite( STDERR, "wp-config.php : variable d'environnement manquante : $nom\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			exit( 1 );
		}

		http_response_code( 503 );
		exit( 'Configuration incomplète.' );
	}

	return $valeur;
}

// ─── Base ────────────────────────────────────────────────────────────────────
// MySQL de l'hôte, par son socket (DB_HOST=localhost:/var/run/mysqld/mysqld.sock) :
// aucun port réseau, le dossier du socket est monté dans les conteneurs.
define( 'DB_NAME', ag_env( 'DB_NAME' ) );
define( 'DB_USER', ag_env( 'DB_USER' ) );
define( 'DB_PASSWORD', ag_env( 'DB_PASSWORD' ) );
define( 'DB_HOST', ag_env( 'DB_HOST' ) );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', 'utf8mb4_unicode_ci' );

$table_prefix = 'wp_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride

// ─── Clés et sels ────────────────────────────────────────────────────────────
// Générés une fois par bootstrap-vps.sh. Les changer déconnecte tout le monde,
// rien de plus.
foreach ( array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT' ) as $ag_cle ) {
	define( $ag_cle, ag_env( $ag_cle ) );
}
unset( $ag_cle );

// ─── Adresses ────────────────────────────────────────────────────────────────
// Fixées ici plutôt que lues en base : une base restaurée d'ailleurs ne peut
// pas renvoyer le site vers l'adresse de son poste d'origine.
define( 'WP_HOME', ag_env( 'WP_HOME' ) );
define( 'WP_SITEURL', ag_env( 'WP_SITEURL' ) );
define( 'FORCE_SSL_ADMIN', true );

// ─── Code en lecture seule ───────────────────────────────────────────────────
// Le code vient de l'image, propriété de root : WordPress ne pourrait de toute
// façon rien y écrire. Ces constantes retirent les boutons qui échoueraient
// (mises à jour, installation d'extensions, éditeur de fichiers). Toute mise à
// jour du noyau, du thème ou d'une extension passe par git, puis par la CI.
define( 'DISALLOW_FILE_MODS', true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );

// Remplacé par le service cron (docker/cron.sh), une passe par minute : il
// exécute les tâches planifiées de WordPress quel que soit le trafic.
define( 'DISABLE_WP_CRON', true );

define( 'WP_ENVIRONMENT_TYPE', 'production' );

// Les erreurs PHP vont dans les journaux du conteneur (docker/php.ini), jamais
// à l'écran ni dans un debug.log : le disque est en lecture seule.
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/public/' );
}

require_once ABSPATH . 'wp-settings.php';
