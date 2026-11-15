<?php
/**
 * Archives génériques (catégories, étiquettes, dates, auteurs).
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part(
	'template-parts/sections/page-hero',
	null,
	array(
		'title'  => wp_strip_all_tags( get_the_archive_title() ),
		'lead'   => wp_strip_all_tags( get_the_archive_description() ),
		'kicker' => __( 'Ressources', 'al-ghazzali' ),
	)
);
get_template_part( 'template-parts/sections/post-list' );
get_footer();
