<?php
/**
 * Archive des événements : à venir (requête principale) puis événements passés.
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\Events;
use AlGhazzali\Core\Content\PostTypes;
use AlGhazzali\Seo\Archives;

defined( 'ABSPATH' ) || exit;

$ag_seo  = Archives::seo( PostTypes::EVENT );
$ag_past = Events::past( 6 );

get_header();
get_template_part(
	'template-parts/sections/page-hero',
	null,
	array(
		'title'  => $ag_seo['heading'],
		'lead'   => $ag_seo['lead'],
		'kicker' => __( 'Agenda', 'al-ghazzali' ),
	)
);
?>
<section class="section" aria-labelledby="upcoming-title">
	<div class="container container--narrow">
		<h2 class="section-title section-title--sm" id="upcoming-title"><?php esc_html_e( 'À venir', 'al-ghazzali' ); ?></h2>
		<?php if ( have_posts() ) : ?>
			<div class="event-list" data-stagger>
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/cards/event', null, array( 'post' => get_post() ) );
				}
				?>
			</div>
			<?php ag_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/sections/empty', null, array( 'text' => __( 'Aucun événement n’est programmé pour le moment. Suivez-nous sur les réseaux pour ne rien manquer.', 'al-ghazzali' ) ) ); ?>
		<?php endif; ?>

		<?php if ( array() !== $ag_past ) : ?>
			<h2 class="section-title section-title--sm events-past-title"><?php esc_html_e( 'Événements passés', 'al-ghazzali' ); ?></h2>
			<div class="event-list event-list--past">
				<?php
				foreach ( $ag_past as $ag_event ) {
					get_template_part( 'template-parts/cards/event', null, array( 'post' => $ag_event ) );
				}
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
