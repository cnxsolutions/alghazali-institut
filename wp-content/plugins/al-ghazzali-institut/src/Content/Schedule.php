<?php
/**
 * Emploi du temps : créneaux saisis sur chaque formation, regroupés par jour.
 *
 * @package AlGhazzali\Core
 */

namespace AlGhazzali\Core\Content;

defined( 'ABSPATH' ) || exit;

/**
 * Un créneau validé :
 * array{day: int, start: string, end: string, level: string, public: string, teacher: int, mode: string, course: \WP_Post}
 */
final class Schedule {

	// Plafond de sécurité de la requête hebdomadaire.
	private const COURSES_LIMIT = 100;

	/**
	 * @return array<int, string>
	 */
	public static function days(): array {
		return array(
			1 => __( 'Lundi', 'al-ghazzali-institut' ),
			2 => __( 'Mardi', 'al-ghazzali-institut' ),
			3 => __( 'Mercredi', 'al-ghazzali-institut' ),
			4 => __( 'Jeudi', 'al-ghazzali-institut' ),
			5 => __( 'Vendredi', 'al-ghazzali-institut' ),
			6 => __( 'Samedi', 'al-ghazzali-institut' ),
			7 => __( 'Dimanche', 'al-ghazzali-institut' ),
		);
	}

	/**
	 * Créneaux de toutes les formations publiées, par jour (1 = lundi).
	 *
	 * @return array<int, list<array<string, mixed>>>
	 */
	public static function weekly(): array {
		return self::by_day( self::collect( self::courses_with_slots() ) );
	}

	/**
	 * @return array<int, list<array<string, mixed>>>
	 */
	public static function for_course( \WP_Post $course ): array {
		return self::by_day( self::collect( array( $course ) ) );
	}

	/**
	 * Créneaux assurés par un membre de l'équipe, du lundi au dimanche.
	 *
	 * @return list<array<string, mixed>>
	 */
	public static function for_teacher( int $teacher_id ): array {
		$slots = array();
		foreach ( self::weekly() as $day_slots ) {
			foreach ( $day_slots as $slot ) {
				if ( $slot['teacher'] === $teacher_id ) {
					$slots[] = $slot;
				}
			}
		}
		return $slots;
	}

	/**
	 * Nom d'un enseignant, seulement si sa fiche est publiée (brouillon = non affiché).
	 */
	public static function teacher_name( int $teacher_id ): string {
		$teacher = $teacher_id > 0 ? get_post( $teacher_id ) : null;
		return $teacher instanceof \WP_Post && 'publish' === $teacher->post_status ? get_the_title( $teacher ) : '';
	}

	/**
	 * @return \WP_Post[]
	 */
	private static function courses_with_slots(): array {
		// get_posts amorce les caches méta : aucune requête dans les boucles qui suivent.
		return get_posts(
			array(
				'post_type'      => PostTypes::COURSE,
				'posts_per_page' => self::COURSES_LIMIT,
				'no_found_rows'  => true,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => Fields::SLOTS,
						'compare' => 'EXISTS',
					),
				),
			)
		);
	}

	/**
	 * Créneaux complets (jour, début, fin) des formations données ; les lignes incomplètes sont ignorées.
	 *
	 * @param \WP_Post[] $courses Formations.
	 * @return list<array<string, mixed>>
	 */
	private static function collect( array $courses ): array {
		$days     = self::days();
		$slots    = array();
		$teachers = array();

		foreach ( $courses as $course ) {
			foreach ( Course::slots( $course->ID ) as $row ) {
				$day = (int) $row['day'];
				if ( ! isset( $days[ $day ] ) || '' === $row['start'] || '' === $row['end'] ) {
					continue;
				}

				$teacher = (int) $row['teacher'];
				if ( $teacher > 0 ) {
					$teachers[] = $teacher;
				}

				$slots[] = array(
					'day'     => $day,
					'start'   => $row['start'],
					'end'     => $row['end'],
					'level'   => $row['level'],
					'public'  => $row['public'],
					'teacher' => $teacher,
					'mode'    => $row['mode'],
					'course'  => $course,
				);
			}
		}

		// Une seule requête pour tous les enseignants cités, au lieu d'une par créneau.
		if ( array() !== $teachers ) {
			_prime_post_caches( array_unique( $teachers ), false, false );
		}
		return $slots;
	}

	/**
	 * @param list<array<string, mixed>> $slots Créneaux.
	 * @return array<int, list<array<string, mixed>>>
	 */
	private static function by_day( array $slots ): array {
		$week = array_fill_keys( array_keys( self::days() ), array() );
		foreach ( $slots as $slot ) {
			$week[ $slot['day'] ][] = $slot;
		}

		foreach ( $week as $day => $day_slots ) {
			usort( $day_slots, static fn( array $a, array $b ): int => strcmp( $a['start'], $b['start'] ) );
			$week[ $day ] = $day_slots;
		}
		return $week;
	}
}
