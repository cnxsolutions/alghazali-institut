<?php
/**
 * Carte formation : visuel, titre, chapeau, format, nombre de niveaux et classes ouvertes.
 *
 * @package AlGhazzali
 *
 * Arguments : post (WP_Post), heading (h2|h3, défaut h3).
 */

use AlGhazzali\Core\Content\Course;
use AlGhazzali\Core\Content\Fields;

defined( 'ABSPATH' ) || exit;

$ag_post = $args['post'] ?? null;
if ( ! $ag_post instanceof WP_Post ) {
	return;
}

$ag_heading = ag_card_heading( $args );
$ag_format  = Fields::label( Fields::formats(), ag_meta( Fields::FORMAT, $ag_post->ID ) );
$ag_arabic  = ag_meta( Fields::ARABIC, $ag_post->ID );
$ag_levels  = count( Course::levels( $ag_post->ID ) );
$ag_publics = ag_publics_label( Course::publics( $ag_post->ID ) );
$ag_price   = ag_meta( Fields::PRICE, $ag_post->ID );
?>
<article class="course-card" data-reveal data-spotlight>
	<div class="course-card__media">
		<?php if ( has_post_thumbnail( $ag_post ) ) : ?>
			<?php echo get_the_post_thumbnail( $ag_post, 'ag-card', array( 'loading' => 'lazy', 'sizes' => '(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw' ) ); ?>
		<?php else : ?>
			<span class="course-card__art" aria-hidden="true">
				<span class="pattern pattern--card"></span>
				<?php if ( '' !== $ag_arabic ) : ?>
					<span class="course-card__ar" lang="ar" dir="rtl"><?php echo esc_html( $ag_arabic ); ?></span>
				<?php endif; ?>
			</span>
		<?php endif; ?>
		<?php if ( '' !== $ag_publics ) : ?>
			<span class="badge badge--float"><?php echo esc_html( $ag_publics ); ?></span>
		<?php endif; ?>
	</div>

	<div class="course-card__body">
		<<?php echo esc_html( $ag_heading ); ?> class="course-card__title">
			<a href="<?php echo esc_url( get_permalink( $ag_post ) ); ?>"><?php echo esc_html( get_the_title( $ag_post ) ); ?></a>
		</<?php echo esc_html( $ag_heading ); ?>>
		<?php if ( has_excerpt( $ag_post ) ) : ?>
			<p class="course-card__excerpt"><?php echo esc_html( get_the_excerpt( $ag_post ) ); ?></p>
		<?php endif; ?>
		<ul class="chips">
			<?php if ( '' !== $ag_format ) : ?>
				<li><?php echo ag_icon( 'video' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $ag_format ); ?></li>
			<?php endif; ?>
			<?php if ( $ag_levels > 0 ) : ?>
				<li><?php echo ag_icon( 'level' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( ag_levels_label( $ag_levels ) ); ?></li>
			<?php endif; ?>
			<?php if ( '' !== $ag_price ) : ?>
				<li><?php echo ag_icon( 'sparkle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $ag_price ); ?></li>
			<?php endif; ?>
		</ul>
		<span class="link-arrow" aria-hidden="true"><?php esc_html_e( 'Découvrir', 'al-ghazzali' ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	</div>
</article>
