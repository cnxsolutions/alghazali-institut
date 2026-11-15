<?php
/**
 * Carte événement : bloc date, titre, horaire, format et lieu.
 *
 * @package AlGhazzali
 *
 * Arguments : post (WP_Post), heading (h2|h3, défaut h3).
 */

use AlGhazzali\Core\Content\Fields;

defined( 'ABSPATH' ) || exit;

$ag_post = $args['post'] ?? null;
if ( ! $ag_post instanceof WP_Post ) {
	return;
}

$ag_heading = ag_card_heading( $args );
$ag_date    = ag_meta( Fields::EVENT_DATE, $ag_post->ID );
$ag_stamp   = '' !== $ag_date ? (int) strtotime( $ag_date ) : 0;
$ag_start   = ag_meta( Fields::EVENT_START, $ag_post->ID );
$ag_end     = ag_meta( Fields::EVENT_END, $ag_post->ID );
$ag_mode    = Fields::label( Fields::event_modes(), ag_meta( Fields::EVENT_MODE, $ag_post->ID ) );
$ag_place   = ag_meta( Fields::EVENT_PLACE, $ag_post->ID );
?>
<article class="event-card" data-reveal>
	<?php if ( $ag_stamp > 0 ) : ?>
		<time class="event-card__date" datetime="<?php echo esc_attr( $ag_date ); ?>">
			<span class="event-card__day"><?php echo esc_html( date_i18n( 'j', $ag_stamp ) ); ?></span>
			<span class="event-card__month"><?php echo esc_html( date_i18n( 'M Y', $ag_stamp ) ); ?></span>
		</time>
	<?php endif; ?>
	<div class="event-card__body">
		<<?php echo esc_html( $ag_heading ); ?> class="event-card__title">
			<a href="<?php echo esc_url( get_permalink( $ag_post ) ); ?>"><?php echo esc_html( get_the_title( $ag_post ) ); ?></a>
		</<?php echo esc_html( $ag_heading ); ?>>
		<ul class="chips">
			<?php if ( '' !== $ag_start ) : ?>
				<li><?php echo ag_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( '' !== $ag_end ? $ag_start . ' – ' . $ag_end : $ag_start ); ?></li>
			<?php endif; ?>
			<?php if ( '' !== $ag_mode ) : ?>
				<li><?php echo ag_icon( 'video' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $ag_mode ); ?></li>
			<?php endif; ?>
			<?php if ( '' !== $ag_place ) : ?>
				<li><?php echo ag_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $ag_place ); ?></li>
			<?php endif; ?>
		</ul>
	</div>
	<span class="event-card__arrow" aria-hidden="true"><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
</article>
