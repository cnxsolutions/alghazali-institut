<?php
/**
 * Plugin Name: Al Ghazali Institut — point de santé
 * Description: Répond sur /up avec l'état de la base, de l'installation et des médias. Interrogé par deploy/deploy.sh après chaque mise en service.
 * Version: 1.0.0
 * Author: CNX Solutions
 *
 * Extension « must-use » : chargée d'office, impossible à désactiver depuis
 * l'administration. Un point de santé qu'on pourrait éteindre d'un clic ne
 * prouverait plus grand-chose.
 *
 * Accroché à `wp_loaded`, donc APRÈS le chargement du thème et des extensions :
 * une erreur fatale dans functions.php fait échouer /up, ce qui est voulu. Un
 * point de santé qui répondrait avant le thème validerait une version cassée.
 *
 * Les autres cas d'échec n'arrivent même pas jusqu'ici, et c'est tout aussi
 * bien : base injoignable (WordPress répond 500), base vide (il redirige vers
 * son installeur). Dans tous les cas, ce n'est pas un 200.
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

/**
 * Répond à GET /up, puis arrête la requête.
 */
function ag_sante_repondre() {
	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	$chemin = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );

	if ( '/up' !== $chemin ) {
		return;
	}

	global $wpdb;

	$base     = '1' === (string) $wpdb->get_var( 'SELECT 1' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$installe = $base && is_blog_installed();

	// Un dossier des médias non inscriptible (montage absent, mauvais
	// propriétaire) casse les téléversements de l'administration, et ne se
	// verrait sinon qu'au premier essai.
	$medias   = wp_upload_dir( null, false );
	$ecriture = empty( $medias['error'] ) && wp_is_writable( $medias['basedir'] );

	$ok = $base && $installe && $ecriture;

	nocache_headers();
	status_header( $ok ? 200 : 503 );
	header( 'Content-Type: application/json; charset=utf-8' );

	echo wp_json_encode(
		array(
			'statut'   => $ok ? 'ok' : 'erreur',
			'base'     => $base,
			'installe' => $installe,
			'medias'   => $ecriture,
			'version'  => getenv( 'APP_VERSION' ) ? getenv( 'APP_VERSION' ) : null,
		)
	);
	exit;
}
add_action( 'wp_loaded', 'ag_sante_repondre', 0 );
