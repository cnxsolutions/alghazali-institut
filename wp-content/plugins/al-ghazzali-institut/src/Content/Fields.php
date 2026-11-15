<?php
/**
 * Référentiel unique des champs : clés méta, listes de choix et zones de saisie.
 *
 * @package AlGhazzali\Core
 */

namespace AlGhazzali\Core\Content;

use AlGhazzali\Core\Admin\Field;
use AlGhazzali\Core\Admin\Repeater;

defined( 'ABSPATH' ) || exit;

final class Fields {

	// Formation : en-tête de page et carte.
	public const KICKER   = '_ag_kicker';
	public const ARABIC   = '_ag_arabic';
	public const FORMAT   = '_ag_format';
	public const RHYTHM   = '_ag_rhythm';
	public const DURATION = '_ag_duration';
	public const PRICE    = '_ag_price';
	public const START    = '_ag_start';

	// Formation : zones de la page.
	public const LEVELS_INTRO   = '_ag_levels_intro';
	public const LEVELS         = '_ag_levels';
	public const FEATURES       = '_ag_features';
	public const STEPS_TITLE    = '_ag_steps_title';
	public const STEPS          = '_ag_steps';
	public const SLOTS          = '_ag_slots';
	public const DISCOUNTS      = '_ag_discounts';
	public const DISCOUNTS_NOTE = '_ag_discounts_note';
	public const FAQ            = '_ag_faq';

	// Équipe.
	public const ROLE = '_ag_role';

	// Événements.
	public const EVENT_DATE  = '_ag_event_date';
	public const EVENT_START = '_ag_event_start';
	public const EVENT_END   = '_ag_event_end';
	public const EVENT_MODE  = '_ag_event_mode';
	public const EVENT_PLACE = '_ag_event_place';
	public const EVENT_URL   = '_ag_event_url';

	// Plafond de la liste des membres proposés dans les créneaux.
	private const TEAM_CHOICES_LIMIT = 200;

	/**
	 * @return array<string, string>
	 */
	public static function formats(): array {
		return array(
			'online' => __( 'En ligne', 'al-ghazzali-institut' ),
			'onsite' => __( 'En présentiel', 'al-ghazzali-institut' ),
			'hybrid' => __( 'En ligne & présentiel', 'al-ghazzali-institut' ),
		);
	}

	/**
	 * Mode d'un créneau : une séance a lieu soit en ligne, soit sur place.
	 *
	 * @return array<string, string>
	 */
	public static function modes(): array {
		return array(
			'online' => __( 'En ligne', 'al-ghazzali-institut' ),
			'onsite' => __( 'En présentiel', 'al-ghazzali-institut' ),
		);
	}

	/**
	 * Classes séparées : chaque créneau est réservé aux hommes ou aux femmes.
	 *
	 * @return array<string, string>
	 */
	public static function publics(): array {
		return array(
			'hommes' => __( 'Hommes', 'al-ghazzali-institut' ),
			'femmes' => __( 'Femmes', 'al-ghazzali-institut' ),
		);
	}

	/**
	 * @return array<string, string>
	 */
	public static function event_modes(): array {
		return array(
			'onsite' => __( 'Sur place', 'al-ghazzali-institut' ),
			'online' => __( 'En ligne', 'al-ghazzali-institut' ),
		);
	}

	/**
	 * @return Field[]
	 */
	public static function course_header(): array {
		return array(
			new Field( self::KICKER, __( 'Surtitre', 'al-ghazzali-institut' ), 'text', null, __( 'Petit texte au-dessus du titre. Ex. : Langue arabe', 'al-ghazzali-institut' ) ),
			new Field( self::ARABIC, __( 'Titre en arabe (décoratif)', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : العربية الفصحى', 'al-ghazzali-institut' ) ),
			new Field( self::FORMAT, __( 'Format', 'al-ghazzali-institut' ), 'select', self::formats() ),
			new Field( self::RHYTHM, __( 'Rythme', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : 2 séances de 1 h 30 par semaine', 'al-ghazzali-institut' ) ),
			new Field( self::DURATION, __( 'Durée', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : Année scolaire, de septembre à juin', 'al-ghazzali-institut' ) ),
			new Field( self::PRICE, __( 'Tarif affiché sur la carte', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : À partir de 30 € / mois. Vide : non affiché.', 'al-ghazzali-institut' ) ),
			new Field( self::START, __( 'Date de rentrée', 'al-ghazzali-institut' ), 'date' ),
		);
	}

	/**
	 * @return array<Field|Repeater>
	 */
	public static function course_levels(): array {
		return array(
			new Field( self::LEVELS_INTRO, __( 'Introduction du parcours', 'al-ghazzali-institut' ), 'textarea', null, __( 'Une ou deux phrases affichées au-dessus des niveaux.', 'al-ghazzali-institut' ) ),
			new Repeater(
				self::LEVELS,
				__( 'Niveaux', 'al-ghazzali-institut' ),
				array(
					new Field( 'name', __( 'Nom du niveau', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : Niveau 1', 'al-ghazzali-institut' ) ),
					new Field( 'subtitle', __( 'Sous-titre', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : Lire et écrire', 'al-ghazzali-institut' ) ),
					new Field( 'note', __( 'Mention', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : Ouverture en septembre 2027. Vide : aucune mention.', 'al-ghazzali-institut' ) ),
					new Field( 'description', __( 'Présentation du niveau', 'al-ghazzali-institut' ), 'textarea' ),
					new Field( 'objectives', __( 'Objectifs du niveau', 'al-ghazzali-institut' ), 'textarea', null, __( 'Un objectif par ligne.', 'al-ghazzali-institut' ) ),
					new Field( 'programme', __( 'Au programme', 'al-ghazzali-institut' ), 'textarea', null, __( 'Un point par ligne.', 'al-ghazzali-institut' ) ),
					new Field( 'method', __( 'La méthode', 'al-ghazzali-institut' ), 'textarea' ),
					new Field( 'material', __( 'Support de cours', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : Livret de cours fourni', 'al-ghazzali-institut' ) ),
					new Field( 'price', __( 'Tarif du niveau', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : 300 € / an. Vide : pas d’encart tarif.', 'al-ghazzali-institut' ) ),
					new Field( 'price_note', __( 'Précision du tarif', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : 2 séances par semaine, de septembre à juin', 'al-ghazzali-institut' ) ),
				),
				__( 'Ajouter un niveau', 'al-ghazzali-institut' ),
				__( 'Chaque niveau devient un onglet de la frise « Les niveaux », dans l’ordre de cette liste (boutons Monter / Descendre).', 'al-ghazzali-institut' )
			),
		);
	}

	/**
	 * @return array<Field|Repeater>
	 */
	public static function course_format(): array {
		return array(
			new Repeater(
				self::FEATURES,
				__( 'Points clés', 'al-ghazzali-institut' ),
				array(
					new Field( 'label', __( 'Étiquette', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : Rythme', 'al-ghazzali-institut' ) ),
					new Field( 'title', __( 'Titre', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : Deux séances par semaine', 'al-ghazzali-institut' ) ),
					new Field( 'text', __( 'Texte', 'al-ghazzali-institut' ), 'textarea' ),
				),
				__( 'Ajouter un point clé', 'al-ghazzali-institut' )
			),
			new Field( self::STEPS_TITLE, __( 'Titre du déroulé', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : Comment se déroule une séance ?', 'al-ghazzali-institut' ) ),
			new Field( self::STEPS, __( 'Étapes du déroulé', 'al-ghazzali-institut' ), 'textarea', null, __( 'Une étape par ligne : elles s’affichent numérotées.', 'al-ghazzali-institut' ) ),
		);
	}

	/**
	 * @return Repeater[]
	 */
	public static function course_schedule(): array {
		return array(
			new Repeater(
				self::SLOTS,
				__( 'Créneaux', 'al-ghazzali-institut' ),
				array(
					new Field( 'day', __( 'Jour', 'al-ghazzali-institut' ), 'select', static fn(): array => Schedule::days() ),
					new Field( 'start', __( 'Début', 'al-ghazzali-institut' ), 'time' ),
					new Field( 'end', __( 'Fin', 'al-ghazzali-institut' ), 'time' ),
					new Field( 'level', __( 'Niveau', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : Niveau 1', 'al-ghazzali-institut' ) ),
					new Field( 'public', __( 'Classe', 'al-ghazzali-institut' ), 'select', self::publics() ),
					new Field( 'teacher', __( 'Enseignant(e)', 'al-ghazzali-institut' ), 'select', static fn(): array => self::team_choices() ),
					new Field( 'mode', __( 'Mode', 'al-ghazzali-institut' ), 'select', self::modes() ),
				),
				__( 'Ajouter un créneau', 'al-ghazzali-institut' ),
				__( 'Un créneau = une séance hebdomadaire. Il apparaît sur la fiche formation et dans l’emploi du temps général. Le nom de l’enseignant(e) s’affiche dès que sa fiche Équipe est publiée.', 'al-ghazzali-institut' )
			),
		);
	}

	/**
	 * @return array<Field|Repeater>
	 */
	public static function course_pricing(): array {
		return array(
			new Repeater(
				self::DISCOUNTS,
				__( 'Réductions', 'al-ghazzali-institut' ),
				array(
					new Field( 'value', __( 'Réduction', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : -10 %', 'al-ghazzali-institut' ) ),
					new Field( 'label', __( 'Condition', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : 2 inscriptions dans la même famille', 'al-ghazzali-institut' ) ),
				),
				__( 'Ajouter une réduction', 'al-ghazzali-institut' )
			),
			new Field( self::DISCOUNTS_NOTE, __( 'Précision', 'al-ghazzali-institut' ), 'textarea', null, __( 'Conditions d’application, affichées sous les réductions.', 'al-ghazzali-institut' ) ),
		);
	}

	/**
	 * @return Repeater[]
	 */
	public static function course_faq(): array {
		return array(
			new Repeater(
				self::FAQ,
				__( 'Questions fréquentes', 'al-ghazzali-institut' ),
				array(
					new Field( 'question', __( 'Question', 'al-ghazzali-institut' ), 'text' ),
					new Field( 'answer', __( 'Réponse', 'al-ghazzali-institut' ), 'textarea' ),
				),
				__( 'Ajouter une question', 'al-ghazzali-institut' ),
				__( 'Affichées en accordéon et transmises à Google (données structurées FAQ) à partir de deux questions.', 'al-ghazzali-institut' )
			),
		);
	}

	/**
	 * @return Field[]
	 */
	public static function team(): array {
		return array(
			new Field( self::ROLE, __( 'Fonction', 'al-ghazzali-institut' ), 'text', null, __( 'Ex. : Enseignante d’arabe · classes femmes', 'al-ghazzali-institut' ) ),
		);
	}

	/**
	 * @return Field[]
	 */
	public static function event(): array {
		return array(
			new Field( self::EVENT_DATE, __( 'Date', 'al-ghazzali-institut' ), 'date' ),
			new Field( self::EVENT_START, __( 'Heure de début', 'al-ghazzali-institut' ), 'time' ),
			new Field( self::EVENT_END, __( 'Heure de fin', 'al-ghazzali-institut' ), 'time' ),
			new Field( self::EVENT_MODE, __( 'Format', 'al-ghazzali-institut' ), 'select', self::event_modes() ),
			new Field( self::EVENT_PLACE, __( 'Lieu ou adresse', 'al-ghazzali-institut' ), 'text' ),
			new Field( self::EVENT_URL, __( 'Lien d’inscription ou de diffusion', 'al-ghazzali-institut' ), 'url' ),
		);
	}

	/**
	 * Libellé d'une valeur de liste, ou chaîne vide si inconnue.
	 *
	 * @param array<string, string> $choices Liste de choix.
	 * @param string                $value   Valeur enregistrée.
	 */
	public static function label( array $choices, string $value ): string {
		return $choices[ $value ] ?? '';
	}

	/**
	 * Membres de l'équipe proposés dans les créneaux (brouillons compris, pour préparer la rentrée).
	 *
	 * @return array<int, string>
	 */
	private static function team_choices(): array {
		$members = get_posts(
			array(
				'post_type'      => PostTypes::TEACHER,
				'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
				'posts_per_page' => self::TEAM_CHOICES_LIMIT,
				'no_found_rows'  => true,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		return wp_list_pluck( $members, 'post_title', 'ID' );
	}
}
