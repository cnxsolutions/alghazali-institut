<?php
/**
 * Fiche formation, zone « Réductions » (facultative).
 *
 * @package AlGhazzali
 *
 * Arguments : discounts (voir Course::discounts()), note.
 */

defined( 'ABSPATH' ) || exit;

$ag_discounts = is_array( $args['discounts'] ?? null ) ? $args['discounts'] : array();
if ( array() === $ag_discounts ) {
	return;
}
$ag_note = (string) ( $args['note'] ?? '' );
?>
<section class="section section--tight discounts" aria-labelledby="discounts-title">
	<div class="container container--narrow">
		<?php ag_section_head( __( 'Tarifs', 'al-ghazzali' ), __( 'Les *réductions*', 'al-ghazzali' ), '', 'section-head--center', 'discounts-title' ); ?>
		<ul class="discount-grid" data-stagger>
			<?php foreach ( $ag_discounts as $ag_discount ) : ?>
				<li class="discount" data-reveal>
					<strong class="discount__value"><?php echo esc_html( $ag_discount['value'] ); ?></strong>
					<span class="discount__label"><?php echo esc_html( $ag_discount['label'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php if ( '' !== $ag_note ) : ?>
			<p class="discounts__note" data-reveal><?php echo esc_html( $ag_note ); ?></p>
		<?php endif; ?>
	</div>
</section>
