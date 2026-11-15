<?php
/**
 * Page « Notre équipe » : membres regroupés par groupe (direction, enseignants…), ordre de l'admin.
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\PostTypes;
use AlGhazzali\Core\Content\Taxonomies;
use AlGhazzali\Seo\Archives;

defined( 'ABSPATH' ) || exit;

$ag_seo = Archives::seo( PostTypes::TEACHER );

// Regroupement par groupe : un membre rattaché à deux groupes apparaît dans les deux.
$ag_members = array();
while ( have_posts() ) {
	the_post();
	$ag_terms = get_the_terms( get_the_ID(), Taxonomies::TEAM_GROUP );
	foreach ( is_array( $ag_terms ) ? $ag_terms : array( null ) as $ag_term ) {
		$ag_members[ $ag_term instanceof WP_Term ? $ag_term->term_id : 0 ][] = get_post();
	}
}
rewind_posts();

$ag_sections = array();
foreach ( Taxonomies::team_groups() as $ag_group ) {
	if ( isset( $ag_members[ $ag_group->term_id ] ) ) {
		$ag_sections[] = array( $ag_group->name, $ag_members[ $ag_group->term_id ] );
	}
}
if ( isset( $ag_members[0] ) ) {
	$ag_sections[] = array( __( 'L’équipe', 'al-ghazzali' ), $ag_members[0] );
}

get_header();
get_template_part(
	'template-parts/sections/page-hero',
	null,
	array(
		'title'  => $ag_seo['heading'] ?? '',
		'lead'   => $ag_seo['lead'] ?? '',
		'kicker' => __( 'Al Ghazzali Institut', 'al-ghazzali' ),
	)
);
?>
<section class="section">
	<div class="container">
		<?php if ( array() === $ag_sections ) : ?>
			<?php get_template_part( 'template-parts/sections/empty', null, array( 'text' => __( 'La présentation de l’équipe arrive bientôt.', 'al-ghazzali' ) ) ); ?>
		<?php endif; ?>

		<?php foreach ( $ag_sections as list( $ag_title, $ag_team ) ) : ?>
			<div class="team-group">
				<header class="team-group__head" data-reveal>
					<h2 class="team-group__title"><?php echo esc_html( $ag_title ); ?></h2>
				</header>
				<div class="teacher-grid" data-stagger>
					<?php
					foreach ( $ag_team as $ag_member ) {
						get_template_part( 'template-parts/cards/teacher', null, array( 'post' => $ag_member ) );
					}
					?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
<?php
get_footer();
