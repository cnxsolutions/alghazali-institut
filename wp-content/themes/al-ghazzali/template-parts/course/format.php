<?php
/**
 * Fiche formation, zone « Format & modalités » : points clés puis déroulé d'une séance.
 *
 * @package AlGhazzali
 *
 * Arguments : features (voir Course::features()), steps (list<string>), steps_title.
 */

defined( 'ABSPATH' ) || exit;

$ag_features = is_array( $args['features'] ?? null ) ? $args['features'] : array();
$ag_steps    = is_array( $args['steps'] ?? null ) ? $args['steps'] : array();
if ( array() === $ag_features && array() === $ag_steps ) {
	return;
}
$ag_steps_title = (string) ( $args['steps_title'] ?? '' );
?>
<section class="section section--sand format-zone" aria-labelledby="format-title">
	<div class="container">
		<?php ag_section_head( __( 'Format & modalités', 'al-ghazzali' ), __( 'Comment se déroulent *les cours*', 'al-ghazzali' ), '', 'section-head--center', 'format-title' ); ?>

		<?php if ( array() !== $ag_features ) : ?>
			<ul class="feature-grid feature-grid--row" data-stagger>
				<?php foreach ( $ag_features as $ag_feature ) : ?>
					<li class="feature-card" data-reveal data-spotlight>
						<?php if ( '' !== $ag_feature['label'] ) : ?>
							<p class="feature-card__label"><?php echo esc_html( $ag_feature['label'] ); ?></p>
						<?php endif; ?>
						<h3 class="feature-card__title"><?php echo esc_html( $ag_feature['title'] ); ?></h3>
						<?php if ( '' !== $ag_feature['text'] ) : ?>
							<p><?php echo esc_html( $ag_feature['text'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( array() !== $ag_steps ) : ?>
			<div class="format-steps">
				<h3 class="format-steps__title" data-reveal><?php echo esc_html( '' !== $ag_steps_title ? $ag_steps_title : __( 'Comment se déroule une séance ?', 'al-ghazzali' ) ); ?></h3>
				<ol class="steps steps--compact" data-steps data-stagger>
					<?php foreach ( $ag_steps as $ag_index => $ag_step ) : ?>
						<li class="step" data-reveal>
							<span class="step__number" aria-hidden="true"><?php echo esc_html( (string) ( $ag_index + 1 ) ); ?></span>
							<p><?php echo esc_html( $ag_step ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		<?php endif; ?>
	</div>
</section>
