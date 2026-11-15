<?php
/**
 * Accueil : les formations (cartes gérées dans l'extension, ordre de l'admin).
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

$ag_courses = ag_courses( array( 'limit' => 6 ) );
if ( array() === $ag_courses ) {
	return;
}
?>
<section class="section section--sand courses" aria-labelledby="courses-title">
	<div class="container">
		<?php ag_section_head( __( 'Nos formations', 'al-ghazzali' ), __( 'Un parcours *par niveaux*', 'al-ghazzali' ), __( 'Choisissez votre formation, puis le niveau qui vous correspond : nous vous orientons si vous hésitez.', 'al-ghazzali' ), 'section-head--center', 'courses-title' ); ?>

		<div class="card-grid" data-stagger>
			<?php
			foreach ( $ag_courses as $ag_course ) {
				get_template_part( 'template-parts/cards/course', null, array( 'post' => $ag_course ) );
			}
			?>
		</div>

		<p class="section-foot" data-reveal>
			<a class="btn btn--primary" href="<?php echo esc_url( (string) get_post_type_archive_link( PostTypes::COURSE ) ); ?>"><?php esc_html_e( 'Voir toutes les formations', 'al-ghazzali' ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</p>
	</div>
</section>
