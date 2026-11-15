<?php
/**
 * Fonctionnalités WordPress activées par le thème.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Setup;

use AlGhazzali\Hookable;

defined( 'ABSPATH' ) || exit;

final class ThemeSupport implements Hookable {

	public function register(): void {
		add_action( 'after_setup_theme', array( $this, 'setup' ) );
	}

	public function setup(): void {
		load_theme_textdomain( 'al-ghazzali', AG_THEME_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 96,
				'width'       => 320,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);

		// Le chapeau des pages alimente le bandeau et la meta description.
		add_post_type_support( 'page', 'excerpt' );

		add_image_size( 'ag-card', 720, 480, true );
		add_image_size( 'ag-portrait', 480, 560, true );
		add_image_size( 'ag-og', 1200, 630, true );

		register_nav_menus(
			array(
				'primary' => __( 'Menu principal', 'al-ghazzali' ),
				'footer'  => __( 'Pied de page : l’institut', 'al-ghazzali' ),
				'legal'   => __( 'Pied de page : liens légaux', 'al-ghazzali' ),
			)
		);
	}
}
