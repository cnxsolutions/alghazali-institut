<?php
/**
 * Fiche formation, zone « Les niveaux » : frise en onglets puis programme détaillé de chaque niveau.
 *
 * Sans JavaScript, la frise reste une liste d'ancres et tous les niveaux s'affichent à la suite.
 *
 * @package AlGhazzali
 *
 * Arguments : levels (voir Course::levels()), intro.
 */

defined( 'ABSPATH' ) || exit;

$ag_levels = is_array( $args['levels'] ?? null ) ? $args['levels'] : array();
if ( array() === $ag_levels ) {
	return;
}
$ag_intro = (string) ( $args['intro'] ?? '' );
?>
<section class="section levels-zone" id="niveaux" aria-labelledby="levels-title">
	<div class="container">
		<?php
		ag_section_head(
			__( 'Le parcours', 'al-ghazzali' ),
			/* translators: %s: « 5 niveaux ». */
			sprintf( __( 'Un parcours en *%s*', 'al-ghazzali' ), ag_levels_label( count( $ag_levels ) ) ),
			$ag_intro,
			'section-head--center',
			'levels-title'
		);
		?>

		<div class="levels" data-tabs>
			<?php if ( count( $ag_levels ) > 1 ) : ?>
				<nav class="levels__nav" aria-label="<?php esc_attr_e( 'Niveaux du parcours', 'al-ghazzali' ); ?>" data-reveal>
					<?php foreach ( $ag_levels as $ag_index => $ag_level ) : ?>
						<a class="level-tab" href="#niveau-<?php echo esc_attr( (string) ( $ag_index + 1 ) ); ?>" data-tab>
							<span class="level-tab__num" lang="ar" aria-hidden="true"><?php echo esc_html( ag_arabic_numeral( $ag_index + 1 ) ); ?></span>
							<span class="level-tab__name"><?php echo esc_html( $ag_level['name'] ); ?></span>
							<?php if ( '' !== $ag_level['subtitle'] ) : ?>
								<span class="level-tab__sub"><?php echo esc_html( $ag_level['subtitle'] ); ?></span>
							<?php endif; ?>
							<?php if ( '' !== $ag_level['note'] ) : ?>
								<span class="level-tab__note"><?php echo esc_html( $ag_level['note'] ); ?></span>
							<?php endif; ?>
						</a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

			<?php foreach ( $ag_levels as $ag_index => $ag_level ) : ?>
				<?php $ag_panel = 'niveau-' . ( $ag_index + 1 ); ?>
				<article class="level-panel<?php echo '' !== $ag_level['price'] ? ' level-panel--priced' : ''; ?>" id="<?php echo esc_attr( $ag_panel ); ?>" aria-labelledby="<?php echo esc_attr( $ag_panel ); ?>-title" data-reveal>
					<div class="level-panel__main">
						<header class="level-panel__head">
							<span class="level-panel__num" lang="ar" aria-hidden="true"><?php echo esc_html( ag_arabic_numeral( $ag_index + 1 ) ); ?></span>
							<div>
								<p class="kicker"><?php echo esc_html( implode( ' · ', array_filter( array( __( 'Programme détaillé', 'al-ghazzali' ), $ag_level['note'] ) ) ) ); ?></p>
								<h3 class="level-panel__title" id="<?php echo esc_attr( $ag_panel ); ?>-title">
									<?php echo esc_html( $ag_level['name'] ); ?>
									<?php if ( '' !== $ag_level['subtitle'] ) : ?>
										<span><?php echo esc_html( $ag_level['subtitle'] ); ?></span>
									<?php endif; ?>
								</h3>
							</div>
						</header>

						<?php if ( '' !== $ag_level['description'] ) : ?>
							<div class="level-panel__desc"><?php echo wp_kses_post( wpautop( esc_html( $ag_level['description'] ) ) ); ?></div>
						<?php endif; ?>

						<?php if ( array() !== $ag_level['objectives'] || array() !== $ag_level['programme'] ) : ?>
							<div class="level-panel__lists">
								<?php if ( array() !== $ag_level['objectives'] ) : ?>
									<div>
										<h4 class="level-panel__subtitle"><?php esc_html_e( 'Objectifs du niveau', 'al-ghazzali' ); ?></h4>
										<ul class="checklist">
											<?php foreach ( $ag_level['objectives'] as $ag_item ) : ?>
												<li><?php echo ag_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $ag_item ); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php endif; ?>
								<?php if ( array() !== $ag_level['programme'] ) : ?>
									<div>
										<h4 class="level-panel__subtitle"><?php esc_html_e( 'Au programme', 'al-ghazzali' ); ?></h4>
										<ul class="checklist checklist--dots">
											<?php foreach ( $ag_level['programme'] as $ag_item ) : ?>
												<li><?php echo esc_html( $ag_item ); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( '' !== $ag_level['method'] ) : ?>
							<div class="level-panel__method">
								<h4 class="level-panel__subtitle"><?php esc_html_e( 'La méthode', 'al-ghazzali' ); ?></h4>
								<?php echo wp_kses_post( wpautop( esc_html( $ag_level['method'] ) ) ); ?>
							</div>
						<?php endif; ?>

						<?php if ( '' !== $ag_level['material'] ) : ?>
							<p class="level-panel__material"><?php echo ag_icon( 'book' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $ag_level['material'] ); ?></p>
						<?php endif; ?>
					</div>

					<?php if ( '' !== $ag_level['price'] ) : ?>
						<aside class="price-card" aria-label="<?php esc_attr_e( 'Tarif', 'al-ghazzali' ); ?>">
							<p class="price-card__label"><?php esc_html_e( 'Tarif', 'al-ghazzali' ); ?></p>
							<p class="price-card__value"><?php echo esc_html( $ag_level['price'] ); ?></p>
							<?php if ( '' !== $ag_level['price_note'] ) : ?>
								<p class="price-card__note"><?php echo esc_html( $ag_level['price_note'] ); ?></p>
							<?php endif; ?>
							<a class="btn btn--gold btn--block" <?php echo ag_enroll_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( ag_enroll_label() ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
						</aside>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
