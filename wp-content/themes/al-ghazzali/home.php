<?php
/**
 * Page des articles (blog).
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

$ag_blog_id = (int) get_option( 'page_for_posts' );

get_header();
get_template_part(
	'template-parts/sections/page-hero',
	null,
	array(
		'title'  => $ag_blog_id > 0 ? get_the_title( $ag_blog_id ) : __( 'Blog', 'al-ghazzali' ),
		'lead'   => $ag_blog_id > 0 && has_excerpt( $ag_blog_id ) ? get_the_excerpt( $ag_blog_id ) : '',
		'kicker' => __( 'Ressources', 'al-ghazzali' ),
	)
);
get_template_part( 'template-parts/sections/post-list' );
get_footer();
