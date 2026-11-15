<?php
/**
 * Accueil : aperçu de l'équipe (ordre choisi dans l'admin).
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

$ag_team = get_posts(
	array(
		'post_type'      => PostTypes::TEACHER,
		'posts_per_page' => 4,
		'no_found_rows'  => true,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
	)
);
if ( array() === $ag_team ) {
	return;
}
?>
<section class="section teachers-preview" aria-labelledby="teachers-title">
	<div class="container">
		<div class="section-row">
			<?php ag_section_head( __( 'Notre équipe', 'al-ghazzali' ), __( 'Des enseignants *qualifiés et à l’écoute*', 'al-ghazzali' ), '', '', 'teachers-title' ); ?>
			<a class="link-arrow" href="<?php echo esc_url( (string) get_post_type_archive_link( PostTypes::TEACHER ) ); ?>" data-reveal><?php esc_html_e( 'Toute l’équipe', 'al-ghazzali' ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>
		<div class="teacher-grid" data-stagger>
			<?php
			foreach ( $ag_team as $ag_member ) {
				get_template_part( 'template-parts/cards/teacher', null, array( 'post' => $ag_member ) );
			}
			?>
		</div>
	</div>
</section>
