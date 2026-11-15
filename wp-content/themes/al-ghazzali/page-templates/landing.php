<?php
/**
 * Template Name: Page SEO (atterrissage)
 *
 * Page ciblant une requête (ex. « cours d'arabe en ligne ») : contenu éditorial + cartes des formations.
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$ag_courses = ag_courses( array( 'limit' => 6 ) );

	get_template_part(
		'template-parts/sections/page-hero',
		null,
		array(
			'title'     => get_the_title(),
			'lead'      => has_excerpt() ? get_the_excerpt() : '',
			'arabic'    => 'العربية',
			'enroll'    => true,
			'secondary' => array() !== $ag_courses ? array( __( 'Voir les formations', 'al-ghazzali' ), '#formations' ) : null,
		)
	);
	?>
	<section class="section">
		<div class="container container--narrow prose" data-reveal>
			<?php the_content(); ?>
		</div>
	</section>

	<?php if ( array() !== $ag_courses ) : ?>
		<section class="section section--sand" id="formations" aria-labelledby="landing-courses-title">
			<div class="container">
				<?php ag_section_head( __( 'Nos formations', 'al-ghazzali' ), __( 'Choisissez *votre parcours*', 'al-ghazzali' ), '', 'section-head--center', 'landing-courses-title' ); ?>
				<div class="card-grid" data-stagger>
					<?php
					foreach ( $ag_courses as $ag_course ) {
						get_template_part( 'template-parts/cards/course', null, array( 'post' => $ag_course ) );
					}
					?>
				</div>
				<p class="section-foot">
					<a class="link-arrow" href="<?php echo esc_url( (string) get_post_type_archive_link( PostTypes::COURSE ) ); ?>"><?php esc_html_e( 'Toutes nos formations', 'al-ghazzali' ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				</p>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
