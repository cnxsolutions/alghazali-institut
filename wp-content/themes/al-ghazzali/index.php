<?php
/**
 * Gabarit de repli.
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part(
	'template-parts/sections/page-hero',
	null,
	array( 'title' => wp_strip_all_tags( wp_get_document_title() ) )
);
get_template_part( 'template-parts/sections/post-list' );
get_footer();
