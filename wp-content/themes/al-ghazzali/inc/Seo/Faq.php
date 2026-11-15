<?php
/**
 * Extrait les questions/réponses des blocs « Détails » d'un contenu (FAQ éditable dans l'éditeur).
 *
 * @package AlGhazzali
 */

namespace AlGhazzali\Seo;

defined( 'ABSPATH' ) || exit;

final class Faq {

	/**
	 * @return list<array{question: string, answer: string}>
	 */
	public static function from_content( string $content ): array {
		if ( ! has_block( 'core/details', $content ) ) {
			return array();
		}
		return self::collect( parse_blocks( $content ) );
	}

	/**
	 * @param array<int, array<string, mixed>> $blocks Blocs analysés.
	 * @return list<array{question: string, answer: string}>
	 */
	private static function collect( array $blocks ): array {
		$entries = array();

		foreach ( $blocks as $block ) {
			if ( 'core/details' === $block['blockName'] ) {
				$question = preg_match( '#<summary[^>]*>(.*?)</summary>#is', (string) $block['innerHTML'], $match ) ? self::text( $match[1] ) : '';
				$answer   = self::text( serialize_blocks( $block['innerBlocks'] ) );

				if ( '' !== $question && '' !== $answer ) {
					$entries[] = array(
						'question' => $question,
						'answer'   => $answer,
					);
				}
				continue;
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$entries = array_merge( $entries, self::collect( $block['innerBlocks'] ) );
			}
		}
		return $entries;
	}

	private static function text( string $html ): string {
		return trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $html ) ) );
	}
}
