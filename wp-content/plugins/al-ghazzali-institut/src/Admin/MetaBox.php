<?php
/**
 * Boîte méta générique : enregistre, affiche et sauvegarde des champs simples et des listes répétables.
 *
 * @package AlGhazzali\Core
 */

namespace AlGhazzali\Core\Admin;

use AlGhazzali\Core\Hookable;

defined( 'ABSPATH' ) || exit;

final class MetaBox implements Hookable {

	/**
	 * @param string                 $id         Identifiant unique de la boîte.
	 * @param string                 $title      Titre affiché.
	 * @param string[]               $post_types Types de contenu concernés.
	 * @param array<Field|Repeater>  $fields     Champs, dans l'ordre d'affichage.
	 * @param string                 $context    normal|side.
	 * @param string                 $intro      Phrase d'explication en tête de boîte (où s'affiche la zone).
	 * @param string                 $priority   high|default|low : ordre parmi les boîtes.
	 */
	public function __construct(
		private readonly string $id,
		private readonly string $title,
		private readonly array $post_types,
		private readonly array $fields,
		private readonly string $context = 'normal',
		private readonly string $intro = '',
		private readonly string $priority = 'default',
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'register_meta' ) );
		add_action( 'add_meta_boxes', array( $this, 'add' ) );

		foreach ( $this->post_types as $post_type ) {
			add_action( "save_post_{$post_type}", array( $this, 'save' ) );
		}
	}

	public function register_meta(): void {
		foreach ( $this->post_types as $post_type ) {
			foreach ( $this->fields as $field ) {
				$is_list = $field instanceof Repeater;
				register_post_meta(
					$post_type,
					$field->key,
					array(
						'type'              => $is_list ? 'array' : 'string',
						'single'            => true,
						'default'           => $is_list ? array() : '',
						'show_in_rest'      => false,
						'sanitize_callback' => array( $field, 'sanitize' ),
						'auth_callback'     => static fn( bool $allowed, string $meta_key, int $post_id ): bool => current_user_can( 'edit_post', $post_id ),
					)
				);
			}
		}
	}

	public function add(): void {
		// Par défaut, les zones suivent l'extrait (chapeau), dans leur ordre d'enregistrement.
		add_meta_box( $this->id, $this->title, array( $this, 'render' ), $this->post_types, $this->context, $this->priority );
	}

	public function render( \WP_Post $post ): void {
		wp_nonce_field( $this->id . '_save', $this->id . '_nonce' );

		if ( '' !== $this->intro ) {
			echo '<p class="ag-box__intro">' . esc_html( $this->intro ) . '</p>';
		}

		echo '<div class="ag-fields">';
		foreach ( $this->fields as $field ) {
			if ( $field instanceof Repeater ) {
				$this->render_repeater( $field, get_post_meta( $post->ID, $field->key, true ) );
				continue;
			}
			$this->render_field( $field, (string) get_post_meta( $post->ID, $field->key, true ), $field->key, 'ag-field-' . sanitize_html_class( ltrim( $field->key, '_' ) ) );
		}
		echo '</div>';
	}

	public function save( int $post_id ): void {
		$nonce_key = $this->id . '_nonce';
		$nonce     = isset( $_POST[ $nonce_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $nonce_key ] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, $this->id . '_save' ) ) {
			return;
		}
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( $this->fields as $field ) {
			// Nettoyé juste en dessous par Field::sanitize() ou Repeater::sanitize().
			$raw   = isset( $_POST[ $field->key ] ) ? wp_unslash( $_POST[ $field->key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$clean = $field->sanitize( $raw );

			if ( '' === $clean || array() === $clean ) {
				delete_post_meta( $post_id, $field->key );
				continue;
			}
			update_post_meta( $post_id, $field->key, wp_slash( $clean ) );
		}
	}

	/**
	 * @param bool $compact Ligne de liste répétable : l'aide devient l'indication du champ, pour ne pas la répéter à chaque ligne.
	 */
	private function render_field( Field $field, string $value, string $name, string $id, bool $compact = false ): void {
		printf( '<div class="ag-field ag-field--%s">', esc_attr( $field->type ) );
		$placeholder = $compact && '' !== $field->help ? sprintf( ' placeholder="%s"', esc_attr( $field->help ) ) : '';

		if ( 'checkbox' !== $field->type ) {
			echo '<label class="ag-field__label" for="' . esc_attr( $id ) . '">' . esc_html( $field->label ) . '</label>';
		}

		switch ( $field->type ) {
			case 'textarea':
				printf(
					'<textarea class="large-text" rows="%1$d" id="%2$s" name="%3$s"%4$s>%5$s</textarea>',
					$compact ? 3 : 4,
					esc_attr( $id ),
					esc_attr( $name ),
					$placeholder, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé ci-dessus.
					esc_textarea( $value )
				);
				break;

			case 'select':
				printf( '<select id="%1$s" name="%2$s"><option value="">%3$s</option>', esc_attr( $id ), esc_attr( $name ), esc_html__( '— Choisir —', 'al-ghazzali-institut' ) );
				foreach ( $field->options() as $option_value => $option_label ) {
					printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( (string) $option_value ), selected( $value, (string) $option_value, false ), esc_html( $option_label ) );
				}
				echo '</select>';
				break;

			case 'checkbox':
				printf(
					'<label class="ag-field__label" for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s> %4$s</label>',
					esc_attr( $id ),
					esc_attr( $name ),
					checked( $value, '1', false ),
					esc_html( $field->label )
				);
				break;

			default:
				$input_type = in_array( $field->type, array( 'url', 'date', 'time' ), true ) ? $field->type : 'text';
				printf(
					'<input type="%1$s" class="widefat" id="%2$s" name="%3$s" value="%4$s"%5$s>',
					esc_attr( $input_type ),
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $value ),
					$placeholder // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé ci-dessus.
				);
		}

		if ( '' !== $field->help && ! $compact ) {
			echo '<p class="description">' . esc_html( $field->help ) . '</p>';
		}
		echo '</div>';
	}

	/**
	 * @param mixed $rows Lignes enregistrées (tableau attendu).
	 */
	private function render_repeater( Repeater $repeater, mixed $rows ): void {
		$rows = is_array( $rows ) ? array_values( array_filter( $rows, 'is_array' ) ) : array();

		echo '<div class="ag-field ag-field--repeater" data-ag-repeater>';
		echo '<p class="ag-field__label">' . esc_html( $repeater->label ) . '</p>';
		if ( '' !== $repeater->help ) {
			echo '<p class="description">' . esc_html( $repeater->help ) . '</p>';
		}

		echo '<ol class="ag-repeater__rows" data-ag-rows>';
		foreach ( $rows as $index => $row ) {
			$this->render_row( $repeater, $row, (string) $index );
		}
		echo '</ol>';

		// Modèle de ligne vierge, cloné par admin.js (__i__ remplacé par un index unique).
		echo '<template data-ag-template>';
		$this->render_row( $repeater, array(), '__i__' );
		echo '</template>';

		printf( '<button type="button" class="button" data-ag-add>%s</button>', esc_html( $repeater->add_label ) );
		echo '</div>';
	}

	/**
	 * @param array<string, mixed> $row Valeurs de la ligne.
	 */
	private function render_row( Repeater $repeater, array $row, string $index ): void {
		echo '<li class="ag-repeater__row">';
		echo '<div class="ag-repeater__bar">';
		echo '<span class="ag-repeater__number" aria-hidden="true"></span>';
		printf( '<button type="button" class="button-link" data-ag-move="-1">%s</button>', esc_html__( 'Monter', 'al-ghazzali-institut' ) );
		printf( '<button type="button" class="button-link" data-ag-move="1">%s</button>', esc_html__( 'Descendre', 'al-ghazzali-institut' ) );
		printf( '<button type="button" class="button-link button-link-delete" data-ag-remove>%s</button>', esc_html__( 'Supprimer', 'al-ghazzali-institut' ) );
		echo '</div>';

		echo '<div class="ag-fields">';
		foreach ( $repeater->fields as $field ) {
			$this->render_field(
				$field,
				is_scalar( $row[ $field->key ] ?? null ) ? (string) $row[ $field->key ] : '',
				$repeater->key . '[' . $index . '][' . $field->key . ']',
				'ag-' . sanitize_html_class( ltrim( $repeater->key, '_' ) ) . '-' . $index . '-' . $field->key,
				true
			);
		}
		echo '</div></li>';
	}
}
