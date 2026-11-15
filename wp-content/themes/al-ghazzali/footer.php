<?php
/**
 * Pied de page du site.
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

$ag_institute = ag_institute();
$ag_courses   = ag_courses( array( 'limit' => 8 ) );
$ag_landings  = ag_landing_pages();
$ag_whatsapp  = $ag_institute->whatsapp_url();
?>
</main>

<?php
if ( ! is_page_template( 'page-templates/contact.php' ) ) {
	get_template_part( 'template-parts/sections/cta' );
}
?>

<footer class="site-footer">
	<div class="site-footer__pattern" aria-hidden="true"></div>
	<div class="container site-footer__grid">
		<div class="site-footer__brand">
			<?php get_template_part( 'template-parts/brand' ); ?>
			<p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
			<?php get_template_part( 'template-parts/socials' ); ?>
		</div>

		<nav class="site-footer__col" aria-labelledby="footer-courses">
			<h2 class="site-footer__title" id="footer-courses"><?php esc_html_e( 'Nos formations', 'al-ghazzali' ); ?></h2>
			<ul>
				<?php foreach ( $ag_courses as $ag_course ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $ag_course ) ); ?>"><?php echo esc_html( get_the_title( $ag_course ) ); ?></a></li>
				<?php endforeach; ?>
				<li><a href="<?php echo esc_url( (string) get_post_type_archive_link( PostTypes::COURSE ) ); ?>"><?php esc_html_e( 'Toutes les formations', 'al-ghazzali' ); ?></a></li>
			</ul>
		</nav>

		<nav class="site-footer__col" aria-labelledby="footer-institute">
			<h2 class="site-footer__title" id="footer-institute"><?php esc_html_e( 'L’institut', 'al-ghazzali' ); ?></h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => 'ag_primary_menu_fallback',
				)
			);
			?>
		</nav>

		<div class="site-footer__col">
			<h2 class="site-footer__title"><?php esc_html_e( 'Nous contacter', 'al-ghazzali' ); ?></h2>
			<ul class="contact-list">
				<?php if ( '' !== $ag_institute->get( 'email' ) ) : ?>
					<li><?php echo ag_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><a href="<?php echo esc_url( 'mailto:' . $ag_institute->get( 'email' ) ); ?>"><?php echo esc_html( $ag_institute->get( 'email' ) ); ?></a></li>
				<?php endif; ?>
				<?php if ( '' !== $ag_institute->get( 'phone' ) ) : ?>
					<li><?php echo ag_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><a href="<?php echo esc_url( $ag_institute->phone_href() ); ?>"><?php echo esc_html( $ag_institute->get( 'phone' ) ); ?></a></li>
				<?php endif; ?>
				<?php if ( '' !== $ag_whatsapp ) : ?>
					<li><?php echo ag_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><a href="<?php echo esc_url( $ag_whatsapp ); ?>" target="_blank" rel="noopener">WhatsApp</a></li>
				<?php endif; ?>
				<?php if ( $ag_institute->has_address() ) : ?>
					<li><?php echo ag_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $ag_institute->get( 'street' ) . ', ' . $ag_institute->get( 'postal_code' ) . ' ' . $ag_institute->get( 'city' ) ); ?></span></li>
				<?php endif; ?>
				<li><?php echo ag_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><a href="<?php echo esc_url( $ag_institute->contact_url() ); ?>"><?php esc_html_e( 'Page contact', 'al-ghazzali' ); ?></a></li>
			</ul>
		</div>
	</div>

	<?php if ( array() !== $ag_landings ) : ?>
		<nav class="container site-footer__seo" aria-label="<?php esc_attr_e( 'Thématiques', 'al-ghazzali' ); ?>">
			<?php foreach ( $ag_landings as $ag_landing ) : ?>
				<a href="<?php echo esc_url( get_permalink( $ag_landing ) ); ?>"><?php echo esc_html( get_the_title( $ag_landing ) ); ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<div class="container site-footer__bottom">
		<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. <?php esc_html_e( 'Tous droits réservés.', 'al-ghazzali' ); ?></p>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'legal',
				'container'      => false,
				'menu_class'     => 'legal-menu',
				'depth'          => 1,
				'fallback_cb'    => false,
			)
		);
		?>
		<p class="credit">
			<?php
			printf(
				/* translators: %s: lien vers l'agence. */
				esc_html__( 'Développé par %s', 'al-ghazzali' ),
				'<a href="https://cnx-solutions.fr" target="_blank" rel="noopener">CNX Solutions</a>'
			);
			?>
		</p>
	</div>
</footer>

<?php if ( '' !== $ag_whatsapp ) : ?>
	<a class="floating-whatsapp" href="<?php echo esc_url( $ag_whatsapp ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Nous écrire sur WhatsApp', 'al-ghazzali' ); ?>">
		<?php echo ag_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
<?php endif; ?>

<button class="back-to-top" type="button" data-back-to-top aria-label="<?php esc_attr_e( 'Revenir en haut de page', 'al-ghazzali' ); ?>">
	<?php echo ag_icon( 'arrow-up' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</button>

<?php wp_footer(); ?>
</body>
</html>
