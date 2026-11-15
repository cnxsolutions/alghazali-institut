<?php
/**
 * Balises du <head> : titre, meta description, canonical, Open Graph et Twitter.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Seo;

use AlGhazzali\Hookable;

defined( 'ABSPATH' ) || exit;

final class HeadTags implements Hookable {

	public function __construct( private readonly Context $context ) {}

	public function register(): void {
		add_filter( 'pre_get_document_title', array( $this, 'document_title' ) );
		add_filter( 'document_title_separator', array( $this, 'separator' ) );

		// Remplacé par notre canonical, qui couvre aussi les archives paginées.
		remove_action( 'wp_head', 'rel_canonical' );
		add_action( 'wp_head', array( $this, 'output' ), 1 );
	}

	public function document_title( string $title ): string {
		$custom = $this->context->custom_title();
		return '' !== $custom ? $custom : $title;
	}

	public function separator(): string {
		return '–';
	}

	public function output(): void {
		$description = $this->context->description();
		$canonical   = $this->context->canonical();
		$image       = $this->context->image();
		$title       = $this->context->title();

		$tags = array(
			array( 'name', 'description', $description ),
			array( 'property', 'og:locale', 'fr_FR' ),
			array( 'property', 'og:type', $this->context->og_type() ),
			array( 'property', 'og:site_name', get_bloginfo( 'name' ) ),
			array( 'property', 'og:title', $title ),
			array( 'property', 'og:description', $description ),
			array( 'property', 'og:url', $canonical ),
			array( 'property', 'og:image', $image['url'] ),
			array( 'property', 'og:image:width', (string) $image['width'] ),
			array( 'property', 'og:image:height', (string) $image['height'] ),
			array( 'property', 'og:image:alt', $image['alt'] ),
			array( 'name', 'twitter:card', 'summary_large_image' ),
		);

		if ( is_singular( 'post' ) ) {
			$tags[] = array( 'property', 'article:published_time', (string) get_the_date( 'c' ) );
			$tags[] = array( 'property', 'article:modified_time', (string) get_the_modified_date( 'c' ) );
		}

		if ( '' !== $canonical ) {
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
		}

		foreach ( $tags as list( $attribute, $name, $content ) ) {
			if ( '' === $content ) {
				continue;
			}
			$value = in_array( $name, array( 'og:url', 'og:image' ), true ) ? esc_url( $content ) : esc_attr( $content );
			printf( '<meta %1$s="%2$s" content="%3$s">' . "\n", esc_attr( $attribute ), esc_attr( $name ), $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé ci-dessus selon le type.
		}
	}
}
