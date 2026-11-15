<?php
/**
 * Détecte une extension SEO active, pour ne pas dupliquer ses balises.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Seo;

defined( 'ABSPATH' ) || exit;

final class SeoPlugins {

	public static function is_active(): bool {
		return defined( 'WPSEO_VERSION' )          // Yoast SEO.
			|| defined( 'RANK_MATH_VERSION' )      // Rank Math.
			|| defined( 'SEOPRESS_VERSION' )       // SEOPress.
			|| defined( 'AIOSEO_VERSION' )         // All in One SEO.
			|| defined( 'THE_SEO_FRAMEWORK_VERSION' );
	}
}
