<?php
/**
 * Accueil : présentation de l'institut, atouts et chiffres (calculés depuis le contenu réel).
 *
 * @package AlGhazzali
 *
 * Arguments : week (créneaux de la semaine, voir Schedule::weekly()).
 */

use AlGhazzali\Core\Content\Course;

defined( 'ABSPATH' ) || exit;

$ag_courses = ag_courses( array( 'limit' => 100 ) );
$ag_levels  = 0;
foreach ( $ag_courses as $ag_course ) {
	$ag_levels += count( Course::levels( $ag_course->ID ) );
}
$ag_week = is_array( $args['week'] ?? null ) ? $args['week'] : array();

$ag_stats = array_filter(
	array(
		__( 'formations', 'al-ghazzali' )           => count( $ag_courses ),
		__( 'niveaux', 'al-ghazzali' )              => $ag_levels,
		__( 'créneaux par semaine', 'al-ghazzali' ) => count( array_merge( ...array_values( $ag_week ) ) ),
	)
);

$ag_features = array(
	array( 'cap', __( 'Enseignants qualifiés', 'al-ghazzali' ), __( 'Des enseignants formés à la pédagogie de l’arabe pour les francophones, encadrés par une direction pédagogique.', 'al-ghazzali' ) ),
	array( 'users', __( 'Petits groupes en direct', 'al-ghazzali' ), __( 'Des classes à effectif réduit pour prendre la parole à chaque séance et progresser réellement.', 'al-ghazzali' ) ),
	array( 'level', __( 'Un parcours par niveaux', 'al-ghazzali' ), __( 'Des objectifs clairs à chaque étape, de la première lettre à la lecture de textes authentiques.', 'al-ghazzali' ) ),
	array( 'user', __( 'Classes hommes et femmes', 'al-ghazzali' ), __( 'Chaque niveau ouvre des groupes séparés, à des horaires adaptés à chacun.', 'al-ghazzali' ) ),
);
?>
<section class="section intro" id="decouvrir">
	<div class="container intro__grid">
		<div class="intro__text" data-reveal>
			<p class="kicker"><?php esc_html_e( 'Notre institut', 'al-ghazzali' ); ?></p>
			<h2 class="section-title"><?php echo ag_highlight( __( 'Un institut arabe qui rend l’apprentissage *simple et vivant*', 'al-ghazzali' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
			<p><?php esc_html_e( 'Apprendre l’arabe ne devrait pas être réservé à quelques-uns. Notre institut propose un parcours clair, du premier pas jusqu’au niveau avancé, avec des enseignants qui prennent le temps d’expliquer.', 'al-ghazzali' ); ?></p>
			<p><?php esc_html_e( 'Lecture, écriture, grammaire, compréhension et expression orale : chaque élève avance à son rythme, dans un groupe de son niveau, en classe séparée pour les hommes et pour les femmes.', 'al-ghazzali' ); ?></p>

			<?php if ( array() !== $ag_stats ) : ?>
				<dl class="stats">
					<?php foreach ( $ag_stats as $ag_label => $ag_value ) : ?>
						<div class="stats__item">
							<dt><?php echo esc_html( $ag_label ); ?></dt>
							<dd data-count="<?php echo esc_attr( (string) $ag_value ); ?>"><?php echo esc_html( (string) $ag_value ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
		</div>

		<ul class="feature-grid" data-stagger>
			<?php foreach ( $ag_features as list( $ag_icon, $ag_title, $ag_text ) ) : ?>
				<li class="feature-card" data-reveal data-spotlight>
					<span class="feature-card__icon"><?php echo ag_icon( $ag_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h3 class="feature-card__title"><?php echo esc_html( $ag_title ); ?></h3>
					<p><?php echo esc_html( $ag_text ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
