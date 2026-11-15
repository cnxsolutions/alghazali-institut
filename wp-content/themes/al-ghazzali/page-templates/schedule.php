<?php
/**
 * Template Name: Emploi du temps
 *
 * Grille hebdomadaire construite à partir des créneaux saisis sur chaque formation (extension).
 * Filtres : formation et classe (hommes / femmes), présélectionnables par l'URL (?classe=femmes).
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\Schedule;

defined( 'ABSPATH' ) || exit;

$ag_week    = Schedule::weekly();
$ag_has_any = array() !== array_merge( ...array_values( $ag_week ) );

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part(
		'template-parts/sections/page-hero',
		null,
		array(
			'title'  => get_the_title(),
			'lead'   => has_excerpt() ? get_the_excerpt() : '',
			'kicker' => __( 'Planning', 'al-ghazzali' ),
		)
	);
	?>
	<section class="section">
		<div class="container">
			<?php if ( '' !== trim( get_the_content() ) ) : ?>
				<div class="prose container--narrow schedule-intro"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php if ( ! $ag_has_any ) : ?>
				<?php get_template_part( 'template-parts/sections/empty', null, array( 'text' => __( 'Les horaires de la prochaine année seront publiés très bientôt.', 'al-ghazzali' ) ) ); ?>
			<?php else : ?>
				<?php
				get_template_part(
					'template-parts/sections/week',
					null,
					array(
						'week'    => $ag_week,
						'id'      => 'schedule-grid',
						'filters' => ag_week_filters( $ag_week, array( 'formation', 'classe' ) ),
						'heading' => 'h2',
					)
				);
				?>
			<?php endif; ?>
		</div>
	</section>
	<?php
endwhile;

get_footer();
