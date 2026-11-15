<?php
/**
 * Template Name: Contact
 *
 * Coordonnées issues du Customizer (aucun formulaire : les inscriptions passent par la plateforme élèves).
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

$ag_institute = ag_institute();
$ag_channels  = array();

if ( '' !== $ag_institute->get( 'email' ) ) {
	$ag_channels[] = array( 'mail', __( 'E-mail', 'al-ghazzali' ), $ag_institute->get( 'email' ), 'mailto:' . $ag_institute->get( 'email' ) );
}
if ( '' !== $ag_institute->whatsapp_url() ) {
	$ag_channels[] = array( 'whatsapp', 'WhatsApp', __( 'Réponse rapide', 'al-ghazzali' ), $ag_institute->whatsapp_url() );
}
if ( '' !== $ag_institute->get( 'phone' ) ) {
	$ag_channels[] = array( 'phone', __( 'Téléphone', 'al-ghazzali' ), $ag_institute->get( 'phone' ), $ag_institute->phone_href() );
}
if ( $ag_institute->has_address() ) {
	$ag_address    = $ag_institute->get( 'street' ) . ', ' . $ag_institute->get( 'postal_code' ) . ' ' . $ag_institute->get( 'city' );
	$ag_channels[] = array( 'pin', __( 'Adresse', 'al-ghazzali' ), $ag_address, 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $ag_address ) );
}

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part(
		'template-parts/sections/page-hero',
		null,
		array(
			'title'  => get_the_title(),
			'lead'   => has_excerpt() ? get_the_excerpt() : '',
			'kicker' => __( 'Contact', 'al-ghazzali' ),
			'enroll' => $ag_institute->enroll_is_external(),
		)
	);
	?>
	<section class="section">
		<div class="container">
			<?php if ( array() !== $ag_channels ) : ?>
				<ul class="contact-grid" data-stagger>
					<?php foreach ( $ag_channels as list( $ag_icon, $ag_label, $ag_value, $ag_url ) ) : ?>
						<li class="contact-card" data-reveal data-spotlight>
							<span class="feature-card__icon"><?php echo ag_icon( $ag_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<h2 class="contact-card__label"><?php echo esc_html( $ag_label ); ?></h2>
							<a class="contact-card__value" href="<?php echo esc_url( $ag_url ); ?>"<?php echo str_starts_with( $ag_url, 'http' ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $ag_value ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php elseif ( current_user_can( 'customize' ) ) : ?>
				<p class="notice"><?php esc_html_e( 'Renseignez vos coordonnées dans Apparence › Personnaliser › Al Ghazzali Institut › Coordonnées.', 'al-ghazzali' ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $ag_institute->get( 'hours' ) ) : ?>
				<div class="hours" data-reveal>
					<h2 class="section-title section-title--sm"><?php esc_html_e( 'Horaires d’accueil', 'al-ghazzali' ); ?></h2>
					<p><?php echo nl2br( esc_html( $ag_institute->get( 'hours' ) ) ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( '' !== trim( get_the_content() ) ) : ?>
				<div class="prose container--narrow" data-reveal><?php the_content(); ?></div>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/socials' ); ?>
		</div>
	</section>
	<?php
endwhile;

get_footer();
