<?php
/**
 * Liste répétable (niveaux, créneaux, questions…) : chaque ligne regroupe plusieurs champs.
 *
 * @package AlGhazzali\Core
 */

namespace AlGhazzali\Core\Admin;

defined( 'ABSPATH' ) || exit;

final class Repeater {

	// Plafond de sécurité : une requête forgée ne peut pas gonfler la méta indéfiniment.
	private const MAX_ROWS = 60;

	/**
	 * @param string  $key       Clé méta (préfixée « _ag_ »).
	 * @param string  $label     Libellé de la liste.
	 * @param Field[] $fields    Colonnes d'une ligne.
	 * @param string  $add_label Libellé du bouton d'ajout.
	 * @param string  $help      Aide affichée sous le libellé.
	 */
	public function __construct(
		public readonly string $key,
		public readonly string $label,
		public readonly array $fields,
		public readonly string $add_label,
		public readonly string $help = '',
	) {}

	/**
	 * Nettoie chaque ligne, retire les lignes vides et renumérote.
	 *
	 * @param mixed $value Valeur brute (déjà « unslashée »).
	 * @return list<array<string, string>>
	 */
	public function sanitize( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$rows = array();
		foreach ( $value as $raw_row ) {
			if ( ! is_array( $raw_row ) ) {
				continue;
			}

			$row = array();
			foreach ( $this->fields as $field ) {
				$row[ $field->key ] = $field->sanitize( $raw_row[ $field->key ] ?? '' );
			}
			if ( '' === implode( '', $row ) ) {
				continue;
			}

			$rows[] = $row;
			if ( count( $rows ) >= self::MAX_ROWS ) {
				break;
			}
		}
		return $rows;
	}
}
