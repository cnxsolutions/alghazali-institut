<?php
/**
 * Informations de l'institut saisies dans le Customizer (coordonnées, réseaux, plateforme).
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Settings;

defined( 'ABSPATH' ) || exit;

final class Institute {

	public const PREFIX = 'ag_';

	/**
	 * Définition des réglages : clé => [section, libellé, type].
	 *
	 * @return array<string, array{section: string, label: string, type: string, default?: string}>
	 */
	public static function settings(): array {
		return array(
			'email'               => array(
				'section' => 'contact',
				'label'   => __( 'E-mail', 'al-ghazzali' ),
				'type'    => 'email',
			),
			'phone'               => array(
				'section' => 'contact',
				'label'   => __( 'Téléphone', 'al-ghazzali' ),
				'type'    => 'text',
			),
			'whatsapp'            => array(
				'section' => 'contact',
				'label'   => __( 'Numéro WhatsApp (format international, ex. 33612345678)', 'al-ghazzali' ),
				'type'    => 'text',
			),
			'street'              => array(
				'section' => 'contact',
				'label'   => __( 'Adresse', 'al-ghazzali' ),
				'type'    => 'text',
			),
			'postal_code'         => array(
				'section' => 'contact',
				'label'   => __( 'Code postal', 'al-ghazzali' ),
				'type'    => 'text',
			),
			'city'                => array(
				'section' => 'contact',
				'label'   => __( 'Ville', 'al-ghazzali' ),
				'type'    => 'text',
			),
			'hours'               => array(
				'section' => 'contact',
				'label'   => __( 'Horaires d’accueil', 'al-ghazzali' ),
				'type'    => 'textarea',
			),
			'instagram'           => array(
				'section' => 'social',
				'label'   => 'Instagram',
				'type'    => 'url',
			),
			'facebook'            => array(
				'section' => 'social',
				'label'   => 'Facebook',
				'type'    => 'url',
			),
			'youtube'             => array(
				'section' => 'social',
				'label'   => 'YouTube',
				'type'    => 'url',
			),
			'tiktok'              => array(
				'section' => 'social',
				'label'   => 'TikTok',
				'type'    => 'url',
			),
			'telegram'            => array(
				'section' => 'social',
				'label'   => 'Telegram',
				'type'    => 'url',
			),
			'enroll_url'          => array(
				'section' => 'platform',
				'label'   => __( 'Lien « S’inscrire » (plateforme d’inscription)', 'al-ghazzali' ),
				'type'    => 'url',
			),
			'account_url'         => array(
				'section' => 'platform',
				'label'   => __( 'Lien « Mon espace » (espace élève)', 'al-ghazzali' ),
				'type'    => 'url',
			),
			'hero_kicker'         => array(
				'section' => 'home',
				'label'   => __( 'Surtitre', 'al-ghazzali' ),
				'type'    => 'text',
				'default' => __( 'Institut de langue arabe', 'al-ghazzali' ),
			),
			'hero_title'          => array(
				'section' => 'home',
				'label'   => __( 'Titre principal (H1). Entourez un mot de *astérisques* pour le mettre en valeur.', 'al-ghazzali' ),
				'type'    => 'text',
				'default' => __( 'Institut arabe en ligne : apprenez l’arabe *facilement*', 'al-ghazzali' ),
			),
			'hero_text'           => array(
				'section' => 'home',
				'label'   => __( 'Texte d’introduction', 'al-ghazzali' ),
				'type'    => 'textarea',
				'default' => __( 'Des cours d’arabe en ligne, en direct et en petits groupes : un parcours par niveaux, du débutant à l’avancé, avec des classes séparées pour les hommes et pour les femmes.', 'al-ghazzali' ),
			),
			'default_description' => array(
				'section' => 'seo',
				'label'   => __( 'Meta description par défaut', 'al-ghazzali' ),
				'type'    => 'textarea',
				'default' => __( 'Institut arabe en ligne : cours d’arabe par niveaux, en direct, avec des classes séparées pour hommes et femmes. Apprenez l’arabe facilement, à votre rythme.', 'al-ghazzali' ),
			),
			'og_image'            => array(
				'section' => 'seo',
				'label'   => __( 'Image de partage par défaut (1200 × 630)', 'al-ghazzali' ),
				'type'    => 'image',
			),
			'contact_page'        => array(
				'section' => 'platform',
				'label'   => __( 'Page de contact (cible de « S’inscrire » tant que la plateforme n’est pas reliée)', 'al-ghazzali' ),
				'type'    => 'page',
			),
		);
	}

	public function get( string $key ): string {
		static $defaults = null;
		$defaults ??= array_map( static fn( array $setting ): string => $setting['default'] ?? '', self::settings() );

		return trim( (string) get_theme_mod( self::PREFIX . $key, $defaults[ $key ] ?? '' ) );
	}

	public function contact_url(): string {
		$page_id = (int) $this->get( 'contact_page' );
		$url     = $page_id > 0 ? get_permalink( $page_id ) : false;
		return $url ? $url : home_url( '/contact/' );
	}

	/**
	 * Lien « S'inscrire » : la plateforme si elle est reliée, sinon la page de contact.
	 */
	public function enroll_url(): string {
		$url = $this->get( 'enroll_url' );
		return '' !== $url ? $url : $this->contact_url();
	}

	public function enroll_is_external(): bool {
		return '' !== $this->get( 'enroll_url' );
	}

	public function whatsapp_url(): string {
		$digits = preg_replace( '/\D+/', '', $this->get( 'whatsapp' ) );
		return '' !== $digits ? 'https://wa.me/' . $digits : '';
	}

	public function phone_href(): string {
		$phone = preg_replace( '/[^\d+]/', '', $this->get( 'phone' ) );
		return '' !== $phone ? 'tel:' . $phone : '';
	}

	/**
	 * @return array<string, string> Réseau => URL, seulement ceux renseignés.
	 */
	public function socials(): array {
		$socials = array();
		foreach ( array( 'instagram', 'facebook', 'youtube', 'tiktok', 'telegram' ) as $network ) {
			$url = $this->get( $network );
			if ( '' !== $url ) {
				$socials[ $network ] = $url;
			}
		}
		return $socials;
	}

	public function has_address(): bool {
		return '' !== $this->get( 'street' ) && '' !== $this->get( 'city' );
	}
}
