<?php
/**
 * Accueil : la méthode pour apprendre l'arabe facilement, en 4 étapes.
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

$ag_steps = array(
	array( __( 'Un bilan pour bien commencer', 'al-ghazzali' ), __( 'Un échange pour connaître votre niveau et vos objectifs, puis vous orienter vers le bon groupe.', 'al-ghazzali' ) ),
	array( __( 'L’alphabet et la lecture', 'al-ghazzali' ), __( 'Les 28 lettres, les voyelles et les règles de lecture, pas à pas, jusqu’à lire avec assurance.', 'al-ghazzali' ) ),
	array( __( 'Vocabulaire et grammaire vivante', 'al-ghazzali' ), __( 'Des leçons courtes, des exercices réguliers et de l’oral dès les premières semaines.', 'al-ghazzali' ) ),
	array( __( 'Comprendre et s’exprimer', 'al-ghazzali' ), __( 'Lire des textes authentiques, comprendre l’arabe écrit et oral, et échanger avec aisance.', 'al-ghazzali' ) ),
);

$ag_more_link = (string) get_post_type_archive_link( PostTypes::COURSE );
?>
<section class="section method" aria-labelledby="method-title">
	<div class="container method__grid">
		<div class="method__head" data-reveal>
			<p class="kicker"><?php esc_html_e( 'La méthode', 'al-ghazzali' ); ?></p>
			<h2 class="section-title" id="method-title"><?php echo ag_highlight( __( 'Apprendre l’arabe *facilement*, étape par étape', 'al-ghazzali' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
			<p class="section-intro"><?php esc_html_e( 'Pas de raccourci magique, mais une progression claire, des objectifs atteignables et un professeur qui vous accompagne à chaque étape.', 'al-ghazzali' ); ?></p>
			<?php if ( '' !== $ag_more_link ) : ?>
				<a class="link-arrow" href="<?php echo esc_url( $ag_more_link ); ?>"><?php esc_html_e( 'Découvrir les niveaux', 'al-ghazzali' ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			<?php endif; ?>
		</div>

		<ol class="steps" data-steps data-stagger>
			<?php foreach ( $ag_steps as $ag_index => list( $ag_title, $ag_text ) ) : ?>
				<li class="step" data-reveal>
					<span class="step__number" aria-hidden="true"><?php echo esc_html( (string) ( $ag_index + 1 ) ); ?></span>
					<div>
						<h3 class="step__title"><?php echo esc_html( $ag_title ); ?></h3>
						<p><?php echo esc_html( $ag_text ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
