<?php
/**
 * Article de blog.
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$ag_id       = (int) get_the_ID();
	$ag_category = ag_first_term( $ag_id, 'category' );

	get_template_part(
		'template-parts/sections/page-hero',
		null,
		array(
			'title'  => get_the_title(),
			'lead'   => has_excerpt() ? get_the_excerpt() : '',
			'kicker' => null !== $ag_category ? $ag_category->name : __( 'Article', 'al-ghazzali' ),
			'chips'  => array(
				array( 'calendar', (string) get_the_date() ),
				/* translators: %d: minutes de lecture. */
				array( 'clock', sprintf( __( '%d min de lecture', 'al-ghazzali' ), ag_reading_time( $ag_id ) ) ),
				array( 'pen', get_the_author() ),
			),
		)
	);
	?>
	<article class="section article" data-reading>
		<div class="container container--narrow prose">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="prose__cover"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></figure>
			<?php endif; ?>
			<?php the_content(); ?>
			<p class="article__updated">
				<?php
				/* translators: %s: date de dernière mise à jour. */
				echo esc_html( sprintf( __( 'Mis à jour le %s', 'al-ghazzali' ), get_the_modified_date() ) );
				?>
			</p>
		</div>
	</article>

	<?php
	$ag_related = get_posts(
		array(
			'posts_per_page'      => 3,
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'post__not_in'        => array( $ag_id ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- une seule exclusion.
			'cat'                 => null !== $ag_category ? $ag_category->term_id : 0,
		)
	);
	if ( array() !== $ag_related ) :
		?>
		<section class="section section--sand" aria-labelledby="related-posts-title">
			<div class="container">
				<?php ag_section_head( __( 'À lire aussi', 'al-ghazzali' ), __( 'Continuer *la lecture*', 'al-ghazzali' ), '', '', 'related-posts-title' ); ?>
				<div class="card-grid" data-stagger>
					<?php
					foreach ( $ag_related as $ag_post ) {
						get_template_part( 'template-parts/cards/post', null, array( 'post' => $ag_post ) );
					}
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
