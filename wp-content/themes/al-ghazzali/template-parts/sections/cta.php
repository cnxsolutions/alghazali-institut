<?php
/**
 * Appel à l'action de fin de page.
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

$ag_institute = ag_institute();
$ag_whatsapp  = $ag_institute->whatsapp_url();
?>
<section class="cta-band" aria-labelledby="cta-title">
	<div class="container">
		<div class="cta-band__inner" data-reveal>
			<div class="pattern pattern--soft" aria-hidden="true"></div>
			<div class="cta-band__text">
				<p class="kicker kicker--light"><?php esc_html_e( 'Prêt à commencer ?', 'al-ghazzali' ); ?></p>
				<h2 class="cta-band__title" id="cta-title"><?php echo ag_highlight( __( 'Commencez votre apprentissage *dès aujourd’hui*', 'al-ghazzali' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
				<p><?php esc_html_e( 'Une question sur nos formations, votre niveau ou l’organisation des cours ? Notre équipe vous répond et vous oriente vers le cursus adapté.', 'al-ghazzali' ); ?></p>
			</div>
			<div class="cta-band__actions">
				<a class="btn btn--gold btn--lg" <?php echo ag_enroll_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( ag_enroll_label() ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<?php if ( '' !== $ag_whatsapp ) : ?>
					<a class="btn btn--outline-light btn--lg" href="<?php echo esc_url( $ag_whatsapp ); ?>" target="_blank" rel="noopener"><?php echo ag_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'WhatsApp', 'al-ghazzali' ); ?></a>
				<?php elseif ( $ag_institute->enroll_is_external() ) : ?>
					<a class="btn btn--outline-light btn--lg" href="<?php echo esc_url( $ag_institute->contact_url() ); ?>"><?php esc_html_e( 'Nous contacter', 'al-ghazzali' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
