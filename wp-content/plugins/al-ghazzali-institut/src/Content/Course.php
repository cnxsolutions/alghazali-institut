<?php
/**
 * Lecture d'une formation : listes publiées et zones saisies (niveaux, points clés, FAQ…).
 *
 * Les gabarits ne lisent jamais les métas brutes : ils passent par ces méthodes typées.
 *
 * @package AlGhazzali\Core
 */

namespace AlGhazzali\Core\Content;

defined( 'ABSPATH' ) || exit;

final class Course {

	/**
	 * Formations publiées, dans l'ordre choisi dans l'admin (attribut « Ordre »).
	 *
	 * @return \WP_Post[]
	 */
	public static function published( int $limit = 12, int $exclude = 0 ): array {
		$args = array(
			'post_type'      => PostTypes::COURSE,
			'posts_per_page' => $limit,
			'no_found_rows'  => true,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
		);
		if ( $exclude > 0 ) {
			$args['post__not_in'] = array( $exclude ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- une seule exclusion, liste bornée.
		}
		return get_posts( $args );
	}

	/**
	 * @return list<array{name: string, subtitle: string, note: string, description: string, objectives: list<string>, programme: list<string>, method: string, material: string, price: string, price_note: string}>
	 */
	public static function levels( int $post_id ): array {
		$levels = array();
		foreach ( self::rows( $post_id, Fields::LEVELS, array( 'name', 'subtitle', 'note', 'description', 'objectives', 'programme', 'method', 'material', 'price', 'price_note' ) ) as $row ) {
			if ( '' === $row['name'] ) {
				continue;
			}
			$levels[] = array(
				'name'        => $row['name'],
				'subtitle'    => $row['subtitle'],
				'note'        => $row['note'],
				'description' => $row['description'],
				'objectives'  => self::lines( $row['objectives'] ),
				'programme'   => self::lines( $row['programme'] ),
				'method'      => $row['method'],
				'material'    => $row['material'],
				'price'       => $row['price'],
				'price_note'  => $row['price_note'],
			);
		}
		return $levels;
	}

	/**
	 * @return list<array{label: string, title: string, text: string}>
	 */
	public static function features( int $post_id ): array {
		return array_values(
			array_filter(
				self::rows( $post_id, Fields::FEATURES, array( 'label', 'title', 'text' ) ),
				static fn( array $row ): bool => '' !== $row['title']
			)
		);
	}

	/**
	 * @return list<string>
	 */
	public static function steps( int $post_id ): array {
		return self::lines( (string) get_post_meta( $post_id, Fields::STEPS, true ) );
	}

	/**
	 * @return list<array{value: string, label: string}>
	 */
	public static function discounts( int $post_id ): array {
		return array_values(
			array_filter(
				self::rows( $post_id, Fields::DISCOUNTS, array( 'value', 'label' ) ),
				static fn( array $row ): bool => '' !== $row['value'] && '' !== $row['label']
			)
		);
	}

	/**
	 * @return list<array{question: string, answer: string}>
	 */
	public static function faq( int $post_id ): array {
		return array_values(
			array_filter(
				self::rows( $post_id, Fields::FAQ, array( 'question', 'answer' ) ),
				static fn( array $row ): bool => '' !== $row['question'] && '' !== $row['answer']
			)
		);
	}

	/**
	 * Créneaux saisis, bruts (validés par Schedule).
	 *
	 * @return list<array<string, string>>
	 */
	public static function slots( int $post_id ): array {
		return self::rows( $post_id, Fields::SLOTS, array( 'day', 'start', 'end', 'level', 'public', 'teacher', 'mode' ) );
	}

	/**
	 * Classes ouvertes (hommes, femmes), déduites des créneaux, dans l'ordre de la liste des publics.
	 *
	 * @return array<string, string>
	 */
	public static function publics( int $post_id ): array {
		$used = array_column( self::slots( $post_id ), 'public' );
		return array_intersect_key( Fields::publics(), array_flip( $used ) );
	}

	/**
	 * Une ligne de texte = un élément de liste (lignes vides ignorées).
	 *
	 * @return list<string>
	 */
	public static function lines( string $text ): array {
		return array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', $text ) ?: array() ), 'strlen' ) );
	}

	/**
	 * Lignes d'une liste répétable : chaque colonne attendue existe et vaut une chaîne.
	 *
	 * @param string[] $columns Colonnes lues.
	 * @return list<array<string, string>>
	 */
	private static function rows( int $post_id, string $key, array $columns ): array {
		$rows = get_post_meta( $post_id, $key, true );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$clean = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$values = array();
			foreach ( $columns as $column ) {
				$values[ $column ] = is_scalar( $row[ $column ] ?? null ) ? (string) $row[ $column ] : '';
			}
			$clean[] = $values;
		}
		return $clean;
	}
}
