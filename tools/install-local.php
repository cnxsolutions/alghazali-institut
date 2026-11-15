<?php
/**
 * Installation LOCALE de WordPress puis injection du contenu de départ.
 *
 * Usage : php tools/install-local.php [hôte]   (défaut : alghazzali.localhost)
 * Idempotent : relançable sans doublon. Ne jamais exécuter sur la production.
 *
 * @package AlGhazzali
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( 1 );
}

$_SERVER['HTTP_HOST']   = $argv[1] ?? 'alghazzali.localhost';
$_SERVER['REQUEST_URI'] = '/';

define( 'WP_INSTALLING', true );

// L'e-mail « nouveau site » n'a pas de destinataire réel en local.
function wp_new_blog_notification(): void {}

require dirname( __DIR__ ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

if ( 'local' !== wp_get_environment_type() ) {
	fwrite( STDERR, "Refusé : WP_ENVIRONMENT_TYPE doit valoir « local ».\n" );
	exit( 1 );
}

if ( ! is_blog_installed() ) {
	$password = wp_generate_password( 20, true, false );
	$result   = wp_install( 'Al Ghazzali Institut', 'cnx-admin', 'admin@alghazzali.localhost', true, '', $password, 'fr_FR' );
	echo "WordPress installé.\nIdentifiant : cnx-admin\nMot de passe : {$password}\n";
	if ( empty( $result['user_id'] ) ) {
		fwrite( STDERR, "Échec de l'installation.\n" );
		exit( 1 );
	}
} else {
	echo "WordPress déjà installé : mise à jour du contenu uniquement.\n";
}

switch_theme( 'al-ghazzali' );

// Le thème n'est pas chargé en mode installation : le contenu est injecté dans un second processus.
passthru( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __DIR__ . '/seed.php' ) . ' ' . escapeshellarg( $_SERVER['HTTP_HOST'] ), $status );
exit( $status );
