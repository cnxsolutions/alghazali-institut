<?php
/**
 * Liste des formations : bandeau, grille de cartes, pagination.
 *
 * @package AlGhazzali
 *
 * Arguments : title, lead.
 */

defined( 'ABSPATH' ) || exit;

get_template_part(
	'template-parts/sections/page-hero',
	null,
	array(
		'title'  => (string) ( $args['title'] ?? '' ),
		'lead'   => (string) ( $args['lead'] ?? '' ),
		'arabic' => 'العربية',
		'kicker' => __( 'Langue arabe', 'al-ghazzali' ),
	)
);
?>
<section class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="card-grid" data-stagger>
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part(
						'template-parts/cards/course',
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
			<?php get_template_part( 'template-parts/sections/empty', null, array( 'text' => __( 'Nos formations seront publiées très bientôt.', 'al-ghazzali' ) ) ); ?>
		<?php endif; ?>
	</div>
</section>
