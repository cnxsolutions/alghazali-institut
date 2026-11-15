<?php
/**
 * Contenu de départ LOCAL : réglages, équipe, formations, pages, articles, menus.
 *
 * Usage : php tools/seed.php [hôte] [--force]
 * Sans --force, un contenu existant (même slug) n'est jamais écrasé.
 * Active l'extension « Al Ghazzali Institut » si besoin et retire les contenus des versions précédentes.
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\Fields;
use AlGhazzali\Core\Content\PostTypes;
use AlGhazzali\Core\Content\Taxonomies;
use AlGhazzali\Core\Plugin;

if ( 'cli' !== PHP_SAPI ) {
	exit( 1 );
}

$ag_args  = array_slice( $argv, 1 );
$ag_force = in_array( '--force', $ag_args, true );
$ag_host  = current( array_diff( $ag_args, array( '--force' ) ) );

$_SERVER['HTTP_HOST']   = $ag_host ? $ag_host : 'alghazzali.localhost';
$_SERVER['REQUEST_URI'] = '/';

require dirname( __DIR__ ) . '/wp-load.php';

if ( 'local' !== wp_get_environment_type() ) {
	fwrite( STDERR, "Refusé : WP_ENVIRONMENT_TYPE doit valoir « local ».\n" );
	exit( 1 );
}

if ( ! defined( 'AG_CORE_VERSION' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	$ag_activated = activate_plugin( 'al-ghazzali-institut/al-ghazzali-institut.php' );
	if ( is_wp_error( $ag_activated ) ) {
		fwrite( STDERR, "Activation de l'extension impossible : " . $ag_activated->get_error_message() . "\n" );
		exit( 1 );
	}
	// « init » est déjà passé dans ce processus : types de contenu et groupes enregistrés à la main.
	Plugin::activate();
	echo "Extension « Al Ghazzali Institut » activée\n";
}

// Administrateur courant : nécessaire pour enregistrer le balisage des blocs (lang, dir) sans filtrage.
$ag_admins = get_users(
	array(
		'role'   => 'administrator',
		'number' => 1,
		'fields' => 'ID',
	)
);
wp_set_current_user( (int) ( $ag_admins[0] ?? 0 ) );

// Nom public des articles : jamais l'identifiant de connexion.
wp_update_user(
	array(
		'ID'           => (int) ( $ag_admins[0] ?? 0 ),
		'display_name' => 'Équipe Al Ghazzali',
		'nickname'     => 'Équipe Al Ghazzali',
	)
);

require __DIR__ . '/seed-blocks.php';
$ag_content = require __DIR__ . '/seed-content.php';

/**
 * Crée (ou met à jour avec --force) un contenu identifié par son slug. Retourne son ID.
 *
 * @param array<string, mixed> $data Définition.
 */
function ag_seed_post( string $type, string $slug, array $data, bool $force ): int {
	$existing = get_page_by_path( $slug, OBJECT, $type );
	if ( $existing instanceof WP_Post && ! $force ) {
		echo "  = {$type} {$slug} (existe déjà)\n";
		return $existing->ID;
	}

	$postarr = array(
		'post_type'    => $type,
		'post_name'    => $slug,
		'post_title'   => $data['title'],
		'post_content' => $data['content'] ?? '',
		'post_excerpt' => $data['excerpt'] ?? '',
		'post_status'  => $data['status'] ?? 'publish',
		'menu_order'   => $data['order'] ?? 0,
	);
	if ( $existing instanceof WP_Post ) {
		$postarr['ID'] = $existing->ID;
	}

	$post_id = wp_insert_post( wp_slash( $postarr ), true );
	if ( is_wp_error( $post_id ) ) {
		fwrite( STDERR, "Erreur sur {$type} {$slug} : " . $post_id->get_error_message() . "\n" );
		exit( 1 );
	}

	foreach ( $data['meta'] ?? array() as $key => $value ) {
		update_post_meta( $post_id, $key, wp_slash( $value ) );
	}
	if ( isset( $data['template'] ) ) {
		update_post_meta( $post_id, '_wp_page_template', $data['template'] );
	}
	foreach ( $data['terms'] ?? array() as $taxonomy => $slugs ) {
		wp_set_object_terms( $post_id, $slugs, $taxonomy );
	}

	echo '  + ' . ( $existing ? 'mis à jour' : 'créé' ) . " {$type} {$slug}\n";
	return $post_id;
}

/**
 * @param array<string, mixed> $item Élément de menu (type, object, object_id, url).
 */
function ag_seed_menu_item( int $menu_id, string $title, array $item, int $parent = 0 ): int {
	$args = array(
		'menu-item-title'     => $title,
		'menu-item-status'    => 'publish',
		'menu-item-parent-id' => $parent,
		'menu-item-type'      => $item['type'],
	);
	if ( isset( $item['object'] ) ) {
		$args['menu-item-object'] = $item['object'];
	}
	if ( isset( $item['object_id'] ) ) {
		$args['menu-item-object-id'] = $item['object_id'];
	}

	$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
	return is_wp_error( $item_id ) ? 0 : (int) $item_id;
}

/**
 * Crée un menu à partir d'un arbre [titre, élément, enfants[]] et le retourne.
 *
 * @param array<int, array{0: string, 1: array<string, mixed>, 2?: array<int, mixed>}> $tree Arbre.
 */
function ag_seed_menu( string $name, array $tree, bool $force ): int {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu && ! $force ) {
		echo "  = menu {$name} (existe déjà)\n";
		return (int) $menu->term_id;
	}
	if ( $menu ) {
		wp_delete_nav_menu( $menu->term_id );
	}

	$menu_id = (int) wp_create_nav_menu( $name );
	foreach ( $tree as $node ) {
		$parent_id = ag_seed_menu_item( $menu_id, $node[0], $node[1] );
		foreach ( $node[2] ?? array() as $child ) {
			ag_seed_menu_item( $menu_id, $child[0], $child[1], $parent_id );
		}
	}
	echo "  + menu {$name}\n";
	return $menu_id;
}

echo "Réglages\n";
foreach ( $ag_content['options'] as $ag_option => $ag_value ) {
	update_option( $ag_option, $ag_value );
}

echo "Nettoyage du contenu d'exemple de WordPress\n";
foreach ( array( 'bonjour-tout-le-monde', 'hello-world' ) as $ag_slug ) {
	$ag_sample = get_page_by_path( $ag_slug, OBJECT, 'post' );
	if ( $ag_sample ) {
		wp_delete_post( $ag_sample->ID, true );
	}
}
foreach ( array( 'page-d-exemple', 'sample-page' ) as $ag_slug ) {
	$ag_sample = get_page_by_path( $ag_slug, OBJECT, 'page' );
	if ( $ag_sample ) {
		wp_delete_post( $ag_sample->ID, true );
	}
}
foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'draft', 'name' => 'politique-de-confidentialite', 'numberposts' => 1 ) ) as $ag_draft ) {
	wp_delete_post( $ag_draft->ID, true );
}

echo "Retrait des contenus des versions précédentes\n";
foreach ( $ag_content['obsolete'] as $ag_type => $ag_slugs ) {
	foreach ( $ag_slugs as $ag_slug ) {
		$ag_old = get_page_by_path( $ag_slug, OBJECT, $ag_type );
		if ( $ag_old instanceof WP_Post ) {
			wp_delete_post( $ag_old->ID, true );
			echo "  - {$ag_type} {$ag_slug}\n";
		}
	}
}
foreach ( $ag_content['obsolete_meta'] as $ag_key ) {
	delete_post_meta_by_key( $ag_key );
}
// Anciennes taxonomies (pôles, publics) : plus enregistrées, leurs termes sont supprimés directement.
foreach ( $ag_content['obsolete_tax'] as $ag_taxonomy ) {
	$wpdb->query( $wpdb->prepare( "DELETE tr FROM {$wpdb->term_relationships} tr INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tt.taxonomy = %s", $ag_taxonomy ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s", $ag_taxonomy ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}
$wpdb->query( "DELETE t FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id WHERE tt.term_id IS NULL" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
foreach ( $ag_content['obsolete_cats'] as $ag_slug ) {
	$ag_category = get_term_by( 'slug', $ag_slug, 'category' );
	if ( $ag_category ) {
		wp_delete_term( $ag_category->term_id, 'category' );
	}
}

echo "Termes\n";
$ag_terms = array();
foreach ( $ag_content['terms'] as $ag_taxonomy => $ag_list ) {
	foreach ( $ag_list as $ag_slug => list( $ag_name, $ag_description ) ) {
		$ag_term = get_term_by( 'slug', $ag_slug, $ag_taxonomy );
		if ( ! $ag_term ) {
			$ag_result = wp_insert_term( $ag_name, $ag_taxonomy, array( 'slug' => $ag_slug, 'description' => $ag_description ) );
			$ag_terms[ $ag_taxonomy ][ $ag_slug ] = is_wp_error( $ag_result ) ? 0 : (int) $ag_result['term_id'];
			echo "  + {$ag_taxonomy} {$ag_slug}\n";
			continue;
		}
		if ( $ag_force ) {
			wp_update_term( $ag_term->term_id, $ag_taxonomy, array( 'name' => $ag_name, 'description' => $ag_description ) );
		}
		$ag_terms[ $ag_taxonomy ][ $ag_slug ] = (int) $ag_term->term_id;
	}
}
update_option( 'default_category', $ag_terms['category']['apprendre-arabe'] );
$ag_uncategorized = get_term_by( 'slug', 'non-classe', 'category' ) ?: get_term_by( 'slug', 'uncategorized', 'category' );
if ( $ag_uncategorized ) {
	wp_delete_term( $ag_uncategorized->term_id, 'category' );
}

echo "Pages\n";
$ag_pages = array();
foreach ( $ag_content['pages'] as $ag_slug => $ag_page ) {
	$ag_pages[ $ag_slug ] = ag_seed_post( 'page', $ag_slug, $ag_page, $ag_force );
}

echo "Équipe (brouillons)\n";
$ag_team = array();
foreach ( $ag_content['teachers'] as $ag_slug => $ag_member ) {
	$ag_member['status'] = 'draft';
	$ag_member['terms']  = array( Taxonomies::TEAM_GROUP => array( $ag_member['group'] ) );
	$ag_team[ $ag_slug ] = ag_seed_post( PostTypes::TEACHER, $ag_slug, $ag_member, $ag_force );
}

echo "Formations\n";
$ag_courses = array();
foreach ( $ag_content['courses'] as $ag_slug => $ag_course ) {
	// Enseignant des créneaux : slug du contenu de départ => ID réel.
	$ag_course['meta'][ Fields::SLOTS ] = array_map(
		static fn( array $slot ): array => array( 'teacher' => (string) ( $ag_team[ $slot['teacher'] ] ?? '' ) ) + $slot,
		$ag_course['meta'][ Fields::SLOTS ] ?? array()
	);
	$ag_courses[ $ag_slug ] = ag_seed_post( PostTypes::COURSE, $ag_slug, $ag_course, $ag_force );
}

echo "Articles\n";
foreach ( $ag_content['posts'] as $ag_slug => $ag_post ) {
	$ag_post['terms'] = array( 'category' => array( $ag_post['category'] ) );
	ag_seed_post( 'post', $ag_slug, $ag_post, $ag_force );
}

echo "Événements (brouillons)\n";
foreach ( $ag_content['events'] as $ag_slug => $ag_event ) {
	$ag_event['status'] = 'draft';
	ag_seed_post( PostTypes::EVENT, $ag_slug, $ag_event, $ag_force );
}

echo "Lecture, confidentialité, réglages du thème\n";
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $ag_pages['accueil'] );
update_option( 'page_for_posts', $ag_pages['blog'] );
update_option( 'wp_page_for_privacy_policy', $ag_pages['politique-de-confidentialite'] );
set_theme_mod( 'ag_contact_page', $ag_pages['contact'] );

echo "Menus\n";
$ag_page    = static fn( string $slug ): array => array( 'type' => 'post_type', 'object' => 'page', 'object_id' => $ag_pages[ $slug ] );
$ag_course  = static fn( string $slug ): array => array( 'type' => 'post_type', 'object' => PostTypes::COURSE, 'object_id' => $ag_courses[ $slug ] );
$ag_archive = static fn( string $post_type ): array => array( 'type' => 'post_type_archive', 'object' => $post_type );

$ag_primary = ag_seed_menu(
	'Menu principal',
	array(
		array(
			'L’institut',
			$ag_page( 'institut' ),
			array(
				array( 'Notre institut', $ag_page( 'institut' ) ),
				array( 'Notre équipe', $ag_archive( PostTypes::TEACHER ) ),
				array( 'Événements', $ag_archive( PostTypes::EVENT ) ),
			),
		),
		array(
			'Formations',
			$ag_archive( PostTypes::COURSE ),
			array(
				array( 'Arabe littéraire', $ag_course( 'arabe-litteraire' ) ),
				array( 'Arabe oral & immersion', $ag_course( 'arabe-oral-immersion' ) ),
				array( 'Cours d’arabe en ligne', $ag_page( 'cours-arabe-en-ligne' ) ),
				array( 'Apprendre l’arabe facilement', $ag_page( 'apprendre-arabe-facilement' ) ),
				array( 'Toutes les formations', $ag_archive( PostTypes::COURSE ) ),
			),
		),
		array( 'Emploi du temps', $ag_page( 'emploi-du-temps' ) ),
		array( 'Blog', $ag_page( 'blog' ) ),
		array( 'Contact', $ag_page( 'contact' ) ),
	),
	$ag_force
);

$ag_footer = ag_seed_menu(
	'Pied de page',
	array(
		array( 'Notre institut', $ag_page( 'institut' ) ),
		array( 'Notre équipe', $ag_archive( PostTypes::TEACHER ) ),
		array( 'Emploi du temps', $ag_page( 'emploi-du-temps' ) ),
		array( 'Événements', $ag_archive( PostTypes::EVENT ) ),
		array( 'Blog', $ag_page( 'blog' ) ),
		array( 'Contact', $ag_page( 'contact' ) ),
	),
	$ag_force
);

$ag_legal = ag_seed_menu(
	'Liens légaux',
	array(
		array( 'Mentions légales', $ag_page( 'mentions-legales' ) ),
		array( 'Confidentialité', $ag_page( 'politique-de-confidentialite' ) ),
	),
	$ag_force
);

set_theme_mod(
	'nav_menu_locations',
	array(
		'primary' => $ag_primary,
		'footer'  => $ag_footer,
		'legal'   => $ag_legal,
	)
);

echo "Permaliens\n";
flush_rewrite_rules( false );

$ag_htaccess = ABSPATH . '.htaccess';
if ( ! file_exists( $ag_htaccess ) ) {
	file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- outil CLI local.
		$ag_htaccess,
		"# BEGIN WordPress\n<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\nRewriteBase /\nRewriteRule ^index\\.php$ - [L]\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule . /index.php [L]\n</IfModule>\n# END WordPress\n"
	);
}

echo "Terminé.\n";
