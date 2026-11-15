<?php
/**
 * Panneau « Al Ghazzali Institut » du Customizer, généré depuis Institute::settings().
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Settings;

use AlGhazzali\Hookable;

defined( 'ABSPATH' ) || exit;

final class Customizer implements Hookable {

	private const PANEL = 'ag_institute';

	public function register(): void {
		add_action( 'customize_register', array( $this, 'customize' ) );
	}

	public function customize( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_panel(
			self::PANEL,
			array(
				'title'    => __( 'Al Ghazzali Institut', 'al-ghazzali' ),
				'priority' => 30,
			)
		);

		$sections = array(
			'contact'  => __( 'Coordonnées', 'al-ghazzali' ),
			'social'   => __( 'Réseaux sociaux', 'al-ghazzali' ),
			'platform' => __( 'Plateforme élèves', 'al-ghazzali' ),
			'home'     => __( 'Accueil : bannière', 'al-ghazzali' ),
			'seo'      => __( 'Référencement', 'al-ghazzali' ),
		);
		foreach ( $sections as $id => $title ) {
			$wp_customize->add_section(
				'ag_' . $id,
				array(
					'title' => $title,
					'panel' => self::PANEL,
				)
			);
		}

		foreach ( Institute::settings() as $key => $setting ) {
			$this->add_setting( $wp_customize, $key, $setting );
		}
	}

	/**
	 * @param array{section: string, label: string, type: string, default?: string} $setting Définition.
	 */
	private function add_setting( \WP_Customize_Manager $wp_customize, string $key, array $setting ): void {
		$id = Institute::PREFIX . $key;

		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $setting['default'] ?? '',
				'sanitize_callback' => $this->sanitizer( $setting['type'] ),
				'transport'         => 'refresh',
			)
		);

		$control = array(
			'label'   => $setting['label'],
			'section' => 'ag_' . $setting['section'],
		);

		if ( 'image' === $setting['type'] ) {
			$wp_customize->add_control(
				new \WP_Customize_Media_Control(
					$wp_customize,
					$id,
					$control + array( 'mime_type' => 'image' )
				)
			);
			return;
		}

		$type_map = array(
			'page'     => 'dropdown-pages',
			'textarea' => 'textarea',
			'email'    => 'email',
			'url'      => 'url',
		);
		$wp_customize->add_control( $id, $control + array( 'type' => $type_map[ $setting['type'] ] ?? 'text' ) );
	}

	private function sanitizer( string $type ): callable {
		return match ( $type ) {
			'email'    => 'sanitize_email',
			'url'      => 'esc_url_raw',
			'textarea' => 'sanitize_textarea_field',
			'image', 'page' => 'absint',
			default    => 'sanitize_text_field',
		};
	}
}
