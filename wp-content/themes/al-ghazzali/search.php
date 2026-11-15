<?php
/**
 * Résultats de recherche (noindex).
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part(
	'template-parts/sections/page-hero',
	null,
	array(
		/* translators: %s: termes recherchés. */
		'title'  => sprintf( __( 'Résultats pour « %s »', 'al-ghazzali' ), get_search_query() ),
		'kicker' => __( 'Recherche', 'al-ghazzali' ),
	)
);
?>
<section class="section">
	<div class="container container--narrow">
		<?php get_search_form(); ?>

		<?php if ( have_posts() ) : ?>
			<ol class="results">
				<?php
				while ( have_posts() ) :
					the_post();
					$ag_type = get_post_type_object( (string) get_post_type() );
					?>
					<li class="result" data-reveal>
						<p class="result__type"><?php echo esc_html( null !== $ag_type ? $ag_type->labels->singular_name : '' ); ?></p>
						<h2 class="result__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
					</li>
				<?php endwhile; ?>
			</ol>
			<?php ag_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/sections/empty', null, array( 'text' => __( 'Aucun résultat. Essayez avec un autre mot, par exemple « arabe », « niveau » ou « alphabet ».', 'al-ghazzali' ) ) ); ?>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
