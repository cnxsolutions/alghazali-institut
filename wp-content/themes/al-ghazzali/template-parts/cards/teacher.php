<?php
/**
 * Carte membre de l'équipe : photo (ou monogramme), nom, fonction, présentation courte.
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
$ag_name    = get_the_title( $ag_post );
$ag_role    = ag_meta( Fields::ROLE, $ag_post->ID );
$ag_initial = mb_strtoupper( mb_substr( (string) preg_replace( '/^(Oustadh?a?|Ustadh?a?|Cheikh|Sheikh)\s+/iu', '', $ag_name ), 0, 1 ) );
?>
<article class="teacher-card" data-reveal data-spotlight>
	<div class="teacher-card__avatar">
		<?php if ( has_post_thumbnail( $ag_post ) ) : ?>
			<?php echo get_the_post_thumbnail( $ag_post, 'ag-portrait', array( 'loading' => 'lazy', 'alt' => $ag_name ) ); ?>
		<?php else : ?>
			<span class="teacher-card__monogram" aria-hidden="true"><?php echo esc_html( $ag_initial ); ?></span>
		<?php endif; ?>
	</div>
	<div class="teacher-card__body">
		<<?php echo esc_html( $ag_heading ); ?> class="teacher-card__name">
			<a href="<?php echo esc_url( get_permalink( $ag_post ) ); ?>"><?php echo esc_html( $ag_name ); ?></a>
		</<?php echo esc_html( $ag_heading ); ?>>
		<?php if ( '' !== $ag_role ) : ?>
			<p class="teacher-card__role"><?php echo esc_html( $ag_role ); ?></p>
		<?php endif; ?>
		<?php if ( has_excerpt( $ag_post ) ) : ?>
			<p class="teacher-card__bio"><?php echo esc_html( get_the_excerpt( $ag_post ) ); ?></p>
		<?php endif; ?>
	</div>
</article>
