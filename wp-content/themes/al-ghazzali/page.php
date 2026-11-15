<?php
/**
 * Page standard.
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part(
		'template-parts/sections/page-hero',
		null,
		array(
			'title' => get_the_title(),
			'lead'  => has_excerpt() ? get_the_excerpt() : '',
		)
	);
	?>
	<section class="section">
		<div class="container container--narrow prose" data-reveal>
			<?php the_content(); ?>
		</div>
	</section>
	<?php
endwhile;

get_footer();
