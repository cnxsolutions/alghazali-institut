<?php
/**
 * Définition d'un champ : libellé, type et règle de nettoyage.
 *
 * @package AlGhazzali\Core
 */

namespace AlGhazzali\Core\Admin;

defined( 'ABSPATH' ) || exit;

final class Field {

	/**
	 * @param string                               $key     Clé méta (préfixée « _ag_ »), ou clé de colonne dans une liste répétable.
	 * @param string                               $label   Libellé affiché dans l'admin.
	 * @param string                               $type    text|textarea|url|date|time|select|checkbox.
	 * @param array<string, string>|\Closure|null  $options Choix d'un select ; une closure est résolue tard (après init).
	 * @param string                               $help    Aide affichée sous le champ.
	 */
	public function __construct(
		public readonly string $key,
		public readonly string $label,
		public readonly string $type = 'text',
		private readonly array|\Closure|null $options = null,
		public readonly string $help = '',
	) {}

	/**
	 * @return array<string, string>
	 */
	public function options(): array {
		if ( $this->options instanceof \Closure ) {
			return ( $this->options )();
		}
		return $this->options ?? array();
	}

	/**
	 * Nettoie une valeur déjà « unslashée ».
	 *
	 * @param mixed $value Valeur brute.
	 */
	public function sanitize( mixed $value ): string {
		$value = is_scalar( $value ) ? (string) $value : '';

		return match ( $this->type ) {
			'textarea' => sanitize_textarea_field( $value ),
			'url'      => esc_url_raw( trim( $value ), array( 'http', 'https' ) ),
			'date'     => self::is_date( $value ) ? $value : '',
			'time'     => 1 === preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $value ) ? $value : '',
			'select'   => array_key_exists( $value, $this->options() ) ? $value : '',
			'checkbox' => '1' === $value ? '1' : '',
			default    => sanitize_text_field( $value ),
		};
	}

	private static function is_date( string $value ): bool {
		$date = \DateTimeImmutable::createFromFormat( '!Y-m-d', $value );
		return false !== $date && $date->format( 'Y-m-d' ) === $value;
	}
}
