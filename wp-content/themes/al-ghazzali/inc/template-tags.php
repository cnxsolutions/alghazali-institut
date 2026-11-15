<?php
/**
 * Fonctions d'affichage appelées par les gabarits. Aucune règle métier ici : elles délèguent aux modules.
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\Course;
use AlGhazzali\Core\Content\Fields;
use AlGhazzali\Core\Content\PostTypes;
use AlGhazzali\Seo\Breadcrumbs;
use AlGhazzali\Settings\Institute;

defined( 'ABSPATH' ) || exit;

function ag_institute(): Institute {
	static $instance = null;
	return $instance ??= new Institute();
}

function ag_meta( string $key, int $post_id = 0 ): string {
	return (string) get_post_meta( $post_id > 0 ? $post_id : (int) get_the_ID(), $key, true );
}

/**
 * Icône SVG inline (24 × 24, trait), décorative par défaut.
 */
function ag_icon( string $name, string $class = '' ): string {
	static $paths = array(
		'arrow-right'  => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'arrow-up'     => '<path d="M12 19V5M6 11l6-6 6 6"/>',
		'book'         => '<path d="M2 5.5C4.5 4 8 4 12 6c4-2 7.5-2 10-.5V19c-2.5-1.5-6-1.5-10 .5-4-2-7.5-2-10-.5Z"/><path d="M12 6v13.5"/>',
		'star'         => '<path d="M12 2.5l2.8 2.7h3.9v3.9l2.8 2.9-2.8 2.8v3.9h-3.9L12 21.5l-2.8-2.8H5.3v-3.9L2.5 12l2.8-2.9V5.2h3.9Z"/><circle cx="12" cy="12" r="3.2"/>',
		'users'        => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.8-3.6 3.4-5.5 6.5-5.5s5.7 1.9 6.5 5.5"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14.8c2 .7 3.2 2.4 3.6 5.2"/>',
		'user'         => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1-4.2 4.1-6.5 8-6.5s7 2.3 8 6.5"/>',
		'video'        => '<rect x="2.5" y="6" width="13" height="12" rx="2.5"/><path d="m15.5 10.5 6-3.5v10l-6-3.5"/>',
		'calendar'     => '<rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/>',
		'clock'        => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
		'pin'          => '<path d="M12 21.5s-7-6.1-7-11.5a7 7 0 0 1 14 0c0 5.4-7 11.5-7 11.5Z"/><circle cx="12" cy="10" r="2.5"/>',
		'phone'        => '<path d="M5 3.5h3.5l1.8 4.6-2.3 1.4a11 11 0 0 0 6.5 6.5l1.4-2.3 4.6 1.8V19a2 2 0 0 1-2 2A16.5 16.5 0 0 1 3 5.5a2 2 0 0 1 2-2Z"/>',
		'mail'         => '<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="m3.5 6.5 8.5 6.5 8.5-6.5"/>',
		'check'        => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
		'globe'        => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3Z"/>',
		'layers'       => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 13 9 5 9-5"/>',
		'cap'          => '<path d="m12 4 10 5-10 5L2 9l10-5Z"/><path d="M6 11v5c2 2 10 2 12 0v-5M22 9v6"/>',
		'sparkle'      => '<path d="M12 3c.6 4.2 2.8 6.4 7 7-4.2.6-6.4 2.8-7 7-.6-4.2-2.8-6.4-7-7 4.2-.6 6.4-2.8 7-7Z"/>',
		'heart'        => '<path d="M12 20s-7.5-4.6-7.5-10A4.3 4.3 0 0 1 12 7.3 4.3 4.3 0 0 1 19.5 10c0 5.4-7.5 10-7.5 10Z"/>',
		'pen'          => '<path d="M4 20h4L19.5 8.5a2.8 2.8 0 0 0-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4"/>',
		'chat'         => '<path d="M4 5.5h16v11H9l-5 4v-15Z"/>',
		'level'        => '<path d="M5 20v-5M12 20V9M19 20V4"/>',
		'search'       => '<circle cx="11" cy="11" r="7"/><path d="m20.5 20.5-4.5-4.5"/>',
		'whatsapp'     => '<path d="M12 3a9 9 0 0 0-7.8 13.5L3 21l4.6-1.2A9 9 0 1 0 12 3Z"/><path d="M9 8.5c0 3.5 3 6.5 6.5 6.5l1-1.6-2-1-1 .9a5 5 0 0 1-2.3-2.3l.9-1-1-2Z"/>',
		'instagram'    => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".6"/>',
		'facebook'     => '<path d="M14 8.5h2.5V5H14a3.5 3.5 0 0 0-3.5 3.5V11H8v3.5h2.5V21H14v-6.5h2.5L17 11h-3V9a.5.5 0 0 1 .5-.5Z"/>',
		'youtube'      => '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="m10 9.5 5 2.5-5 2.5v-5Z"/>',
		'tiktok'       => '<path d="M14 3v11.5a3.5 3.5 0 1 1-3.5-3.5"/><path d="M14 3c.4 2.6 2.2 4.4 5 4.7"/>',
		'telegram'     => '<path d="m21 4-18 7.3 6.5 2.2L19 7l-7.5 8v5l3.6-3.6L19 19l2-15Z"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="icon %1$s" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>',
		esc_attr( $class ),
		$paths[ $name ]
	);
}

/**
 * Texte arabe correctement balisé (langue + sens de lecture).
 */
function ag_arabic( string $text, string $class = '' ): string {
	return sprintf( '<span class="ar %1$s" lang="ar" dir="rtl">%2$s</span>', esc_attr( $class ), esc_html( $text ) );
}

/**
 * Échappe un titre puis transforme *mot* en <em>mot</em> (mise en valeur typographique).
 */
function ag_highlight( string $text ): string {
	return (string) preg_replace( '/\*(.+?)\*/u', '<em>$1</em>', esc_html( $text ) );
}

/**
 * Attributs du lien « S'inscrire » : la plateforme (nouvel onglet) ou la page de contact.
 */
function ag_enroll_attrs(): string {
	$institute = ag_institute();
	$attrs     = sprintf( 'href="%s"', esc_url( $institute->enroll_url() ) );
	return $institute->enroll_is_external() ? $attrs . ' target="_blank" rel="noopener"' : $attrs;
}

function ag_enroll_label(): string {
	return ag_institute()->enroll_is_external() ? __( 'S’inscrire', 'al-ghazzali' ) : __( 'Nous contacter', 'al-ghazzali' );
}

/**
 * Fiche synthétique d'une formation : icône, libellé, valeur (vides exclues).
 *
 * @return list<array{icon: string, label: string, value: string}>
 */
function ag_course_facts( int $post_id ): array {
	$levels = count( Course::levels( $post_id ) );
	$facts  = array(
		array( 'video', __( 'Format', 'al-ghazzali' ), Fields::label( Fields::formats(), ag_meta( Fields::FORMAT, $post_id ) ) ),
		array( 'level', __( 'Parcours', 'al-ghazzali' ), $levels > 0 ? ag_levels_label( $levels ) : '' ),
		array( 'users', __( 'Classes', 'al-ghazzali' ), ag_publics_label( Course::publics( $post_id ) ) ),
		array( 'clock', __( 'Rythme', 'al-ghazzali' ), ag_meta( Fields::RHYTHM, $post_id ) ),
		array( 'calendar', __( 'Durée', 'al-ghazzali' ), ag_meta( Fields::DURATION, $post_id ) ),
		array( 'sparkle', __( 'Tarif', 'al-ghazzali' ), ag_meta( Fields::PRICE, $post_id ) ),
	);

	$start = ag_meta( Fields::START, $post_id );
	if ( '' !== $start ) {
		$facts[] = array( 'calendar', __( 'Rentrée', 'al-ghazzali' ), date_i18n( 'j F Y', (int) strtotime( $start ) ) );
	}

	$output = array();
	foreach ( $facts as list( $icon, $label, $value ) ) {
		if ( '' !== $value ) {
			$output[] = array(
				'icon'  => $icon,
				'label' => $label,
				'value' => $value,
			);
		}
	}
	return $output;
}

/**
 * « 1 niveau », « 5 niveaux ».
 */
function ag_levels_label( int $count ): string {
	/* translators: %d: nombre de niveaux. */
	return sprintf( _n( '%d niveau', '%d niveaux', $count, 'al-ghazzali' ), $count );
}

/**
 * « Hommes · Femmes » à partir des classes ouvertes.
 *
 * @param array<string, string> $publics Classes (slug => libellé).
 */
function ag_publics_label( array $publics ): string {
	return implode( ' · ', $publics );
}

/**
 * Chiffres arabes orientaux (١, ٢, ٣…) pour la frise des niveaux.
 */
function ag_arabic_numeral( int $number ): string {
	return strtr( (string) $number, array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' ) );
}

/**
 * Étiquettes de filtre d'un créneau (formation, classe, niveau), lues par app.js.
 *
 * @param array<string, mixed> $slot Créneau (voir Schedule).
 */
function ag_slot_tags( array $slot ): string {
	return trim( 'formation-' . $slot['course']->post_name . ' ' . $slot['public'] . ( '' !== $slot['level'] ? ' niveau-' . sanitize_title( $slot['level'] ) : '' ) );
}

/**
 * Filtres de la grille hebdomadaire, limités aux valeurs présentes (un groupe n'apparaît qu'à partir de 2 choix).
 *
 * @param array<int, list<array<string, mixed>>> $week   Créneaux par jour.
 * @param string[]                                $groups formation|classe|niveau, dans l'ordre d'affichage.
 * @return list<array{label: string, param: string, all: string, options: array<string, string>}>
 */
function ag_week_filters( array $week, array $groups ): array {
	$slots   = array_merge( ...array_values( $week ) );
	$options = array(
		'formation' => array(),
		'classe'    => array(),
		'niveau'    => array(),
	);
	foreach ( $slots as $slot ) {
		$options['formation'][ 'formation-' . $slot['course']->post_name ] = get_the_title( $slot['course'] );
		if ( '' !== $slot['level'] ) {
			$options['niveau'][ 'niveau-' . sanitize_title( $slot['level'] ) ] = $slot['level'];
		}
	}
	// Ordre fixe des classes (hommes, puis femmes), quelle que soit la saisie.
	$options['classe'] = array_intersect_key( Fields::publics(), array_flip( array_column( $slots, 'public' ) ) );
	natcasesort( $options['niveau'] );

	$labels  = array(
		'formation' => array( __( 'Formation', 'al-ghazzali' ), __( 'Toutes', 'al-ghazzali' ) ),
		'classe'    => array( __( 'Classe', 'al-ghazzali' ), __( 'Toutes', 'al-ghazzali' ) ),
		'niveau'    => array( __( 'Niveau', 'al-ghazzali' ), __( 'Tous', 'al-ghazzali' ) ),
	);
	$filters = array();
	foreach ( $groups as $group ) {
		if ( count( $options[ $group ] ?? array() ) > 1 ) {
			$filters[] = array(
				'label'   => $labels[ $group ][0],
				'param'   => $group,
				'all'     => $labels[ $group ][1],
				'options' => $options[ $group ],
			);
		}
	}
	return $filters;
}

/**
 * Formations publiées, dans l'ordre choisi dans l'admin.
 *
 * @param array{exclude?: int, limit?: int} $filters Filtres.
 * @return WP_Post[]
 */
function ag_courses( array $filters = array() ): array {
	return Course::published( $filters['limit'] ?? 12, $filters['exclude'] ?? 0 );
}

function ag_first_term( int $post_id, string $taxonomy ): ?WP_Term {
	$terms = get_the_terms( $post_id, $taxonomy );
	return is_array( $terms ) && array() !== $terms ? $terms[0] : null;
}

/**
 * Niveau de titre d'une carte : h2 sous un H1 de page, h3 sous une section (défaut).
 *
 * @param array<string, mixed> $args Arguments du gabarit de carte.
 */
function ag_card_heading( array $args ): string {
	return 'h2' === ( $args['heading'] ?? '' ) ? 'h2' : 'h3';
}

function ag_reading_time( int $post_id ): int {
	// str_word_count ignore les lettres accentuées : on compte les segments séparés par des espaces.
	$words = count( preg_split( '/\s+/u', trim( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) ), -1, PREG_SPLIT_NO_EMPTY ) ?: array() );
	return max( 1, (int) ceil( $words / 200 ) );
}

function ag_breadcrumbs(): void {
	$items = ( new Breadcrumbs() )->items();
	if ( count( $items ) < 2 ) {
		return;
	}

	$last = count( $items ) - 1;
	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Fil d’Ariane', 'al-ghazzali' ) . '"><ol>';
	foreach ( $items as $index => $item ) {
		if ( $index === $last || '' === $item['url'] ) {
			echo '<li aria-current="page">' . esc_html( $item['name'] ) . '</li>';
			continue;
		}
		echo '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['name'] ) . '</a></li>';
	}
	echo '</ol></nav>';
}

function ag_pagination(): void {
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => __( 'Précédent', 'al-ghazzali' ),
			'next_text'          => __( 'Suivant', 'al-ghazzali' ),
			'screen_reader_text' => __( 'Pagination', 'al-ghazzali' ),
		)
	);
}

/**
 * En-tête de section : surtitre, titre (avec *mise en valeur*) et chapeau.
 */
function ag_section_head( string $kicker, string $title, string $intro = '', string $modifier = '', string $id = '' ): void {
	printf( '<header class="section-head %s" data-reveal>', esc_attr( $modifier ) );
	if ( '' !== $kicker ) {
		echo '<p class="kicker">' . esc_html( $kicker ) . '</p>';
	}
	echo '<h2 class="section-title"' . ( '' !== $id ? ' id="' . esc_attr( $id ) . '"' : '' ) . '>' . ag_highlight( $title ) . '</h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé dans ag_highlight().
	if ( '' !== $intro ) {
		echo '<p class="section-intro">' . esc_html( $intro ) . '</p>';
	}
	echo '</header>';
}

/**
 * Menu principal par défaut tant qu'aucun menu n'est assigné.
 */
function ag_primary_menu_fallback(): void {
	$links = array(
		array( (string) get_post_type_archive_link( PostTypes::COURSE ), __( 'Formations', 'al-ghazzali' ) ),
		array( (string) get_post_type_archive_link( PostTypes::TEACHER ), __( 'Équipe', 'al-ghazzali' ) ),
		array( (string) get_post_type_archive_link( PostTypes::EVENT ), __( 'Événements', 'al-ghazzali' ) ),
		array( ag_institute()->contact_url(), __( 'Contact', 'al-ghazzali' ) ),
	);

	echo '<ul class="site-nav__menu">';
	foreach ( $links as list( $url, $label ) ) {
		printf( '<li class="menu-item"><a href="%1$s">%2$s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * Pages au modèle « Page SEO », dans l'ordre du menu (une requête par chargement).
 *
 * @return WP_Post[]
 */
function ag_landing_pages(): array {
	static $pages = null;
	$pages      ??= get_posts(
		array(
			'post_type'      => 'page',
			'posts_per_page' => 20,
			'no_found_rows'  => true,
			'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => 'page-templates/landing.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);
	return $pages;
}

/**
 * Première page publiée utilisant un modèle donné (ex. emploi du temps), ou 0.
 */
function ag_page_by_template( string $template ): int {
	static $cache = array();
	if ( ! isset( $cache[ $template ] ) ) {
		$ids                = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				'fields'         => 'ids',
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $template, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		$cache[ $template ] = (int) ( $ids[0] ?? 0 );
	}
	return $cache[ $template ];
}
