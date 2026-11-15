<?php
/**
 * Chargement des ressources : CSS critique inline, CSS principal différé, JS en defer.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Assets;

use AlGhazzali\Hookable;

defined( 'ABSPATH' ) || exit;

final class Assets implements Hookable {

	// Polices visibles au-dessus de la ligne de flottaison (l'arabe est chargé à la demande via unicode-range).
	private const PRELOADED_FONTS = array(
		'assets/fonts/fraunces-latin-wght-normal.woff2',
		'assets/fonts/plus-jakarta-sans-latin-wght-normal.woff2',
	);

	public function register(): void {
		add_action( 'wp_head', array( $this, 'head_early' ), 0 );
		add_action( 'wp_head', array( $this, 'favicon_fallback' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'style_loader_tag', array( $this, 'defer_main_style' ), 10, 2 );
	}

	public function head_early(): void {
		// Bascule no-js -> js avant le rendu : les animations d'apparition ne masquent rien sans JavaScript.
		wp_print_inline_script_tag( "document.documentElement.classList.replace('no-js','js');" );

		foreach ( self::PRELOADED_FONTS as $font ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( AG_THEME_URI . '/' . $font ) );
		}
	}

	public function favicon_fallback(): void {
		if ( ! has_site_icon() ) {
			printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( AG_THEME_URI . '/assets/img/favicon.svg' ) );
		}
	}

	public function enqueue(): void {
		wp_register_style( 'ag-critical', false, array(), AG_THEME_VERSION );
		wp_enqueue_style( 'ag-critical' );
		wp_add_inline_style( 'ag-critical', $this->minified( 'assets/css/critical.css' ) );

		wp_enqueue_style( 'ag-main', AG_THEME_URI . '/assets/css/main.css', array( 'ag-critical' ), $this->version( 'assets/css/main.css' ) );

		wp_enqueue_script(
			'ag-app',
			AG_THEME_URI . '/assets/js/app.js',
			array(),
			$this->version( 'assets/js/app.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	/**
	 * Le CSS principal ne bloque pas le rendu : le CSS critique couvre l'écran initial.
	 */
	public function defer_main_style( string $tag, string $handle ): string {
		if ( 'ag-main' !== $handle ) {
			return $tag;
		}
		$deferred = (string) preg_replace( '/media=([\'"])all\1/', 'media="print" onload="this.media=\'all\'"', $tag, 1 );
		return $deferred . '<noscript>' . trim( $tag ) . "</noscript>\n";
	}

	private function minified( string $relative ): string {
		$css = (string) file_get_contents( AG_THEME_DIR . '/' . $relative ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- fichier local du thème.
		$css = (string) preg_replace( '#/\*.*?\*/#s', '', $css );
		$css = (string) preg_replace( '/\s+/', ' ', $css );
		$css = (string) preg_replace( '/\s*([{};,>])\s*/', '$1', $css );
		// Seulement l'espace après « : » : celui d'avant porte du sens dans « .nav :focus ».
		return trim( (string) preg_replace( '/:\s+/', ':', $css ) );
	}

	private function version( string $relative ): string {
		$mtime = filemtime( AG_THEME_DIR . '/' . $relative );
		return false !== $mtime ? (string) $mtime : AG_THEME_VERSION;
	}
}
