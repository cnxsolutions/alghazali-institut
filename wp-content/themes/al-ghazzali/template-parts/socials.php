<?php
/**
 * Liens vers les réseaux sociaux renseignés dans le Customizer.
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

$ag_socials = ag_institute()->socials();
if ( array() === $ag_socials ) {
	return;
}
?>
<ul class="socials">
	<?php foreach ( $ag_socials as $ag_network => $ag_url ) : ?>
		<li>
			<a href="<?php echo esc_url( $ag_url ); ?>" target="_blank" rel="noopener me" aria-label="<?php echo esc_attr( ucfirst( $ag_network ) ); ?>">
				<?php echo ag_icon( $ag_network ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
