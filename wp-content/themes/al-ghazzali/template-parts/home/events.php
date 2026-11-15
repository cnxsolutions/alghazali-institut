<?php
/**
 * Accueil : prochains événements (masqué s'il n'y en a pas).
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\Events;
use AlGhazzali\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

$ag_events = Events::upcoming( 3 );
if ( array() === $ag_events ) {
	return;
}
?>
<section class="section section--sand events-preview" aria-labelledby="events-title">
	<div class="container">
		<div class="section-row">
			<?php ag_section_head( __( 'Agenda', 'al-ghazzali' ), __( 'Les *prochains* rendez-vous', 'al-ghazzali' ), '', '', 'events-title' ); ?>
			<a class="link-arrow" href="<?php echo esc_url( (string) get_post_type_archive_link( PostTypes::EVENT ) ); ?>" data-reveal><?php esc_html_e( 'Tous les événements', 'al-ghazzali' ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>
		<div class="event-list" data-stagger>
			<?php
			foreach ( $ag_events as $ag_event ) {
				get_template_part( 'template-parts/cards/event', null, array( 'post' => $ag_event ) );
			}
			?>
		</div>
	</div>
</section>
