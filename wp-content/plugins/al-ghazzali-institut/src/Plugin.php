<?php
/**
 * Composition de l'extension : chaque module branche ses propres hooks.
 *
 * @package AlGhazzali\Core
 */

namespace AlGhazzali\Core;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	public function boot(): void {
		$modules = array(
			new Content\PostTypes(),
			new Content\Taxonomies(),
			new Content\MetaBoxes(),
			new Admin\Assets(),
			new Admin\Editor(),
		);

		foreach ( $modules as $module ) {
			$module->register();
		}
	}

	/**
	 * Activation : les URL des formations, de l'équipe et des événements doivent exister tout de suite.
	 */
	public static function activate(): void {
		( new Content\PostTypes() )->register_post_types();
		( new Content\Taxonomies() )->register_taxonomies();
		flush_rewrite_rules();
	}
}
