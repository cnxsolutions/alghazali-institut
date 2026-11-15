<?php
/**
 * Ajuste les requêtes principales des archives (tri, pagination, événements à venir).
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Setup;

use AlGhazzali\Core\Content\Events;
use AlGhazzali\Core\Content\Fields;
use AlGhazzali\Core\Content\PostTypes;
use AlGhazzali\Hookable;

defined( 'ABSPATH' ) || exit;

final class Queries implements Hookable {

	public const COURSES_PER_PAGE = 12;

	// Plafond de sécurité : l'archive regroupe toute l'équipe par groupe, sans pagination.
	public const TEAM_LIMIT = 100;

	public function register(): void {
		add_action( 'pre_get_posts', array( $this, 'adjust' ) );
	}

	public function adjust( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->is_post_type_archive( PostTypes::COURSE ) ) {
			$query->set( 'posts_per_page', self::COURSES_PER_PAGE );
			$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
			return;
		}

		if ( $query->is_post_type_archive( PostTypes::TEACHER ) ) {
			$query->set( 'posts_per_page', self::TEAM_LIMIT );
			$query->set( 'no_found_rows', true );
			$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
			return;
		}

		if ( $query->is_post_type_archive( PostTypes::EVENT ) ) {
			$query->set( 'meta_query', Events::upcoming_meta_query() );
			$query->set( 'meta_key', Fields::EVENT_DATE );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
			$query->set( 'posts_per_page', 12 );
		}
	}
}
