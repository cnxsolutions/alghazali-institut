<?php
/**
 * Fiche d'un membre de l'équipe : présentation et créneaux qu'il assure.
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\Fields;
use AlGhazzali\Core\Content\Schedule;
use AlGhazzali\Core\Content\Taxonomies;

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$ag_id    = (int) get_the_ID();
	$ag_group = ag_first_term( $ag_id, Taxonomies::TEAM_GROUP );
	$ag_slots = Schedule::for_teacher( $ag_id );
	$ag_days  = Schedule::days();

	get_template_part(
		'template-parts/sections/page-hero',
		null,
		array(
			'title'  => get_the_title(),
			'lead'   => ag_meta( Fields::ROLE ),
			'kicker' => null !== $ag_group ? $ag_group->name : __( 'Notre équipe', 'al-ghazzali' ),
		)
	);
	?>
	<section class="section">
		<div class="container layout-aside layout-aside--start">
			<aside class="profile-card" data-reveal>
				<?php
				get_template_part(
					'template-parts/cards/teacher',
					null,
					array(
						'post'    => get_post(),
						'heading' => 'h2',
					)
				);
				?>
			</aside>
			<div class="prose" data-reveal>
				<?php the_content(); ?>
			</div>
		</div>
	</section>

	<?php if ( array() !== $ag_slots ) : ?>
		<section class="section section--sand" aria-labelledby="teacher-slots-title">
			<div class="container container--narrow">
				<?php ag_section_head( __( 'Ses cours', 'al-ghazzali' ), __( 'Créneaux de la *semaine*', 'al-ghazzali' ), '', '', 'teacher-slots-title' ); ?>
				<ul class="slot-list" data-stagger>
					<?php foreach ( $ag_slots as $ag_slot ) : ?>
						<li class="slot-list__item" data-reveal>
							<span class="slot-list__day"><?php echo esc_html( $ag_days[ $ag_slot['day'] ] ); ?></span>
							<time class="slot-list__time"><?php echo esc_html( $ag_slot['start'] . ' – ' . $ag_slot['end'] ); ?></time>
							<span class="slot-list__label">
								<a href="<?php echo esc_url( get_permalink( $ag_slot['course'] ) ); ?>"><?php echo esc_html( get_the_title( $ag_slot['course'] ) ); ?></a><?php echo esc_html( implode( '', array_map( static fn( string $part ): string => ' · ' . $part, array_filter( array( $ag_slot['level'], Fields::label( Fields::publics(), $ag_slot['public'] ) ) ) ) ) ); ?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
