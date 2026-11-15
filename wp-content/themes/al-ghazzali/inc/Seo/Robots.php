<?php
/**
 * Directives robots : pages sans valeur de recherche en noindex, robots.txt complété.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Seo;

use AlGhazzali\Hookable;

defined( 'ABSPATH' ) || exit;

final class Robots implements Hookable {

	public function __construct( private readonly Context $context ) {}

	public function register(): void {
		add_filter( 'wp_robots', array( $this, 'directives' ) );
		// Avant le « Sitemap: » ajouté par le cœur (priorité 0), pour rester dans le groupe User-agent.
		add_filter( 'robots_txt', array( $this, 'robots_txt' ), -1, 2 );
	}

	/**
	 * @param array<string, bool|string> $robots Directives.
	 * @return array<string, bool|string>
	 */
	public function directives( array $robots ): array {
		if ( $this->is_noindex() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}

	public function robots_txt( string $output, bool $is_public ): string {
		if ( ! $is_public ) {
			return $output;
		}
		$path = (string) wp_parse_url( home_url(), PHP_URL_PATH );
		return $output . "Disallow: {$path}/?s=\nDisallow: {$path}/search/\n";
	}

	private function is_noindex(): bool {
		// Résultats de recherche, archives d'auteur/date et pages de fichiers : contenu dupliqué ou pauvre.
		if ( is_search() || is_404() || is_author() || is_date() || is_attachment() ) {
			return true;
		}

		$post_id = $this->context->post_id();
		return $post_id > 0 && '1' === get_post_meta( $post_id, SeoFields::NOINDEX, true );
	}
}
