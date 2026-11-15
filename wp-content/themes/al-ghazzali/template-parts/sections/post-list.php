<?php
/**
 * Liste paginée d'articles (blog, catégories, archives) avec filtres par catégorie.
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

$ag_categories = get_categories(
	array(
		'hide_empty' => true,
		'number'     => 12,
	)
);
$ag_blog_id    = (int) get_option( 'page_for_posts' );
?>
<section class="section">
	<div class="container">
		<?php if ( count( $ag_categories ) > 1 ) : ?>
			<nav class="filter-links" aria-label="<?php esc_attr_e( 'Catégories', 'al-ghazzali' ); ?>">
				<?php if ( $ag_blog_id > 0 ) : ?>
					<a href="<?php echo esc_url( (string) get_permalink( $ag_blog_id ) ); ?>"<?php echo is_home() ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Tous', 'al-ghazzali' ); ?></a>
				<?php endif; ?>
				<?php foreach ( $ag_categories as $ag_category ) : ?>
					<a href="<?php echo esc_url( (string) get_category_link( $ag_category ) ); ?>"<?php echo is_category( $ag_category->term_id ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $ag_category->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="card-grid" data-stagger>
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part(
						'template-parts/cards/post',
						null,
						array(
							'post'    => get_post(),
							'heading' => 'h2',
						)
					);
				}
				?>
			</div>
			<?php ag_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/sections/empty', null, array( 'text' => __( 'Aucun article publié pour le moment.', 'al-ghazzali' ) ) ); ?>
		<?php endif; ?>
	</div>
</section>
