<?php
/**
 * Grille hebdomadaire des créneaux, avec filtres combinables (formation, classe, niveau).
 *
 * @package AlGhazzali
 *
 * Arguments :
 * - week        array<int, list<créneau>> (voir Schedule)
 * - id          identifiant HTML de la grille
 * - filters     list<array{label: string, param: string, all: string, options: array<string, string>}>
 * - heading     h2|h3 pour les jours (défaut h3)
 * - show_course affiche le nom de la formation sur chaque créneau (défaut vrai)
 */

use AlGhazzali\Core\Content\Fields;
use AlGhazzali\Core\Content\Schedule;

defined( 'ABSPATH' ) || exit;

$ag_week        = is_array( $args['week'] ?? null ) ? $args['week'] : array();
$ag_id          = (string) ( $args['id'] ?? 'schedule-grid' );
$ag_filters     = is_array( $args['filters'] ?? null ) ? $args['filters'] : array();
$ag_heading     = 'h2' === ( $args['heading'] ?? '' ) ? 'h2' : 'h3';
$ag_show_course = (bool) ( $args['show_course'] ?? true );
$ag_days        = Schedule::days();
$ag_publics     = Fields::publics();
$ag_modes       = Fields::modes();
?>
<?php if ( array() !== $ag_filters ) : ?>
	<div class="filters">
		<?php foreach ( $ag_filters as $ag_index => $ag_filter ) : ?>
			<?php $ag_label_id = $ag_id . '-filter-' . $ag_index; ?>
			<div class="filters__group">
				<span class="filters__label" id="<?php echo esc_attr( $ag_label_id ); ?>"><?php echo esc_html( $ag_filter['label'] ); ?></span>
				<div class="tabs tabs--wrap" role="group" aria-labelledby="<?php echo esc_attr( $ag_label_id ); ?>" data-filter-group="<?php echo esc_attr( $ag_id ); ?>" data-filter-param="<?php echo esc_attr( $ag_filter['param'] ); ?>">
					<button class="tabs__item is-active" type="button" data-filter="*" aria-pressed="true"><?php echo esc_html( $ag_filter['all'] ); ?></button>
					<?php foreach ( $ag_filter['options'] as $ag_value => $ag_option ) : ?>
						<button class="tabs__item" type="button" data-filter="<?php echo esc_attr( $ag_value ); ?>" aria-pressed="false"><?php echo esc_html( $ag_option ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<div class="week" id="<?php echo esc_attr( $ag_id ); ?>">
	<?php foreach ( $ag_week as $ag_day => $ag_slots ) : ?>
		<?php
		if ( array() === $ag_slots ) {
			continue;
		}
		$ag_day_id = $ag_id . '-day-' . $ag_day;
		?>
		<section class="week__day" aria-labelledby="<?php echo esc_attr( $ag_day_id ); ?>">
			<<?php echo esc_html( $ag_heading ); ?> class="week__title" id="<?php echo esc_attr( $ag_day_id ); ?>"><?php echo esc_html( $ag_days[ $ag_day ] ); ?></<?php echo esc_html( $ag_heading ); ?>>
			<ul class="week__slots">
				<?php foreach ( $ag_slots as $ag_slot ) : ?>
					<?php
					$ag_teacher = Schedule::teacher_name( $ag_slot['teacher'] );
					$ag_public  = $ag_publics[ $ag_slot['public'] ] ?? '';
					$ag_mode    = $ag_modes[ $ag_slot['mode'] ] ?? '';
					?>
					<li class="slot slot--<?php echo esc_attr( '' !== $ag_public ? $ag_slot['public'] : 'mixte' ); ?>" data-tags="<?php echo esc_attr( ag_slot_tags( $ag_slot ) ); ?>">
						<time class="slot__time"><?php echo esc_html( $ag_slot['start'] . ' – ' . $ag_slot['end'] ); ?></time>
						<?php if ( $ag_show_course ) : ?>
							<a class="slot__title" href="<?php echo esc_url( get_permalink( $ag_slot['course'] ) ); ?>"><?php echo esc_html( get_the_title( $ag_slot['course'] ) ); ?></a>
							<?php if ( '' !== $ag_slot['level'] ) : ?>
								<span class="slot__label"><?php echo esc_html( $ag_slot['level'] ); ?></span>
							<?php endif; ?>
						<?php else : ?>
							<span class="slot__title"><?php echo esc_html( '' !== $ag_slot['level'] ? $ag_slot['level'] : get_the_title( $ag_slot['course'] ) ); ?></span>
						<?php endif; ?>
						<?php if ( '' !== $ag_teacher ) : ?>
							<span class="slot__teacher"><?php echo esc_html( $ag_teacher ); ?></span>
						<?php endif; ?>
						<?php if ( '' !== $ag_public || '' !== $ag_mode ) : ?>
							<span class="slot__meta">
								<?php if ( '' !== $ag_public ) : ?>
									<span class="slot__chip slot__chip--public"><?php echo esc_html( $ag_public ); ?></span>
								<?php endif; ?>
								<?php if ( '' !== $ag_mode ) : ?>
									<span class="slot__chip"><?php echo esc_html( $ag_mode ); ?></span>
								<?php endif; ?>
							</span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endforeach; ?>
</div>
<p class="week__empty" data-filter-empty="<?php echo esc_attr( $ag_id ); ?>" hidden><?php esc_html_e( 'Aucun créneau ne correspond à cette sélection pour le moment.', 'al-ghazzali' ); ?></p>
