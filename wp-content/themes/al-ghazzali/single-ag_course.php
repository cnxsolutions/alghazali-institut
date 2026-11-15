<?php
/**
 * Fiche formation, zone par zone (chaque zone vient de l'extension et disparaît si elle est vide) :
 * en-tête, l'essentiel, niveaux, format & modalités, emploi du temps, contenu libre, réductions, FAQ.
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\Course;
use AlGhazzali\Core\Content\Fields;
use AlGhazzali\Core\Content\Schedule;

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$ag_id       = (int) get_the_ID();
	$ag_levels   = Course::levels( $ag_id );
	$ag_week     = Schedule::for_course( get_post() );
	$ag_has_week = array() !== array_merge( ...array_values( $ag_week ) );
	$ag_facts    = ag_course_facts( $ag_id );
	$ag_schedule = ag_page_by_template( 'page-templates/schedule.php' );
	$ag_kicker   = ag_meta( Fields::KICKER );

	$ag_secondary = null;
	if ( $ag_has_week ) {
		$ag_secondary = array( __( 'Voir les horaires', 'al-ghazzali' ), '#horaires' );
	} elseif ( array() !== $ag_levels ) {
		$ag_secondary = array( __( 'Voir les niveaux', 'al-ghazzali' ), '#niveaux' );
	}

	get_template_part(
		'template-parts/sections/page-hero',
		null,
		array(
			'title'     => get_the_title(),
			'lead'      => has_excerpt() ? get_the_excerpt() : '',
			'kicker'    => '' !== $ag_kicker ? $ag_kicker : __( 'Formation', 'al-ghazzali' ),
			'arabic'    => ag_meta( Fields::ARABIC ),
			'enroll'    => true,
			'secondary' => $ag_secondary,
		)
	);
	?>

	<?php if ( array() !== $ag_facts ) : ?>
		<section class="facts-bar" aria-labelledby="facts-title">
			<div class="container">
				<h2 class="screen-reader-text" id="facts-title"><?php esc_html_e( 'L’essentiel', 'al-ghazzali' ); ?></h2>
				<dl class="facts facts--bar" data-stagger>
					<?php foreach ( $ag_facts as $ag_fact ) : ?>
						<div class="facts__row" data-reveal>
							<dt><?php echo ag_icon( $ag_fact['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $ag_fact['label'] ); ?></dt>
							<dd><?php echo esc_html( $ag_fact['value'] ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</div>
		</section>
	<?php endif; ?>

	<?php
	get_template_part(
		'template-parts/course/levels',
		null,
		array(
			'levels' => $ag_levels,
			'intro'  => ag_meta( Fields::LEVELS_INTRO ),
		)
	);

	get_template_part(
		'template-parts/course/format',
		null,
		array(
			'features'    => Course::features( $ag_id ),
			'steps'       => Course::steps( $ag_id ),
			'steps_title' => ag_meta( Fields::STEPS_TITLE ),
		)
	);
	?>

	<?php if ( $ag_has_week ) : ?>
		<section class="section" id="horaires" aria-labelledby="slots-title">
			<div class="container">
				<?php ag_section_head( __( 'Emploi du temps', 'al-ghazzali' ), __( 'Les créneaux de *la semaine*', 'al-ghazzali' ), __( 'Horaires à l’heure de Paris. Filtrez par classe et par niveau pour trouver votre groupe.', 'al-ghazzali' ), 'section-head--center', 'slots-title' ); ?>
				<?php
				get_template_part(
					'template-parts/sections/week',
					null,
					array(
						'week'        => $ag_week,
						'id'          => 'course-week',
						'filters'     => ag_week_filters( $ag_week, array( 'classe', 'niveau' ) ),
						'show_course' => false,
					)
				);
				?>
				<?php if ( $ag_schedule > 0 ) : ?>
					<p class="section-foot"><a class="link-arrow" href="<?php echo esc_url( (string) get_permalink( $ag_schedule ) ); ?>"><?php esc_html_e( 'Emploi du temps de toutes les formations', 'al-ghazzali' ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></p>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( '' !== trim( get_the_content() ) ) : ?>
		<section class="section section--sand" aria-label="<?php esc_attr_e( 'En savoir plus', 'al-ghazzali' ); ?>">
			<div class="container container--narrow prose" data-reveal>
				<?php the_content(); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	get_template_part(
		'template-parts/course/discounts',
		null,
		array(
			'discounts' => Course::discounts( $ag_id ),
			'note'      => ag_meta( Fields::DISCOUNTS_NOTE ),
		)
	);

	get_template_part( 'template-parts/course/faq', null, array( 'faq' => Course::faq( $ag_id ) ) );

	$ag_related = ag_courses(
		array(
			'exclude' => $ag_id,
			'limit'   => 3,
		)
	);
	if ( array() !== $ag_related ) :
		?>
		<section class="section section--sand" aria-labelledby="related-title">
			<div class="container">
				<?php ag_section_head( __( 'Aller plus loin', 'al-ghazzali' ), __( 'Découvrir nos *autres formations*', 'al-ghazzali' ), '', '', 'related-title' ); ?>
				<div class="card-grid" data-stagger>
					<?php
					foreach ( $ag_related as $ag_course ) {
						get_template_part( 'template-parts/cards/course', null, array( 'post' => $ag_course ) );
					}
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
