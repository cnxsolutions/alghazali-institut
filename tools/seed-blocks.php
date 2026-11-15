<?php
/**
 * Générateurs de balisage de blocs Gutenberg (identique à celui de l'éditeur, pour éviter
 * les avertissements « contenu inattendu »).
 *
 * @package AlGhazzali
 */

function ag_b_p( string $html ): string {
	return "<!-- wp:paragraph -->\n<p>{$html}</p>\n<!-- /wp:paragraph -->\n\n";
}

function ag_b_h2( string $text ): string {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$text}</h2>\n<!-- /wp:heading -->\n\n";
}

function ag_b_h3( string $text ): string {
	return "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">{$text}</h3>\n<!-- /wp:heading -->\n\n";
}

/**
 * @param string[] $items Éléments (HTML en ligne autorisé).
 */
function ag_b_list( array $items, bool $ordered = false ): string {
	$tag   = $ordered ? 'ol' : 'ul';
	$attrs = $ordered ? ' {"ordered":true}' : '';
	$lis   = '';
	foreach ( $items as $item ) {
		$lis .= "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->";
	}
	return "<!-- wp:list{$attrs} -->\n<{$tag} class=\"wp-block-list\">{$lis}</{$tag}>\n<!-- /wp:list -->\n\n";
}

/**
 * FAQ : un bloc « Détails » par question (repris dans le JSON-LD FAQPage).
 *
 * @param array<string, string> $entries Question => réponse.
 */
function ag_b_faq( array $entries ): string {
	$html = '';
	foreach ( $entries as $question => $answer ) {
		$html .= "<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>{$question}</summary>"
			. "<!-- wp:paragraph -->\n<p>{$answer}</p>\n<!-- /wp:paragraph --></details>\n<!-- /wp:details -->\n\n";
	}
	return $html;
}

/**
 * Encadré « à retenir » (bloc Groupe, classe .callout).
 */
function ag_b_callout( string $inner_blocks ): string {
	return "<!-- wp:group {\"className\":\"callout\",\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group callout\">"
		. trim( $inner_blocks ) . "</div>\n<!-- /wp:group -->\n\n";
}

/**
 * Texte arabe en ligne, balisé pour la langue et le sens de lecture.
 */
function ag_b_ar( string $text ): string {
	return "<span class=\"ar\" lang=\"ar\" dir=\"rtl\">{$text}</span>";
}
