<?php
/**
 * Sitemap XML natif (wp-sitemap.xml) : seulement les contenus utiles à l'indexation.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Seo;

use AlGhazzali\Hookable;

defined( 'ABSPATH' ) || exit;

final class Sitemaps implements Hookable {

	public function register(): void {
		add_filter( 'wp_sitemaps_add_provider', array( $this, 'providers' ), 10, 2 );
		add_filter( 'wp_sitemaps_taxonomies', array( $this, 'taxonomies' ) );
		add_filter( 'wp_sitemaps_posts_query_args', array( $this, 'exclude_noindex' ) );
	}

	/**
	 * Les pages d'auteur sont en noindex : on ne les soumet pas.
	 *
	 * @param \WP_Sitemaps_Provider|false $provider Fournisseur.
	 * @return \WP_Sitemaps_Provider|false
	 */
	public function providers( $provider, string $name ) {
		return 'users' === $name ? false : $provider;
	}

	/**
	 * Les étiquettes produisent des pages maigres : hors sitemap.
	 *
	 * @param array<string, \WP_Taxonomy> $taxonomies Taxonomies.
	 * @return array<string, \WP_Taxonomy>
	 */
	public function taxonomies( array $taxonomies ): array {
		unset( $taxonomies['post_tag'], $taxonomies['post_format'] );
		return $taxonomies;
	}

	/**
	 * La méta noindex n'existe que lorsqu'elle vaut « 1 » (supprimée sinon).
	 *
	 * @param array<string, mixed> $args Arguments WP_Query.
	 * @return array<string, mixed>
	 */
	public function exclude_noindex( array $args ): array {
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => SeoFields::NOINDEX,
				'compare' => 'NOT EXISTS',
			),
		);
		return $args;
	}
}
