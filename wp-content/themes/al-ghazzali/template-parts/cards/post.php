<?php
/**
 * Carte article de blog.
 *
 * @package AlGhazzali
 *
 * Arguments : post (WP_Post), heading (h2|h3, défaut h3).
 */

defined( 'ABSPATH' ) || exit;

$ag_post = $args['post'] ?? null;
if ( ! $ag_post instanceof WP_Post ) {
	return;
}

$ag_heading  = ag_card_heading( $args );
$ag_category = ag_first_term( $ag_post->ID, 'category' );
?>
<article class="post-card" data-reveal data-spotlight>
	<div class="post-card__media">
		<?php if ( has_post_thumbnail( $ag_post ) ) : ?>
			<?php echo get_the_post_thumbnail( $ag_post, 'ag-card', array( 'loading' => 'lazy', 'sizes' => '(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw' ) ); ?>
		<?php else : ?>
			<span class="post-card__art" aria-hidden="true"><span class="pattern pattern--card"></span><?php echo ag_icon( 'book', 'post-card__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<?php endif; ?>
	</div>
	<div class="post-card__body">
		<?php if ( null !== $ag_category ) : ?>
			<p class="post-card__category"><?php echo esc_html( $ag_category->name ); ?></p>
		<?php endif; ?>
		<<?php echo esc_html( $ag_heading ); ?> class="post-card__title">
			<a href="<?php echo esc_url( get_permalink( $ag_post ) ); ?>"><?php echo esc_html( get_the_title( $ag_post ) ); ?></a>
		</<?php echo esc_html( $ag_heading ); ?>>
		<p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $ag_post ), 24 ) ); ?></p>
		<p class="post-card__meta">
			<time datetime="<?php echo esc_attr( (string) get_the_date( 'c', $ag_post ) ); ?>"><?php echo esc_html( (string) get_the_date( '', $ag_post ) ); ?></time>
			<?php
			/* translators: %d: minutes de lecture. */
			echo esc_html( sprintf( __( '%d min de lecture', 'al-ghazzali' ), ag_reading_time( $ag_post->ID ) ) );
			?>
		</p>
	</div>
</article>
