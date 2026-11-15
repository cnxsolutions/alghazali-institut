<?php
/**
 * Page introuvable : explication et chemins de retour.
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part(
	'template-parts/sections/page-hero',
	null,
	array(
		'title'  => __( 'Cette page est *introuvable*', 'al-ghazzali' ),
		'lead'   => __( 'Le lien est peut-être ancien ou la page a été déplacée. Voici quelques pistes pour retrouver votre chemin.', 'al-ghazzali' ),
		'kicker' => __( 'Erreur 404', 'al-ghazzali' ),
		'arabic' => '٤٠٤',
	)
);
?>
<section class="section">
	<div class="container container--narrow">
		<?php get_search_form(); ?>
		<?php get_template_part( 'template-parts/sections/empty', null, array( 'text' => __( 'Vous pouvez aussi revenir à nos formations ou nous écrire directement.', 'al-ghazzali' ) ) ); ?>
		<p class="section-foot"><a class="link-arrow" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Retour à l’accueil', 'al-ghazzali' ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></p>
	</div>
</section>
<?php
get_footer();
