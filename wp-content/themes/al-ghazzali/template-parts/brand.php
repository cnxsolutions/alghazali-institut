<?php
/**
 * Logo : logo personnalisé s'il existe, sinon emblème géométrique + nom du site.
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

$ag_name  = get_bloginfo( 'name' );
$ag_parts = preg_split( '/\s+(?=\S+$)/u', $ag_name ) ?: array( $ag_name );
?>
<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
	<?php if ( has_custom_logo() ) : ?>
		<?php echo wp_get_attachment_image( (int) get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'brand__logo', 'alt' => $ag_name ) ); ?>
	<?php else : ?>
		<svg class="brand__mark" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
			<rect x="11" y="11" width="26" height="26" rx="2" />
			<rect x="11" y="11" width="26" height="26" rx="2" transform="rotate(45 24 24)" />
			<circle cx="24" cy="24" r="5.5" />
		</svg>
		<span class="brand__text">
			<span class="brand__name"><?php echo esc_html( $ag_parts[0] ); ?></span>
			<?php if ( isset( $ag_parts[1] ) ) : ?>
				<span class="brand__sub"><?php echo esc_html( $ag_parts[1] ); ?></span>
			<?php endif; ?>
		</span>
	<?php endif; ?>
</a>
