<?php
/**
 * Écran d'édition des formations, de l'équipe et des événements.
 *
 * Ces contenus sont surtout des zones structurées : l'éditeur classique les affiche en pleine
 * largeur sous le texte, là où l'éditeur de blocs les replie dans un panneau en bas d'écran.
 *
 * @package AlGhazzali\Core
 */

namespace AlGhazzali\Core\Admin;

use AlGhazzali\Core\Content\PostTypes;
use AlGhazzali\Core\Hookable;

defined( 'ABSPATH' ) || exit;

final class Editor implements Hookable {

	private const STRUCTURED = array( PostTypes::COURSE, PostTypes::TEACHER, PostTypes::EVENT );

	public function register(): void {
		add_filter( 'use_block_editor_for_post_type', array( $this, 'block_editor' ), 10, 2 );
		add_filter( 'default_hidden_meta_boxes', array( $this, 'show_excerpt' ), 10, 2 );
	}

	public function block_editor( bool $use_block_editor, string $post_type ): bool {
		return in_array( $post_type, self::STRUCTURED, true ) ? false : $use_block_editor;
	}

	/**
	 * L'extrait sert de chapeau et de présentation courte : visible sans passer par les options de l'écran.
	 *
	 * @param string[] $hidden Boîtes masquées par défaut.
	 * @return string[]
	 */
	public function show_excerpt( array $hidden, \WP_Screen $screen ): array {
		return in_array( $screen->post_type, self::STRUCTURED, true ) ? array_values( array_diff( $hidden, array( 'postexcerpt' ) ) ) : $hidden;
	}
}
