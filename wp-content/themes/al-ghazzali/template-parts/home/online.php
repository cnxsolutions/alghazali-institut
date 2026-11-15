<?php
/**
 * Accueil : les cours en ligne (maquette de classe virtuelle + bénéfices).
 *
 * @package AlGhazzali
 */

defined( 'ABSPATH' ) || exit;

$ag_benefits = array(
	array( 'video', __( 'En direct avec votre professeur', 'al-ghazzali' ), __( 'Des séances interactives, pas des vidéos préenregistrées : vous lisez, répondez et êtes corrigé.', 'al-ghazzali' ) ),
	array( 'globe', __( 'Depuis chez vous, où que vous soyez', 'al-ghazzali' ), __( 'Un ordinateur, une tablette ou un téléphone et une connexion suffisent.', 'al-ghazzali' ) ),
	array( 'users', __( 'Petits groupes et suivi', 'al-ghazzali' ), __( 'Chaque élève est suivi : devoirs corrigés, points réguliers et conseils personnalisés.', 'al-ghazzali' ) ),
	array( 'layers', __( 'Supports de cours fournis', 'al-ghazzali' ), __( 'Des supports clairs et progressifs pour réviser entre deux séances.', 'al-ghazzali' ) ),
);

$ag_landings = ag_landing_pages();
$ag_page_id  = array() !== $ag_landings ? $ag_landings[0]->ID : 0;
?>
<section class="section online" aria-labelledby="online-title">
	<div class="container online__grid">
		<div class="classroom" aria-hidden="true" data-reveal>
			<div class="classroom__bar">
				<span></span><span></span><span></span>
				<strong><?php esc_html_e( 'Classe virtuelle', 'al-ghazzali' ); ?></strong>
			</div>
			<div class="classroom__stage">
				<div class="classroom__board">
					<span lang="ar" dir="rtl">هٰذَا كِتَابٌ</span>
					<small><?php esc_html_e( 'Leçon 3 · La phrase nominale', 'al-ghazzali' ); ?></small>
				</div>
				<div class="classroom__people">
					<?php for ( $ag_i = 0; $ag_i < 4; $ag_i++ ) : ?>
						<span class="classroom__tile"><i></i></span>
					<?php endfor; ?>
				</div>
			</div>
			<div class="classroom__chat">
				<p><b><?php esc_html_e( 'Professeur', 'al-ghazzali' ); ?></b> <?php esc_html_e( 'À vous : lisez la deuxième ligne à voix haute.', 'al-ghazzali' ); ?></p>
			</div>
		</div>

		<div class="online__text">
			<?php ag_section_head( __( 'Cours en ligne', 'al-ghazzali' ), __( 'Des cours d’arabe en ligne, *comme en classe*', 'al-ghazzali' ), __( 'La qualité d’un enseignement en présentiel, avec la souplesse du distanciel.', 'al-ghazzali' ), '', 'online-title' ); ?>
			<ul class="benefits" data-stagger>
				<?php foreach ( $ag_benefits as list( $ag_icon, $ag_title, $ag_text ) ) : ?>
					<li data-reveal>
						<span class="benefits__icon"><?php echo ag_icon( $ag_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<div>
							<h3><?php echo esc_html( $ag_title ); ?></h3>
							<p><?php echo esc_html( $ag_text ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $ag_page_id > 0 ) : ?>
				<a class="btn btn--primary" href="<?php echo esc_url( (string) get_permalink( $ag_page_id ) ); ?>"><?php esc_html_e( 'Découvrir les cours en ligne', 'al-ghazzali' ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
