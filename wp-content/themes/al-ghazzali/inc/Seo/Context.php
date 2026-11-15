<?php
/**
 * Calcule les données SEO de la page courante : titre, description, URL canonique, image.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Seo;

use AlGhazzali\Settings\Institute;

defined( 'ABSPATH' ) || exit;

final class Context {

	private const DESCRIPTION_MAX = 158;

	public function __construct( private readonly Institute $institute ) {}

	/**
	 * Contenu (article, page, page des articles) portant les réglages SEO de la requête, ou 0.
	 */
	public function post_id(): int {
		if ( is_singular() ) {
			return (int) get_queried_object_id();
		}
		if ( is_home() && ! is_front_page() ) {
			return (int) get_option( 'page_for_posts' );
		}
		return 0;
	}

	/**
	 * Titre saisi manuellement (page ou archive), ou chaîne vide pour laisser WordPress le composer.
	 */
	public function custom_title(): string {
		$post_id = $this->post_id();
		if ( $post_id > 0 ) {
			return (string) get_post_meta( $post_id, SeoFields::TITLE, true );
		}

		$archive = $this->archive_seo();
		if ( null === $archive ) {
			return '';
		}

		$title = $archive['title'];
		$paged = (int) get_query_var( 'paged' );
		if ( $paged > 1 ) {
			/* translators: %d: numéro de page. */
			$title .= ' – ' . sprintf( __( 'Page %d', 'al-ghazzali' ), $paged );
		}
		return $title;
	}

	public function title(): string {
		return wp_get_document_title();
	}

	public function description(): string {
		$post_id = $this->post_id();
		$text    = '';

		if ( $post_id > 0 ) {
			$text = (string) get_post_meta( $post_id, SeoFields::DESCRIPTION, true );
			if ( '' === $text ) {
				$post = get_post( $post_id );
				if ( $post instanceof \WP_Post ) {
					$text = has_excerpt( $post ) ? $post->post_excerpt : excerpt_remove_blocks( strip_shortcodes( $post->post_content ) );
				}
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$text = (string) term_description();
		} else {
			$text = $this->archive_seo()['description'] ?? '';
		}

		$text = self::clean( $text );
		return '' !== $text ? $text : self::clean( $this->institute->get( 'default_description' ) );
	}

	public function canonical(): string {
		if ( is_404() || is_search() ) {
			return '';
		}
		if ( is_front_page() ) {
			return home_url( '/' );
		}
		if ( is_singular() ) {
			return (string) wp_get_canonical_url();
		}

		$url = '';
		if ( is_home() ) {
			$url = (string) get_permalink( (int) get_option( 'page_for_posts' ) );
		} elseif ( is_post_type_archive() ) {
			$url = (string) get_post_type_archive_link( $this->archive_post_type() );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$link = get_term_link( get_queried_object() );
			$url  = is_wp_error( $link ) ? '' : $link;
		}

		if ( '' === $url ) {
			return '';
		}

		$paged = (int) get_query_var( 'paged' );
		return $paged > 1 ? trailingslashit( $url ) . user_trailingslashit( 'page/' . $paged, 'paged' ) : $url;
	}

	/**
	 * @return array{url: string, width: int, height: int, alt: string}
	 */
	public function image(): array {
		$post_id       = $this->post_id();
		$attachment_id = ( $post_id > 0 && has_post_thumbnail( $post_id ) )
			? (int) get_post_thumbnail_id( $post_id )
			: (int) $this->institute->get( 'og_image' );

		if ( $attachment_id > 0 ) {
			$src = wp_get_attachment_image_src( $attachment_id, 'ag-og' );
			if ( is_array( $src ) ) {
				return array(
					'url'    => $src[0],
					'width'  => (int) $src[1],
					'height' => (int) $src[2],
					'alt'    => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				);
			}
		}

		return array(
			'url'    => AG_THEME_URI . '/assets/img/og-default.jpg',
			'width'  => 1200,
			'height' => 630,
			'alt'    => get_bloginfo( 'name' ),
		);
	}

	public function og_type(): string {
		return is_singular( 'post' ) ? 'article' : 'website';
	}

	/**
	 * Titre, description, intitulé et chapeau de l'archive de type de contenu courante.
	 *
	 * @return array{title: string, description: string, heading: string, lead: string}|null
	 */
	public function archive_seo(): ?array {
		if ( ! is_post_type_archive() ) {
			return null;
		}
		return Archives::seo( $this->archive_post_type() );
	}

	private function archive_post_type(): string {
		$post_type = get_query_var( 'post_type' );
		return (string) ( is_array( $post_type ) ? reset( $post_type ) : $post_type );
	}

	/**
	 * Texte brut d'une ligne, coupé proprement à la longueur d'une meta description.
	 */
	private static function clean( string $text ): string {
		$text = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $text ) ) );
		if ( mb_strlen( $text ) <= self::DESCRIPTION_MAX ) {
			return $text;
		}

		$cut  = mb_substr( $text, 0, self::DESCRIPTION_MAX );
		$last = mb_strrpos( $cut, ' ' );
		return rtrim( false !== $last ? mb_substr( $cut, 0, $last ) : $cut, ' ,;:.' ) . '…';
	}
}
