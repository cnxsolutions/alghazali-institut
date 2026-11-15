<?php
/**
 * État actif du menu : corrige le comportement par défaut sur les contenus personnalisés.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Setup;

use AlGhazzali\Hookable;

defined( 'ABSPATH' ) || exit;

final class MenuState implements Hookable {

	public function register(): void {
		add_filter( 'nav_menu_css_class', array( $this, 'classes' ), 10, 2 );
	}

	/**
	 * @param string[] $classes Classes de l'élément.
	 * @return string[]
	 */
	public function classes( array $classes, \WP_Post $item ): array {
		// WordPress marque la page des articles comme parente de presque tout (formations, 404…).
		if ( 'page' === $item->object && (int) $item->object_id === (int) get_option( 'page_for_posts' ) && ! $this->is_blog() ) {
			$classes = array_values( array_diff( $classes, array( 'current_page_parent' ) ) );
		}

		// Sur une fiche (formation, professeur…), son archive dans le menu devient l'ancêtre actif.
		$post_type = $this->current_post_type();
		if ( '' !== $post_type && 'post_type_archive' === $item->type && $item->object === $post_type && ! is_post_type_archive() ) {
			$classes[] = 'current-menu-ancestor';
		}
		return $classes;
	}

	private function is_blog(): bool {
		return is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_author() || is_date();
	}

	private function current_post_type(): string {
		if ( is_singular() ) {
			return (string) get_post_type();
		}
		if ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );
			return (string) ( is_array( $post_type ) ? reset( $post_type ) : $post_type );
		}
		return '';
	}
}
