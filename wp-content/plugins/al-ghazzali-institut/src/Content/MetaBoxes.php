<?php
/**
 * Boîtes de saisie : une boîte par zone de la page formation, plus l'équipe et les événements.
 *
 * @package AlGhazzali\Core
 */

namespace AlGhazzali\Core\Content;

use AlGhazzali\Core\Admin\MetaBox;
use AlGhazzali\Core\Hookable;

defined( 'ABSPATH' ) || exit;

final class MetaBoxes implements Hookable {

	public function register(): void {
		// Libellés traduits : construits à l'init, quand les traductions peuvent être chargées.
		add_action( 'init', array( $this, 'register_boxes' ), 1 );
	}

	public function register_boxes(): void {
		$course = array( PostTypes::COURSE );

		$boxes = array(
			new MetaBox( 'ag_course_header', __( 'En-tête et carte de la formation', 'al-ghazzali-institut' ), $course, Fields::course_header(), 'normal', __( 'Le chapeau de la page est le champ « Extrait ». Ces informations s’affichent en haut de la fiche et sur la carte de la formation.', 'al-ghazzali-institut' ) ),
			new MetaBox( 'ag_course_levels', __( 'Zone « Les niveaux »', 'al-ghazzali-institut' ), $course, Fields::course_levels() ),
			new MetaBox( 'ag_course_format', __( 'Zone « Format & modalités »', 'al-ghazzali-institut' ), $course, Fields::course_format() ),
			new MetaBox( 'ag_course_schedule', __( 'Zone « Emploi du temps »', 'al-ghazzali-institut' ), $course, Fields::course_schedule() ),
			new MetaBox( 'ag_course_pricing', __( 'Zone « Réductions »', 'al-ghazzali-institut' ), $course, Fields::course_pricing(), 'normal', __( 'Facultatif : sans réduction, la zone n’apparaît pas.', 'al-ghazzali-institut' ) ),
			new MetaBox( 'ag_course_faq', __( 'Zone « Questions fréquentes »', 'al-ghazzali-institut' ), $course, Fields::course_faq() ),
			new MetaBox( 'ag_team_details', __( 'Profil', 'al-ghazzali-institut' ), array( PostTypes::TEACHER ), Fields::team(), 'side', __( 'Photo : « Image mise en avant ». Présentation courte : « Extrait ».', 'al-ghazzali-institut' ) ),
			new MetaBox( 'ag_event_details', __( 'Date et lieu', 'al-ghazzali-institut' ), array( PostTypes::EVENT ), Fields::event(), 'side' ),
		);

		foreach ( $boxes as $box ) {
			$box->register();
		}
	}
}
