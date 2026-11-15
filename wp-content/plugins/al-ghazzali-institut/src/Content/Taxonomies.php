<?php
/**
 * Groupes de l'équipe (direction, enseignants, enseignantes…), modifiables dans l'admin.
 *
 * @package AlGhazzali\Core
 */

namespace AlGhazzali\Core\Content;

use AlGhazzali\Core\Hookable;

defined( 'ABSPATH' ) || exit;

final class Taxonomies implements Hookable {

	public const TEAM_GROUP = 'ag_team_group';

	public function register(): void {
		add_action( 'init', array( $this, 'register_taxonomies' ) );
	}

	public function register_taxonomies(): void {
		register_taxonomy(
			self::TEAM_GROUP,
			array( PostTypes::TEACHER ),
			array(
				'labels'            => array(
					'name'          => __( 'Groupes', 'al-ghazzali-institut' ),
					'singular_name' => __( 'Groupe', 'al-ghazzali-institut' ),
					'all_items'     => __( 'Tous les groupes', 'al-ghazzali-institut' ),
					'add_new_item'  => __( 'Ajouter un groupe', 'al-ghazzali-institut' ),
					'edit_item'     => __( 'Modifier le groupe', 'al-ghazzali-institut' ),
				),
				'description'       => __( 'Les groupes s’affichent sur la page Équipe dans leur ordre de création.', 'al-ghazzali-institut' ),
				// Simple regroupement d'affichage : pas de page publique par groupe.
				'public'            => false,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'rewrite'           => false,
			)
		);
	}

	/**
	 * Groupes de l'équipe, dans l'ordre de création.
	 *
	 * @return \WP_Term[]
	 */
	public static function team_groups(): array {
		$terms = get_terms(
			array(
				'taxonomy'   => self::TEAM_GROUP,
				'hide_empty' => false,
				'orderby'    => 'term_id',
			)
		);
		return is_array( $terms ) ? $terms : array();
	}
}
