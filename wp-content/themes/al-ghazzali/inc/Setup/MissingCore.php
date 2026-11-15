<?php
/**
 * Extension « Al Ghazali Institut » absente : le site se met en pause proprement (503) au lieu de casser.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Setup;

use AlGhazzali\Hookable;

defined( 'ABSPATH' ) || exit;

final class MissingCore implements Hookable {

	public function register(): void {
		add_action( 'admin_notices', array( $this, 'notice' ) );
		add_action( 'template_redirect', array( $this, 'maintenance' ) );
	}

	public function notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-error"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
			esc_html__( 'Le thème Al Ghazali a besoin de l’extension « Al Ghazali Institut » (formations, équipe, emploi du temps). Le site est en maintenance tant qu’elle est désactivée.', 'al-ghazzali' ),
			esc_url( admin_url( 'plugins.php' ) ),
			esc_html__( 'Activer l’extension', 'al-ghazzali' )
		);
	}

	public function maintenance(): void {
		wp_die(
			esc_html__( 'Le site est momentanément en maintenance. Merci de revenir dans quelques instants.', 'al-ghazzali' ),
			esc_html__( 'Maintenance', 'al-ghazzali' ),
			array( 'response' => 503 )
		);
	}
}
