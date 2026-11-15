<?php
/**
 * Types de contenu : formations, équipe (enseignants et direction), événements.
 *
 * @package AlGhazzali\Core
 */

namespace AlGhazzali\Core\Content;

use AlGhazzali\Core\Hookable;

defined( 'ABSPATH' ) || exit;

final class PostTypes implements Hookable {

	public const COURSE  = 'ag_course';
	public const TEACHER = 'ag_teacher';
	public const EVENT   = 'ag_event';

	public function register(): void {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	public function register_post_types(): void {
		register_post_type(
			self::COURSE,
			array(
				'labels'        => $this->labels( __( 'Formations', 'al-ghazzali-institut' ), __( 'Formation', 'al-ghazzali-institut' ), __( 'Toutes les formations', 'al-ghazzali-institut' ) ),
				'description'   => __( 'Formations en langue arabe, organisées par niveaux.', 'al-ghazzali-institut' ),
				'public'        => true,
				'has_archive'   => 'formations',
				'rewrite'       => array(
					'slug'       => 'formations',
					'with_front' => false,
				),
				'menu_icon'     => 'dashicons-welcome-learn-more',
				'menu_position' => 20,
				'show_in_rest'  => true,
				'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
			)
		);

		register_post_type(
			self::TEACHER,
			array(
				'labels'        => $this->labels( __( 'Équipe', 'al-ghazzali-institut' ), __( 'Membre de l’équipe', 'al-ghazzali-institut' ), __( 'Toute l’équipe', 'al-ghazzali-institut' ) ),
				'description'   => __( 'Direction et enseignants de l’institut.', 'al-ghazzali-institut' ),
				'public'        => true,
				'has_archive'   => 'equipe',
				'rewrite'       => array(
					'slug'       => 'equipe',
					'with_front' => false,
				),
				'menu_icon'     => 'dashicons-groups',
				'menu_position' => 21,
				'show_in_rest'  => true,
				'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
			)
		);

		register_post_type(
			self::EVENT,
			array(
				'labels'        => $this->labels( __( 'Événements', 'al-ghazzali-institut' ), __( 'Événement', 'al-ghazzali-institut' ), __( 'Tous les événements', 'al-ghazzali-institut' ) ),
				'description'   => __( 'Réunions d’information, ateliers et rencontres de l’institut.', 'al-ghazzali-institut' ),
				'public'        => true,
				'has_archive'   => 'evenements',
				'rewrite'       => array(
					'slug'       => 'evenements',
					'with_front' => false,
				),
				'menu_icon'     => 'dashicons-calendar-alt',
				'menu_position' => 22,
				'show_in_rest'  => true,
				'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
			)
		);
	}

	/**
	 * @return array<string, string>
	 */
	private function labels( string $plural, string $singular, string $all ): array {
		return array(
			'name'               => $plural,
			'singular_name'      => $singular,
			'menu_name'          => $plural,
			'all_items'          => $all,
			'add_new'            => __( 'Ajouter', 'al-ghazzali-institut' ),
			/* translators: %s: nom du type de contenu au singulier. */
			'add_new_item'       => sprintf( __( 'Ajouter : %s', 'al-ghazzali-institut' ), $singular ),
			/* translators: %s: nom du type de contenu au singulier. */
			'edit_item'          => sprintf( __( 'Modifier : %s', 'al-ghazzali-institut' ), $singular ),
			/* translators: %s: nom du type de contenu au singulier. */
			'view_item'          => sprintf( __( 'Voir : %s', 'al-ghazzali-institut' ), $singular ),
			'search_items'       => __( 'Rechercher', 'al-ghazzali-institut' ),
			'not_found'          => __( 'Aucun élément trouvé.', 'al-ghazzali-institut' ),
			'not_found_in_trash' => __( 'Aucun élément dans la corbeille.', 'al-ghazzali-institut' ),
			'archives'           => $plural,
		);
	}
}
