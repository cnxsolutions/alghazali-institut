<?php
/**
 * Page d'accueil : sections de présentation, puis contenu éditable (texte SEO + FAQ).
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\Schedule;

defined( 'ABSPATH' ) || exit;

// Lu une fois, partagé par les chiffres clés et la section des classes.
$ag_week = Schedule::weekly();

get_header();

get_template_part( 'template-parts/home/hero' );
get_template_part( 'template-parts/home/intro', null, array( 'week' => $ag_week ) );
get_template_part( 'template-parts/home/courses' );
get_template_part( 'template-parts/home/classes', null, array( 'week' => $ag_week ) );
get_template_part( 'template-parts/home/method' );
get_template_part( 'template-parts/home/online' );
get_template_part( 'template-parts/home/teachers' );
get_template_part( 'template-parts/home/events' );
get_template_part( 'template-parts/home/articles' );

while ( have_posts() ) :
	the_post();
	if ( '' !== trim( get_the_content() ) ) :
		?>
		<section class="section section--sand home-content" aria-label="<?php esc_attr_e( 'En savoir plus', 'al-ghazzali' ); ?>">
			<div class="container container--narrow prose" data-reveal>
				<?php the_content(); ?>
			</div>
		</section>
		<?php
	endif;
endwhile;

get_footer();
