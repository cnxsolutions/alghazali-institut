<?php
/**
 * Accueil : bannière principale (H1 éditable dans le Customizer).
 *
 * @package AlGhazzali
 */

use AlGhazzali\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

$ag_institute = ag_institute();
?>
<section class="hero" aria-labelledby="hero-title">
	<div class="hero__bg" aria-hidden="true">
		<div class="pattern pattern--hero"></div>
		<div class="hero__glow"></div>
	</div>

	<div class="container hero__grid">
		<div class="hero__content">
			<p class="hero__kicker"><?php echo esc_html( $ag_institute->get( 'hero_kicker' ) ); ?></p>
			<h1 id="hero-title" class="hero__title"><?php echo ag_highlight( $ag_institute->get( 'hero_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
			<p class="hero__lead"><?php echo esc_html( $ag_institute->get( 'hero_text' ) ); ?></p>

			<div class="hero__actions">
				<a class="btn btn--gold btn--lg" <?php echo ag_enroll_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php echo esc_html( ag_enroll_label() ); ?>
					<?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<a class="btn btn--outline-light btn--lg" href="<?php echo esc_url( (string) get_post_type_archive_link( PostTypes::COURSE ) ); ?>">
					<?php esc_html_e( 'Découvrir nos formations', 'al-ghazzali' ); ?>
				</a>
			</div>

			<ul class="hero__proof">
				<li><?php echo ag_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Classes hommes et femmes séparées', 'al-ghazzali' ); ?></li>
				<li><?php echo ag_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Cours en direct', 'al-ghazzali' ); ?></li>
				<li><?php echo ag_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Du débutant à l’avancé', 'al-ghazzali' ); ?></li>
			</ul>
		</div>

		<div class="hero__visual" aria-hidden="true">
			<div class="emblem">
				<svg class="emblem__rings" viewBox="0 0 400 400">
					<circle cx="200" cy="200" r="190" />
					<circle cx="200" cy="200" r="150" stroke-dasharray="2 8" />
					<g class="emblem__star">
						<rect x="80" y="80" width="240" height="240" rx="6" />
						<rect x="80" y="80" width="240" height="240" rx="6" transform="rotate(45 200 200)" />
					</g>
					<g class="emblem__star emblem__star--inner">
						<rect x="125" y="125" width="150" height="150" rx="4" />
						<rect x="125" y="125" width="150" height="150" rx="4" transform="rotate(45 200 200)" />
					</g>
				</svg>
				<?php // « La langue du dâd » : surnom classique de la langue arabe. ?>
				<span class="emblem__word" lang="ar" dir="rtl">الضَّاد</span>
			</div>

			<div class="float-card float-card--letters">
				<span lang="ar" dir="rtl">أ ب ت ث</span>
				<small><?php esc_html_e( 'Lire et écrire l’arabe', 'al-ghazzali' ); ?></small>
			</div>

			<span class="float-letter float-letter--1" lang="ar">ع</span>
			<span class="float-letter float-letter--2" lang="ar">ل</span>
			<span class="float-letter float-letter--3" lang="ar">م</span>
		</div>
	</div>
</section>
