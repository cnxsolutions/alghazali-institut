<?php
/**
 * Archive des formations.
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\PostTypes;
use AlGhazzali\Seo\Archives;

defined( 'ABSPATH' ) || exit;

$ag_seo = Archives::seo( PostTypes::COURSE );

get_header();
get_template_part(
	'template-parts/sections/course-archive',
	null,
	array(
		'title' => $ag_seo['heading'] ?? '',
		'lead'  => $ag_seo['lead'] ?? '',
	)
);
get_footer();
