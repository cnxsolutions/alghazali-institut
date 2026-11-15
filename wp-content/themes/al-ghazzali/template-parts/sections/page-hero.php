<?php
/**
 * Bandeau des pages intérieures : fil d'Ariane, H1 unique, chapeau, puces et actions.
 *
 * @package AlGhazzali
 *
 * Arguments : title, lead, kicker, arabic, chips (list<[icône, texte]>), enroll (bool), secondary ([libellé, url]).
 */

defined( 'ABSPATH' ) || exit;

$ag_title     = (string) ( $args['title'] ?? '' );
$ag_lead      = (string) ( $args['lead'] ?? '' );
$ag_kicker    = (string) ( $args['kicker'] ?? '' );
$ag_arabic    = (string) ( $args['arabic'] ?? '' );
$ag_chips     = is_array( $args['chips'] ?? null ) ? $args['chips'] : array();
$ag_enroll    = (bool) ( $args['enroll'] ?? false );
$ag_secondary = is_array( $args['secondary'] ?? null ) ? $args['secondary'] : null;
?>
<section class="page-hero">
	<div class="page-hero__bg" aria-hidden="true">
		<div class="pattern pattern--hero"></div>
		<div class="hero__glow"></div>
	</div>
	<?php if ( '' !== $ag_arabic ) : ?>
		<span class="page-hero__ar" lang="ar" dir="rtl" aria-hidden="true"><?php echo esc_html( $ag_arabic ); ?></span>
	<?php endif; ?>

	<div class="container page-hero__inner">
		<?php ag_breadcrumbs(); ?>
		<?php if ( '' !== $ag_kicker ) : ?>
			<p class="kicker kicker--light"><?php echo esc_html( $ag_kicker ); ?></p>
		<?php endif; ?>
		<h1 class="page-hero__title"><?php echo ag_highlight( $ag_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
		<?php if ( '' !== $ag_lead ) : ?>
			<p class="page-hero__lead"><?php echo esc_html( wp_strip_all_tags( $ag_lead ) ); ?></p>
		<?php endif; ?>

		<?php if ( array() !== $ag_chips ) : ?>
			<ul class="chips chips--light">
				<?php foreach ( $ag_chips as list( $ag_icon, $ag_text ) ) : ?>
					<li><?php echo ag_icon( $ag_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $ag_text ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $ag_enroll || null !== $ag_secondary ) : ?>
			<div class="page-hero__actions">
				<?php if ( $ag_enroll ) : ?>
					<a class="btn btn--gold" <?php echo ag_enroll_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( ag_enroll_label() ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<?php endif; ?>
				<?php if ( null !== $ag_secondary ) : ?>
					<a class="btn btn--outline-light" href="<?php echo esc_url( $ag_secondary[1] ); ?>"><?php echo esc_html( $ag_secondary[0] ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
