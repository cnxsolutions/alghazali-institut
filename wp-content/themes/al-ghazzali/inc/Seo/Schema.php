<?php
/**
 * Données structurées JSON-LD : un seul @graph relié par des @id.
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Seo;

use AlGhazzali\Core\Content\Course;
use AlGhazzali\Core\Content\Events;
use AlGhazzali\Core\Content\Fields;
use AlGhazzali\Core\Content\PostTypes;
use AlGhazzali\Hookable;
use AlGhazzali\Settings\Institute;

defined( 'ABSPATH' ) || exit;

final class Schema implements Hookable {

	private const COURSE_MODES = array(
		'online' => 'Online',
		'onsite' => 'Onsite',
		'hybrid' => 'Blended',
	);

	/**
	 * @param bool $standalone Faux si une extension SEO publie déjà Organisation, Site et Fil d'Ariane.
	 */
	public function __construct(
		private readonly Context $context,
		private readonly Breadcrumbs $breadcrumbs,
		private readonly Institute $institute,
		private readonly bool $standalone,
	) {}

	public function register(): void {
		add_action( 'wp_head', array( $this, 'output' ), 20 );
	}

	public function output(): void {
		$nodes = $this->standalone
			? array( $this->organization(), $this->website(), $this->webpage(), $this->breadcrumb_list() )
			: array();

		$nodes[] = $this->main_entity();
		$nodes[] = $this->faq();

		$graph = array_values( array_filter( $nodes ) );
		if ( array() === $graph ) {
			return;
		}

		$json = wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
		);

		if ( false !== $json ) {
			echo '<script type="application/ld+json">' . $json . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON encodé, balises neutralisées par JSON_HEX_TAG.
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	private function organization(): array {
		$node = array(
			'@type'         => 'EducationalOrganization',
			'@id'           => self::id( 'organization' ),
			'name'          => get_bloginfo( 'name' ),
			'url'           => home_url( '/' ),
			'description'   => $this->institute->get( 'default_description' ),
			'logo'          => array(
				'@type' => 'ImageObject',
				'url'   => $this->logo_url(),
			),
			'knowsLanguage' => array( 'fr', 'ar' ),
		);

		$email = $this->institute->get( 'email' );
		if ( '' !== $email ) {
			$node['email'] = $email;
		}
		$phone = $this->institute->get( 'phone' );
		if ( '' !== $phone ) {
			$node['telephone'] = $phone;
		}
		if ( $this->institute->has_address() ) {
			$node['address'] = array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $this->institute->get( 'street' ),
				'postalCode'      => $this->institute->get( 'postal_code' ),
				'addressLocality' => $this->institute->get( 'city' ),
				'addressCountry'  => 'FR',
			);
		}
		$same_as = array_values( $this->institute->socials() );
		if ( array() !== $same_as ) {
			$node['sameAs'] = $same_as;
		}
		return $node;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function website(): array {
		return array(
			'@type'       => 'WebSite',
			'@id'         => self::id( 'website' ),
			'url'         => home_url( '/' ),
			'name'        => get_bloginfo( 'name' ),
			'description' => get_bloginfo( 'description' ),
			'inLanguage'  => 'fr-FR',
			'publisher'   => array( '@id' => self::id( 'organization' ) ),
		);
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function webpage(): ?array {
		$url = $this->context->canonical();
		if ( '' === $url ) {
			return null;
		}

		$type = 'WebPage';
		if ( is_post_type_archive() || is_home() || is_category() || is_tax() ) {
			$type = 'CollectionPage';
		} elseif ( is_page_template( 'page-templates/contact.php' ) ) {
			$type = 'ContactPage';
		}

		$node = array(
			'@type'              => $type,
			'@id'                => $url . '#webpage',
			'url'                => $url,
			'name'               => $this->context->title(),
			'description'        => $this->context->description(),
			'isPartOf'           => array( '@id' => self::id( 'website' ) ),
			'inLanguage'         => 'fr-FR',
			'primaryImageOfPage' => array(
				'@type' => 'ImageObject',
				'url'   => $this->context->image()['url'],
			),
		);

		if ( is_front_page() ) {
			$node['about'] = array( '@id' => self::id( 'organization' ) );
		} else {
			$node['breadcrumb'] = array( '@id' => $url . '#breadcrumb' );
		}
		if ( is_singular() ) {
			$node['datePublished'] = (string) get_the_date( 'c' );
			$node['dateModified']  = (string) get_the_modified_date( 'c' );
		}
		return $node;
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function breadcrumb_list(): ?array {
		$items = $this->breadcrumbs->items();
		$url   = $this->context->canonical();
		if ( count( $items ) < 2 || '' === $url ) {
			return null;
		}

		$elements = array();
		foreach ( $items as $index => $item ) {
			$element = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => $item['name'],
			);
			if ( '' !== $item['url'] ) {
				$element['item'] = $item['url'];
			}
			$elements[] = $element;
		}

		return array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $url . '#breadcrumb',
			'itemListElement' => $elements,
		);
	}

	/**
	 * Entité principale de la page : formation, professeur, événement ou article.
	 *
	 * @return array<string, mixed>|null
	 */
	private function main_entity(): ?array {
		if ( ! is_singular() ) {
			return null;
		}

		$post_id = (int) get_queried_object_id();
		return match ( get_post_type( $post_id ) ) {
			PostTypes::COURSE  => $this->course( $post_id ),
			PostTypes::TEACHER => $this->person( $post_id ),
			PostTypes::EVENT   => $this->event( $post_id ),
			'post'             => $this->article( $post_id ),
			default            => null,
		};
	}

	/**
	 * @return array<string, mixed>
	 */
	private function course( int $post_id ): array {
		$url      = (string) get_permalink( $post_id );
		$format   = (string) get_post_meta( $post_id, Fields::FORMAT, true );
		$start    = (string) get_post_meta( $post_id, Fields::START, true );
		$instance = array(
			'@type'      => 'CourseInstance',
			'courseMode' => self::COURSE_MODES[ $format ] ?? 'Online',
			'inLanguage' => 'fr',
		);
		if ( '' !== $start ) {
			$instance['startDate'] = $start;
		}

		$node = array(
			'@type'             => 'Course',
			'@id'               => $url . '#course',
			'url'               => $url,
			'name'              => get_the_title( $post_id ),
			'description'       => $this->context->description(),
			'inLanguage'        => 'fr',
			'about'             => array(
				'@type' => 'Thing',
				'name'  => __( 'Langue arabe', 'al-ghazzali' ),
			),
			'provider'          => $this->provider(),
			'image'             => $this->context->image()['url'],
			'hasCourseInstance' => $instance,
		);

		$levels = array_column( Course::levels( $post_id ), 'name' );
		if ( array() !== $levels ) {
			$node['educationalLevel'] = implode( ', ', $levels );
		}
		$publics = Course::publics( $post_id );
		if ( array() !== $publics ) {
			$node['audience'] = array(
				'@type'        => 'EducationalAudience',
				/* translators: %s: liste des classes (Hommes, Femmes). */
				'audienceType' => sprintf( __( 'Adultes, classes séparées : %s', 'al-ghazzali' ), implode( ', ', $publics ) ),
			);
		}
		return $node;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function person( int $post_id ): array {
		$url  = (string) get_permalink( $post_id );
		$node = array(
			'@type'       => 'Person',
			'@id'         => $url . '#person',
			'url'         => $url,
			'name'        => get_the_title( $post_id ),
			'description' => $this->context->description(),
			'worksFor'    => $this->provider(),
			'knowsAbout'  => __( 'Langue arabe', 'al-ghazzali' ),
		);

		$role = (string) get_post_meta( $post_id, Fields::ROLE, true );
		if ( '' !== $role ) {
			$node['jobTitle'] = $role;
		}
		if ( has_post_thumbnail( $post_id ) ) {
			$node['image'] = (string) get_the_post_thumbnail_url( $post_id, 'ag-portrait' );
		}
		return $node;
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function event( int $post_id ): ?array {
		$start = Events::iso( $post_id, Fields::EVENT_START );
		if ( '' === $start ) {
			return null;
		}

		$url    = (string) get_permalink( $post_id );
		$online = 'online' === get_post_meta( $post_id, Fields::EVENT_MODE, true );
		$place  = (string) get_post_meta( $post_id, Fields::EVENT_PLACE, true );
		$link   = (string) get_post_meta( $post_id, Fields::EVENT_URL, true );

		$node = array(
			'@type'               => 'Event',
			'@id'                 => $url . '#event',
			'url'                 => $url,
			'name'                => get_the_title( $post_id ),
			'description'         => $this->context->description(),
			'startDate'           => $start,
			'eventStatus'         => 'https://schema.org/EventScheduled',
			'eventAttendanceMode' => $online ? 'https://schema.org/OnlineEventAttendanceMode' : 'https://schema.org/OfflineEventAttendanceMode',
			'organizer'           => $this->provider(),
			'image'               => $this->context->image()['url'],
			'location'            => $online
				? array(
					'@type' => 'VirtualLocation',
					'url'   => '' !== $link ? $link : $url,
				)
				: array(
					'@type'   => 'Place',
					'name'    => '' !== $place ? $place : get_bloginfo( 'name' ),
					'address' => '' !== $place ? $place : $this->institute->get( 'city' ),
				),
		);

		$end = Events::iso( $post_id, Fields::EVENT_END );
		if ( '' !== $end ) {
			$node['endDate'] = $end;
		}
		return $node;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function article( int $post_id ): array {
		$url = (string) get_permalink( $post_id );
		return array(
			'@type'            => 'BlogPosting',
			'@id'              => $url . '#article',
			'headline'         => get_the_title( $post_id ),
			'description'      => $this->context->description(),
			'datePublished'    => (string) get_the_date( 'c', $post_id ),
			'dateModified'     => (string) get_the_modified_date( 'c', $post_id ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ),
			),
			'publisher'        => $this->provider(),
			'image'            => $this->context->image()['url'],
			'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
			'inLanguage'       => 'fr-FR',
		);
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function faq(): ?array {
		$post_id = $this->context->post_id();
		if ( 0 === $post_id ) {
			return null;
		}

		// Fiche formation : la zone « Questions fréquentes » s'ajoute aux blocs Détails du contenu.
		$entries = array_merge(
			PostTypes::COURSE === get_post_type( $post_id ) ? Course::faq( $post_id ) : array(),
			Faq::from_content( (string) get_post_field( 'post_content', $post_id ) )
		);
		if ( count( $entries ) < 2 ) {
			return null;
		}

		$questions = array();
		foreach ( $entries as $entry ) {
			$questions[] = array(
				'@type'          => 'Question',
				'name'           => $entry['question'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $entry['answer'],
				),
			);
		}

		return array(
			'@type'      => 'FAQPage',
			'@id'        => $this->context->canonical() . '#faq',
			'mainEntity' => $questions,
		);
	}

	/**
	 * Référence à l'organisation : par @id si elle est publiée par le thème, sinon par nom.
	 *
	 * @return array<string, string>
	 */
	private function provider(): array {
		return $this->standalone
			? array( '@id' => self::id( 'organization' ) )
			: array(
				'@type' => 'EducationalOrganization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			);
	}

	private function logo_url(): string {
		$logo_id = (int) get_theme_mod( 'custom_logo' );
		$url     = $logo_id > 0 ? wp_get_attachment_image_url( $logo_id, 'full' ) : false;
		return $url ? $url : AG_THEME_URI . '/assets/img/logo.png';
	}

	private static function id( string $fragment ): string {
		return home_url( '/#' . $fragment );
	}
}
