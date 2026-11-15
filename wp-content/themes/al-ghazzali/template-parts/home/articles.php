<?php
/**
 * Accueil : derniers articles du blog (contenu frais, maillage interne).
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

$ag_posts = get_posts(
	array(
		'posts_per_page'      => 3,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	)
);
if ( array() === $ag_posts ) {
	return;
}

$ag_blog_id = (int) get_option( 'page_for_posts' );
?>
<section class="section articles-preview" aria-labelledby="articles-title">
	<div class="container">
		<div class="section-row">
			<?php ag_section_head( __( 'Ressources', 'al-ghazzali' ), __( 'Conseils pour *bien apprendre*', 'al-ghazzali' ), '', '', 'articles-title' ); ?>
			<?php if ( $ag_blog_id > 0 ) : ?>
				<a class="link-arrow" href="<?php echo esc_url( (string) get_permalink( $ag_blog_id ) ); ?>" data-reveal><?php esc_html_e( 'Tous les articles', 'al-ghazzali' ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			<?php endif; ?>
		</div>
		<div class="card-grid" data-stagger>
			<?php
			foreach ( $ag_posts as $ag_post ) {
				get_template_part( 'template-parts/cards/post', null, array( 'post' => $ag_post ) );
			}
			?>
		</div>
	</div>
</section>
