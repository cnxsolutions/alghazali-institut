<?php
/**
 * Styles et script des boîtes de saisie, chargés uniquement sur l'écran d'édition d'un contenu.
 *
 * @package AlGhazzali\Core
 */

namespace AlGhazzali\Core\Admin;

use AlGhazzali\Core\Hookable;

defined( 'ABSPATH' ) || exit;

final class Assets implements Hookable {

	public function register(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function enqueue( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_style( 'ag-core-admin', AG_CORE_URI . 'assets/admin.css', array(), AG_CORE_VERSION );
		wp_enqueue_script(
			'ag-core-admin',
			AG_CORE_URI . 'assets/admin.js',
			array(),
			AG_CORE_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
}
