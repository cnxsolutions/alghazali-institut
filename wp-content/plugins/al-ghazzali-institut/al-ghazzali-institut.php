<?php
/**
 * Plugin Name:       Al Ghazali Institut
 * Description:       Contenus de l'institut gérés depuis l'administration : formations (niveaux, modalités, emploi du temps, tarifs, FAQ), équipe et événements.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            CNX Solutions
 * Author URI:        https://cnx-solutions.fr
 * Text Domain:       al-ghazzali-institut
 *
 * @package AlGhazzali\Core
 */

defined( 'ABSPATH' ) || exit;

define( 'AG_CORE_VERSION', '1.0.0' );
define( 'AG_CORE_URI', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/src/autoload.php';

( new AlGhazzali\Core\Plugin() )->boot();

register_activation_hook( __FILE__, array( AlGhazzali\Core\Plugin::class, 'activate' ) );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
