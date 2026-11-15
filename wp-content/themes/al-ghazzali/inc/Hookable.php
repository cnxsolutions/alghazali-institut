<?php
/**
 * Contrat commun des modules : chacun déclare ses propres hooks.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali;

defined( 'ABSPATH' ) || exit;

interface Hookable {

	/**
	 * Branche les actions et filtres du module.
	 */
	public function register(): void;
}
