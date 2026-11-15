<?php
/**
 * État vide utile : explication + chemin de sortie.
 *
 * @package AlGhazzali
 *
 * Arguments : text.
 */

use AlGhazzali\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;
?>
<div class="empty-state" data-reveal>
	<span class="empty-state__icon" aria-hidden="true"><?php echo ag_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<p><?php echo esc_html( (string) ( $args['text'] ?? '' ) ); ?></p>
	<div class="empty-state__actions">
		<a class="btn btn--primary" href="<?php echo esc_url( (string) get_post_type_archive_link( PostTypes::COURSE ) ); ?>"><?php esc_html_e( 'Voir nos formations', 'al-ghazzali' ); ?></a>
		<a class="btn btn--outline" href="<?php echo esc_url( ag_institute()->contact_url() ); ?>"><?php esc_html_e( 'Nous contacter', 'al-ghazzali' ); ?></a>
	</div>
</div>
