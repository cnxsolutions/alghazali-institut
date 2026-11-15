<?php
/**
 * Composition du thème : chaque module branche ses propres hooks.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali;

defined( 'ABSPATH' ) || exit;

final class Theme {

	/**
	 * Instancie et enregistre les modules.
	 */
	public function boot(): void {
		$institute   = new Settings\Institute();
		$context     = new Seo\Context( $institute );
		$breadcrumbs = new Seo\Breadcrumbs();

		// Si une extension SEO est active, elle gère les balises meta : on évite les doublons.
		$standalone_seo = ! Seo\SeoPlugins::is_active();

		$modules = array(
			new Setup\ThemeSupport(),
			new Setup\Cleanup(),
			new Setup\MenuState(),
			new Setup\Queries(),
			new Assets\Assets(),
			new Settings\Customizer(),
			new Seo\Sitemaps(),
			new Seo\Schema( $context, $breadcrumbs, $institute, $standalone_seo ),
		);

		if ( $standalone_seo ) {
			$modules[] = new Seo\HeadTags( $context );
			$modules[] = new Seo\Robots( $context );
			$modules[] = new Seo\SeoBox();
		}

		foreach ( $modules as $module ) {
			$module->register();
		}
	}
}
