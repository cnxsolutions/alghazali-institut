<?php
/**
 * Fil d'Ariane de la page courante, partagé par l'affichage et le JSON-LD.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Seo;

use AlGhazzali\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

final class Breadcrumbs {

	/**
	 * @return list<array{name: string, url: string}> Vide sur la page d'accueil.
	 */
	public function items(): array {
		if ( is_front_page() ) {
			return array();
		}

		$items = array( self::item( __( 'Accueil', 'al-ghazzali' ), home_url( '/' ) ) );

		if ( is_singular( array( PostTypes::COURSE, PostTypes::TEACHER, PostTypes::EVENT ) ) ) {
			$items[] = $this->archive_item( (string) get_post_type() );
		} elseif ( is_singular( 'post' ) ) {
			$blog_id = (int) get_option( 'page_for_posts' );
			if ( $blog_id > 0 ) {
				$items[] = self::item( get_the_title( $blog_id ), (string) get_permalink( $blog_id ) );
			}
			$category = $this->first_term( (int) get_queried_object_id(), 'category' );
			if ( null !== $category ) {
				$items[] = $category;
			}
		} elseif ( is_page() ) {
			foreach ( array_reverse( get_post_ancestors( (int) get_queried_object_id() ) ) as $ancestor_id ) {
				$items[] = self::item( get_the_title( $ancestor_id ), (string) get_permalink( $ancestor_id ) );
			}
		}

		$current = $this->current();
		if ( null !== $current ) {
			$items[] = $current;
		}
		return $items;
	}

	/**
	 * @return array{name: string, url: string}|null
	 */
	private function current(): ?array {
		if ( is_singular() ) {
			return self::item( single_post_title( '', false ), (string) get_permalink() );
		}
		if ( is_home() ) {
			$blog_id = (int) get_option( 'page_for_posts' );
			return self::item( $blog_id > 0 ? get_the_title( $blog_id ) : __( 'Blog', 'al-ghazzali' ), $blog_id > 0 ? (string) get_permalink( $blog_id ) : '' );
		}
		if ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );
			return $this->archive_item( (string) ( is_array( $post_type ) ? reset( $post_type ) : $post_type ) );
		}
		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			$link = $term instanceof \WP_Term ? get_term_link( $term ) : '';
			return self::item( single_term_title( '', false ), is_string( $link ) ? $link : '' );
		}
		if ( is_search() ) {
			/* translators: %s: termes recherchés. */
			return self::item( sprintf( __( 'Recherche : %s', 'al-ghazzali' ), get_search_query() ), '' );
		}
		if ( is_404() ) {
			return self::item( __( 'Page introuvable', 'al-ghazzali' ), '' );
		}
		return null;
	}

	/**
	 * @return array{name: string, url: string}
	 */
	private function archive_item( string $post_type ): array {
		$seo  = Archives::seo( $post_type );
		$name = null !== $seo ? $seo['heading'] : (string) post_type_archive_title( '', false );
		return self::item( $name, (string) get_post_type_archive_link( $post_type ) );
	}

	/**
	 * @return array{name: string, url: string}|null
	 */
	private function first_term( int $post_id, string $taxonomy ): ?array {
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( ! is_array( $terms ) || array() === $terms ) {
			return null;
		}
		$link = get_term_link( $terms[0] );
		return self::item( $terms[0]->name, is_string( $link ) ? $link : '' );
	}

	/**
	 * @return array{name: string, url: string}
	 */
	private static function item( string $name, string $url ): array {
		return array(
			'name' => wp_strip_all_tags( $name ),
			'url'  => $url,
		);
	}
}
