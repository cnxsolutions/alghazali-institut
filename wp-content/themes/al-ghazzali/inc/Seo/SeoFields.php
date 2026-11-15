<?php
/**
 * Champs SEO saisis sur chaque contenu : titre, meta description, masquage des moteurs.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Seo;

use AlGhazzali\Core\Admin\Field;

defined( 'ABSPATH' ) || exit;

final class SeoFields {

	public const TITLE       = '_ag_seo_title';
	public const DESCRIPTION = '_ag_seo_description';
	public const NOINDEX     = '_ag_seo_noindex';

	/**
	 * @return Field[]
	 */
	public static function fields(): array {
		return array(
			new Field( self::TITLE, __( 'Titre SEO', 'al-ghazzali' ), 'text', null, __( 'Balise <title> complète, 50 à 60 caractères. Vide : titre de la page + nom du site.', 'al-ghazzali' ) ),
			new Field( self::DESCRIPTION, __( 'Meta description', 'al-ghazzali' ), 'textarea', null, __( '140 à 160 caractères, avec le mot-clé principal et une incitation à cliquer.', 'al-ghazzali' ) ),
			new Field( self::NOINDEX, __( 'Masquer des moteurs (noindex)', 'al-ghazzali' ), 'checkbox' ),
		);
	}
}
