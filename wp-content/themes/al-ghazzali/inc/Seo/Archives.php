<?php
/**
 * Titre SEO (≤ 60 caractères), description, intitulé et chapeau des archives de contenus.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Seo;

use AlGhazzali\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

final class Archives {

	/**
	 * @return array{title: string, description: string, heading: string, lead: string}|null
	 */
	public static function seo( string $post_type ): ?array {
		return self::all()[ $post_type ] ?? null;
	}

	/**
	 * @return array<string, array{title: string, description: string, heading: string, lead: string}>
	 */
	private static function all(): array {
		return array(
			PostTypes::COURSE  => array(
				'title'       => __( 'Formations : cours d’arabe en ligne par niveaux', 'al-ghazzali' ),
				'description' => __( 'Nos formations en langue arabe : arabe littéraire et expression orale, du débutant à l’avancé. Cours en direct, classes hommes et femmes séparées.', 'al-ghazzali' ),
				'heading'     => __( 'Nos formations', 'al-ghazzali' ),
				'lead'        => __( 'Un parcours par niveaux pour lire, comprendre et parler l’arabe, en direct et en petits groupes.', 'al-ghazzali' ),
			),
			PostTypes::TEACHER => array(
				'title'       => __( 'Notre équipe : enseignants d’arabe | Al Ghazzali', 'al-ghazzali' ),
				'description' => __( 'La direction et les enseignants d’Al Ghazzali Institut : une équipe qualifiée pour enseigner la langue arabe aux francophones, hommes et femmes.', 'al-ghazzali' ),
				'heading'     => __( 'Notre équipe', 'al-ghazzali' ),
				'lead'        => __( 'La direction et les enseignants qui font vivre l’institut et accompagnent chaque élève.', 'al-ghazzali' ),
			),
			PostTypes::EVENT   => array(
				'title'       => __( 'Événements : ateliers et réunions | Al Ghazzali', 'al-ghazzali' ),
				'description' => __( 'L’agenda de l’institut : réunions d’information, ateliers découverte et rencontres autour de la langue arabe, en ligne et sur place.', 'al-ghazzali' ),
				'heading'     => __( 'Nos événements', 'al-ghazzali' ),
				'lead'        => __( 'Réunions d’information, ateliers et rencontres autour de la langue arabe.', 'al-ghazzali' ),
			),
		);
	}
}
