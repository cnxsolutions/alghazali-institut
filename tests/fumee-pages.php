<?php
/**
 * Rendu de chaque contenu publié (pages, formations, articles), par le serveur web.
 *
 * Usage : wp eval-file tests/fumee-pages.php
 *
 * Lancé par tests/fumee.sh dans l'image, derrière nginx (le script est envoyé
 * dans le conteneur : cat > /tmp, puis wp eval-file), juste après
 * l'installation du contenu de départ. Sur chaque contenu :
 *   - réponse 200, sans redirection ;
 *   - une balise canonique égale à l'adresse du contenu ;
 *   - un seul H1 ;
 *   - un JSON-LD présent et lisible, avec son @graph ;
 *   - aucune erreur PHP affichée dans la page ;
 *   - le script du thème versionné (?ver=), pour que le cache de 7 jours de
 *     nginx ne serve jamais une version périmée.
 *
 * Code de sortie : 0 tout est conforme, 1 au moins un contenu en échec.
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

// Sortie console en texte brut, jamais affichée dans une page : rien à échapper.
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

$ag_echecs   = 0;
$ag_contenus = get_posts(
	array(
		'post_type'      => array( 'page', 'ag_course', 'post' ),
		'post_status'    => 'publish',
		'posts_per_page' => 100, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- test, sur le contenu de départ.
		'orderby'        => 'ID',
		'order'          => 'ASC',
	)
);

if ( array() === $ag_contenus ) {
	WP_CLI::error( 'aucun contenu publié : le contenu de départ manque' );
}

foreach ( $ag_contenus as $ag_contenu ) {
	$ag_url     = (string) get_permalink( $ag_contenu );
	$ag_reponse = wp_remote_get(
		$ag_url,
		array(
			'redirection' => 0,
			'timeout'     => 20,
		)
	);
	$ag_ecarts  = array();

	if ( is_wp_error( $ag_reponse ) ) {
		echo "ÉCHEC  $ag_url : " . $ag_reponse->get_error_message() . "\n";
		++$ag_echecs;
		continue;
	}

	$ag_code  = (int) wp_remote_retrieve_response_code( $ag_reponse );
	$ag_corps = (string) wp_remote_retrieve_body( $ag_reponse );

	if ( 200 !== $ag_code ) {
		$ag_ecarts[] = "code $ag_code";
	}

	if ( ! preg_match( '#<link rel="canonical" href="([^"]+)"#', $ag_corps, $ag_canonique ) ) {
		$ag_ecarts[] = 'pas de canonique';
	} elseif ( html_entity_decode( $ag_canonique[1] ) !== $ag_url ) {
		$ag_ecarts[] = 'canonique ' . $ag_canonique[1];
	}

	$ag_h1 = preg_match_all( '#<h1[\s>]#', $ag_corps );
	if ( 1 !== $ag_h1 ) {
		$ag_ecarts[] = "$ag_h1 H1";
	}

	if ( ! preg_match( '#<script type="application/ld\+json">(.*?)</script>#s', $ag_corps, $ag_ld ) ) {
		$ag_ecarts[] = 'pas de JSON-LD';
	} else {
		$ag_graphe = json_decode( $ag_ld[1], true );
		if ( ! is_array( $ag_graphe ) || empty( $ag_graphe['@graph'] ) ) {
			$ag_ecarts[] = 'JSON-LD illisible (' . json_last_error_msg() . ')';
		}
	}

	if ( preg_match( '#<b>(Fatal error|Warning|Notice|Deprecated)</b>|(Fatal error|Parse error): #', $ag_corps, $ag_erreur ) ) {
		$ag_ecarts[] = 'erreur PHP dans la page : ' . $ag_erreur[0];
	}

	if ( ! preg_match( '#/assets/js/app\.js\?ver=[0-9A-Za-z.]+#', $ag_corps ) ) {
		$ag_ecarts[] = 'app.js sans version';
	}

	if ( array() === $ag_ecarts ) {
		echo "   ok  $ag_url\n";
	} else {
		echo "ÉCHEC  $ag_url : " . implode( ' ; ', $ag_ecarts ) . "\n";
		++$ag_echecs;
	}
}

if ( 0 !== $ag_echecs ) {
	WP_CLI::error( "$ag_echecs contenu(s) en échec" );
}

WP_CLI::success( count( $ag_contenus ) . ' contenus conformes' );
