<?php
/**
 * Accueil : les classes séparées hommes / femmes, avec leur nombre de créneaux hebdomadaires.
 *
 * @package AlGhazzali
 *
 * Arguments : week (créneaux de la semaine, voir Schedule::weekly()).
 */

use AlGhazzali\Core\Content\Fields;

defined( 'ABSPATH' ) || exit;

$ag_week  = is_array( $args['week'] ?? null ) ? $args['week'] : array();
$ag_slots = array_merge( ...array_values( $ag_week ) );
$ag_page  = ag_page_by_template( 'page-templates/schedule.php' );

$ag_texts = array(
	'hommes' => array( 'الرِّجَال', __( 'Des groupes réservés aux hommes, à chaque niveau, avec un enseignant qui suit la progression de chacun.', 'al-ghazzali' ) ),
	'femmes' => array( 'النِّسَاء', __( 'Des groupes réservés aux femmes, à chaque niveau, avec une enseignante qui suit la progression de chacune.', 'al-ghazzali' ) ),
);
?>
<section class="section section--ink classes" aria-labelledby="classes-title">
	<div class="pattern pattern--soft" aria-hidden="true"></div>
	<div class="container">
		<header class="section-head section-head--center section-head--light" data-reveal>
			<p class="kicker"><?php esc_html_e( 'Hommes et femmes', 'al-ghazzali' ); ?></p>
			<h2 class="section-title" id="classes-title"><?php echo ag_highlight( __( 'Des classes *séparées*, à chaque niveau', 'al-ghazzali' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
			<p class="section-intro"><?php esc_html_e( 'Chaque niveau ouvre des groupes pour les hommes et des groupes pour les femmes, à des horaires différents : choisissez celui qui vous convient.', 'al-ghazzali' ); ?></p>
		</header>

		<div class="class-grid" data-stagger>
			<?php foreach ( Fields::publics() as $ag_public => $ag_label ) : ?>
				<?php
				$ag_count = count( array_filter( $ag_slots, static fn( array $slot ): bool => $slot['public'] === $ag_public ) );
				$ag_url   = $ag_page > 0 ? add_query_arg( 'classe', $ag_public, (string) get_permalink( $ag_page ) ) : '';
				?>
				<article class="class-card" data-reveal data-spotlight>
					<span class="class-card__ar" lang="ar" dir="rtl" aria-hidden="true"><?php echo esc_html( $ag_texts[ $ag_public ][0] ?? '' ); ?></span>
					<h3 class="class-card__title">
						<?php
						/* translators: %s: Hommes ou Femmes. */
						echo esc_html( sprintf( __( 'Classes %s', 'al-ghazzali' ), mb_strtolower( $ag_label ) ) );
						?>
					</h3>
					<p><?php echo esc_html( $ag_texts[ $ag_public ][1] ?? '' ); ?></p>
					<?php if ( $ag_count > 0 ) : ?>
						<p class="class-card__count">
							<strong><?php echo esc_html( (string) $ag_count ); ?></strong>
							<?php echo esc_html( _n( 'créneau par semaine', 'créneaux par semaine', $ag_count, 'al-ghazzali' ) ); ?>
						</p>
					<?php endif; ?>
					<?php if ( '' !== $ag_url ) : ?>
						<a class="link-arrow" href="<?php echo esc_url( $ag_url ); ?>"><?php esc_html_e( 'Voir les horaires', 'al-ghazzali' ); ?><?php echo ag_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
