<?php
/**
 * Al Ghazzali Institut : amorçage du thème.
 *
 * Ce fichier ne contient aucune logique : il charge l'autoloader et démarre les modules.
 * Les contenus (formations, équipe, emploi du temps, événements) viennent de l'extension
 * « Al Ghazzali Institut » ; le thème se charge de leur affichage et du référencement.
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

define( 'AG_THEME_VERSION', '1.1.0' );
define( 'AG_THEME_DIR', get_template_directory() );
define( 'AG_THEME_URI', get_template_directory_uri() );

require_once AG_THEME_DIR . '/inc/autoload.php';

if ( ! defined( 'AG_CORE_VERSION' ) ) {
	( new AlGhazzali\Setup\MissingCore() )->register();
	return;
}

require_once AG_THEME_DIR . '/inc/template-tags.php';

( new AlGhazzali\Theme() )->boot();
