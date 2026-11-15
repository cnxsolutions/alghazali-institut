<?php
/**
 * Allège le <head> et ferme les surfaces inutiles (emojis, XML-RPC, commentaires).
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Setup;

use AlGhazzali\Hookable;

defined( 'ABSPATH' ) || exit;

final class Cleanup implements Hookable {

	public function register(): void {
		// Emojis : script + styles chargés sur chaque page pour un usage quasi nul.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );

		// Balises sans valeur pour le visiteur ni pour le référencement.
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

		// Surface d'attaque : XML-RPC n'est utilisé par aucun service du site.
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'wp_headers', array( $this, 'remove_pingback_header' ) );

		// Pas de modération prévue : commentaires et pings fermés partout (anti-spam).
		add_filter( 'comments_open', '__return_false', 20 );
		add_filter( 'pings_open', '__return_false', 20 );
		add_filter( 'feed_links_show_comments_feed', '__return_false' );

		// Ne charger que les styles des blocs réellement présents dans la page.
		add_filter( 'should_load_separate_core_block_assets', '__return_true' );
	}

	/**
	 * @param array<string, string> $headers En-têtes HTTP.
	 * @return array<string, string>
	 */
	public function remove_pingback_header( array $headers ): array {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
}
