<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var array $event Datos normalizados del evento
 */
$images     = $event['images'] ?? [];
$image_fit  = $event['image_fit'] ?? 'contain';
$first_img  = ! empty( $images ) ? $images[0] : null;
?>
<article class="ige-event-card"
	data-ige-event-id="<?php echo esc_attr( (string) $event['id'] ); ?>"
	data-ige-start="<?php echo esc_attr( $event['iso_start'] ); ?>"
	data-ige-end="<?php echo esc_attr( $event['iso_end'] ); ?>">

	<div class="ige-card-media-wrap fit-<?php echo esc_attr( $image_fit ); ?>">
		<?php if ( $first_img ) : ?>
			<a href="<?php echo esc_url( $first_img['full'] ); ?>"
				class="ige-card-img-link ige-lightbox-trigger"
				data-elementor-open-lightbox="yes"
				data-elementor-lightbox-slideshow="ige-card-<?php echo esc_attr( (string) $event['id'] ); ?>"
				data-elementor-lightbox-title="<?php echo esc_attr( $event['title'] ); ?>"
				title="<?php esc_attr_e( 'Ampliar cartel', 'iglesia-eventos' ); ?>">
				<img src="<?php echo esc_url( $first_img['url'] ); ?>"
					alt="<?php echo esc_attr( $first_img['alt'] ?: $event['title'] ); ?>"
					class="ige-card-img fit-<?php echo esc_attr( $image_fit ); ?>"
					loading="lazy" />
				<span class="ige-zoom-btn" title="<?php esc_attr_e( 'Ampliar imagen', 'iglesia-eventos' ); ?>">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<circle cx="11" cy="11" r="8"></circle>
						<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
						<line x1="11" y1="8" x2="11" y2="14"></line>
						<line x1="8" y1="11" x2="14" y2="11"></line>
					</svg>
					<span><?php esc_html_e( 'Ampliar', 'iglesia-eventos' ); ?></span>
				</span>
			</a>
		<?php else : ?>
			<div class="ige-card-placeholder">
				<span class="dashicons dashicons-calendar-alt"></span>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $event['human_date']['badge_day'] ) ) : ?>
			<div class="ige-date-badge">
				<span class="badge-day"><?php echo esc_html( $event['human_date']['badge_day'] ); ?></span>
				<span class="badge-month"><?php echo esc_html( $event['human_date']['badge_month'] ); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<div class="ige-card-body">
		<?php if ( ! empty( $event['kicker'] ) ) : ?>
			<span class="ige-card-kicker"><?php echo esc_html( $event['kicker'] ); ?></span>
		<?php endif; ?>

		<h4 class="ige-card-title">
			<a href="<?php echo esc_url( $event['permalink'] ); ?>"><?php echo esc_html( $event['title'] ); ?></a>
		</h4>

		<?php if ( ! empty( $event['human_date']['primary'] ) ) : ?>
			<div class="ige-card-meta date">
				<span>📅 <?php echo esc_html( $event['human_date']['primary'] ); ?><?php echo ! empty( $event['human_date']['time'] ) ? ' · ' . esc_html( $event['human_date']['time'] ) . ' h' : ''; ?></span>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $event['location_name'] ) || ! empty( $event['address'] ) ) : ?>
			<div class="ige-card-meta location">
				<span>📍 <?php echo esc_html( $event['location_name'] ?: $event['address'] ); ?></span>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $event['excerpt'] ) ) : ?>
			<p class="ige-card-excerpt"><?php echo esc_html( wp_trim_words( $event['excerpt'], 18 ) ); ?></p>
		<?php endif; ?>

		<div class="ige-card-footer">
			<?php if ( ! empty( $event['buttons'] ) ) : ?>
				<?php $btn = $event['buttons'][0]; ?>
				<a href="<?php echo esc_url( $btn['url'] ); ?>"
					class="ige-btn ige-btn-sm ige-btn-<?php echo esc_attr( $btn['style'] ?: 'primary' ); ?>"
					<?php echo ! empty( $btn['new_tab'] ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
					<?php echo esc_html( $btn['text'] ); ?>
				</a>
			<?php elseif ( ! empty( $event['maps_url'] ) ) : ?>
				<a href="<?php echo esc_url( $event['maps_url'] ); ?>" class="ige-btn ige-btn-sm ige-btn-secondary" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Cómo llegar', 'iglesia-eventos' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</article>
