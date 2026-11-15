<?php
/**
 * En-tête du site.
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

$ag_account_url = ag_institute()->get( 'account_url' );
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#0f2a2c">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Aller au contenu', 'al-ghazzali' ); ?></a>
<div class="scroll-progress" aria-hidden="true"></div>

<header class="site-header" data-header>
	<div class="container site-header__inner">
		<?php get_template_part( 'template-parts/brand' ); ?>

		<nav class="site-nav" id="site-nav" aria-label="<?php esc_attr_e( 'Navigation principale', 'al-ghazzali' ); ?>" data-nav>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'site-nav__menu',
					'depth'          => 2,
					'fallback_cb'    => 'ag_primary_menu_fallback',
				)
			);
			?>
			<div class="site-nav__cta">
				<?php if ( '' !== $ag_account_url ) : ?>
					<a class="btn btn--outline-light" href="<?php echo esc_url( $ag_account_url ); ?>" target="_blank" rel="noopener"><?php echo ag_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Mon espace', 'al-ghazzali' ); ?></a>
				<?php endif; ?>
				<a class="btn btn--gold" <?php echo ag_enroll_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( ag_enroll_label() ); ?></a>
			</div>
		</nav>

		<div class="site-header__actions">
			<?php if ( '' !== $ag_account_url ) : ?>
				<a class="header-link" href="<?php echo esc_url( $ag_account_url ); ?>" target="_blank" rel="noopener"><?php echo ag_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Mon espace', 'al-ghazzali' ); ?></span></a>
			<?php endif; ?>
			<a class="btn btn--gold btn--sm header-cta" <?php echo ag_enroll_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( ag_enroll_label() ); ?></a>
			<button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" data-nav-toggle data-label-open="<?php esc_attr_e( 'Ouvrir le menu', 'al-ghazzali' ); ?>" data-label-close="<?php esc_attr_e( 'Fermer le menu', 'al-ghazzali' ); ?>" data-label-submenu="<?php esc_attr_e( 'Afficher le sous-menu :', 'al-ghazzali' ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Ouvrir le menu', 'al-ghazzali' ); ?></span>
				<span class="nav-toggle__bars" aria-hidden="true"></span>
			</button>
		</div>
	</div>
</header>

<main id="main" class="site-main" tabindex="-1">
