<?php
/**
 * Fiche formation, zone « Questions fréquentes » (accordéon natif <details>).
 *
 * @package AlGhazzali
 *
 * Arguments : faq (voir Course::faq()).
 */

defined( 'ABSPATH' ) || exit;

$ag_faq = is_array( $args['faq'] ?? null ) ? $args['faq'] : array();
if ( array() === $ag_faq ) {
	return;
}
?>
<section class="section faq-zone" aria-labelledby="faq-title">
	<div class="container container--narrow">
		<?php ag_section_head( __( 'Questions fréquentes', 'al-ghazzali' ), __( 'Vos *questions*, nos réponses', 'al-ghazzali' ), '', 'section-head--center', 'faq-title' ); ?>
		<div class="faq" data-stagger>
			<?php foreach ( $ag_faq as $ag_entry ) : ?>
				<details class="faq__item" data-reveal>
					<summary><?php echo esc_html( $ag_entry['question'] ); ?></summary>
					<?php echo wp_kses_post( wpautop( esc_html( $ag_entry['answer'] ) ) ); ?>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
