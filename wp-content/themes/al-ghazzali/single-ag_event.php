<?php
/**
 * Fiche événement.
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\Fields;

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$ag_date  = ag_meta( Fields::EVENT_DATE );
	$ag_start = ag_meta( Fields::EVENT_START );
	$ag_end   = ag_meta( Fields::EVENT_END );
	$ag_mode  = Fields::label( Fields::event_modes(), ag_meta( Fields::EVENT_MODE ) );
	$ag_place = ag_meta( Fields::EVENT_PLACE );
	$ag_link  = ag_meta( Fields::EVENT_URL );

	$ag_chips = array();
	if ( '' !== $ag_date ) {
		$ag_chips[] = array( 'calendar', date_i18n( 'l j F Y', (int) strtotime( $ag_date ) ) );
	}
	if ( '' !== $ag_start ) {
		$ag_chips[] = array( 'clock', '' !== $ag_end ? $ag_start . ' – ' . $ag_end : $ag_start );
	}
	if ( '' !== $ag_mode ) {
		$ag_chips[] = array( 'video', $ag_mode );
	}
	if ( '' !== $ag_place ) {
		$ag_chips[] = array( 'pin', $ag_place );
	}

	get_template_part(
		'template-parts/sections/page-hero',
		null,
		array(
			'title'     => get_the_title(),
			'lead'      => has_excerpt() ? get_the_excerpt() : '',
			'kicker'    => __( 'Événement', 'al-ghazzali' ),
			'chips'     => $ag_chips,
			'secondary' => '' !== $ag_link ? array( __( 'S’inscrire à l’événement', 'al-ghazzali' ), $ag_link ) : null,
		)
	);
	?>
	<section class="section">
		<div class="container container--narrow prose" data-reveal>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="prose__cover"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></figure>
			<?php endif; ?>
			<?php the_content(); ?>
		</div>
	</section>
	<?php
endwhile;

get_footer();
