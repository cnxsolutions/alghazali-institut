<?php
/**
 * Formulaire de recherche accessible.
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

$ag_field_id = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $ag_field_id ); ?>"><?php esc_html_e( 'Rechercher sur le site', 'al-ghazzali' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $ag_field_id ); ?>" class="search-form__input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Rechercher une formation, un article…', 'al-ghazzali' ); ?>" required>
	<button type="submit" class="btn btn--primary"><?php echo ag_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Rechercher', 'al-ghazzali' ); ?></span></button>
</form>
