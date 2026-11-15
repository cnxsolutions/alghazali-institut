<?php
/**
 * Boîte « Référencement (SEO) » sur les pages, articles, formations, membres de l'équipe et événements.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Seo;

use AlGhazzali\Core\Admin\MetaBox;
use AlGhazzali\Core\Content\PostTypes;
use AlGhazzali\Hookable;

defined( 'ABSPATH' ) || exit;

final class SeoBox implements Hookable {

	public function register(): void {
		// Les libellés sont traduits : on attend que le domaine de traduction soit chargé.
		add_action( 'after_setup_theme', array( $this, 'register_box' ), 20 );
	}

	public function register_box(): void {
		( new MetaBox(
			'ag_seo',
			__( 'Référencement (SEO)', 'al-ghazzali' ),
			array( 'post', 'page', PostTypes::COURSE, PostTypes::TEACHER, PostTypes::EVENT ),
			SeoFields::fields(),
			'normal',
			'',
			// Après les zones de contenu.
			'low'
		) )->register();
	}
}
