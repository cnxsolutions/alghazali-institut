<?php
/**
 * Règles des événements : à venir / passés, dates au format ISO.
 *
 * @package AlGhazzali\Core
 */

namespace AlGhazzali\Core\Content;

defined( 'ABSPATH' ) || exit;

final class Events {

	/**
	 * Filtre « date >= aujourd'hui » (fuseau du site).
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function upcoming_meta_query(): array {
		return array(
			array(
				'key'     => Fields::EVENT_DATE,
				'value'   => current_time( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'DATE',
			),
		);
	}

	/**
	 * @return \WP_Post[]
	 */
	public static function upcoming( int $limit ): array {
		return get_posts(
			array(
				'post_type'      => PostTypes::EVENT,
				'posts_per_page' => $limit,
				'no_found_rows'  => true,
				'meta_key'       => Fields::EVENT_DATE, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => self::upcoming_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);
	}

	/**
	 * @return \WP_Post[]
	 */
	public static function past( int $limit ): array {
		return get_posts(
			array(
				'post_type'      => PostTypes::EVENT,
				'posts_per_page' => $limit,
				'no_found_rows'  => true,
				'meta_key'       => Fields::EVENT_DATE, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'orderby'        => 'meta_value',
				'order'          => 'DESC',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => Fields::EVENT_DATE,
						'value'   => current_time( 'Y-m-d' ),
						'compare' => '<',
						'type'    => 'DATE',
					),
				),
			)
		);
	}

	/**
	 * Date + heure ISO 8601 dans le fuseau du site, ou chaîne vide.
	 */
	public static function iso( int $post_id, string $time_key ): string {
		$date = (string) get_post_meta( $post_id, Fields::EVENT_DATE, true );
		if ( '' === $date ) {
			return '';
		}

		$time     = (string) get_post_meta( $post_id, $time_key, true );
		$datetime = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $date . ' ' . ( '' !== $time ? $time : '00:00' ), wp_timezone() );

		if ( false === $datetime ) {
			return '';
		}
		return '' !== $time ? $datetime->format( 'c' ) : $date;
	}
}
