<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var array $event Datos normalizados del evento desde IGE_Query
 */
$images      = $event['images'] ?? [];
$image_fit   = $event['image_fit'] ?? 'contain';
$gallery_mode = $event['gallery_mode'] ?? 'slider';
$has_images  = ! empty( $images );
$is_multi_img = count( $images ) > 1;
?>
<article class="ige-featured-card"
	data-ige-event-id="<?php echo esc_attr( (string) $event['id'] ); ?>"
	data-ige-start="<?php echo esc_attr( $event['iso_start'] ); ?>"
	data-ige-end="<?php echo esc_attr( $event['iso_end'] ); ?>"
	data-ige-countdown="<?php echo $event['countdown'] ? '1' : '0'; ?>">

	<div class="ige-featured-inner">
		<!-- Columna Visual: Imagen / Slider -->
		<div class="ige-featured-media">
			<?php if ( $has_images ) : ?>
				<div class="ige-slider-container <?php echo $is_multi_img ? 'ige-has-multiple' : ''; ?>" data-fit="<?php echo esc_attr( $image_fit ); ?>">
					<div class="ige-slider-track">
						<?php foreach ( $images as $idx => $img ) : ?>
							<div class="ige-slide <?php echo $idx === 0 ? 'active' : ''; ?>">
								<a href="<?php echo esc_url( $img['full'] ); ?>"
									class="ige-lightbox-link"
									data-elementor-open-lightbox="yes"
									data-elementor-lightbox-slideshow="ige-gallery-<?php echo esc_attr( (string) $event['id'] ); ?>"
									data-elementor-lightbox-title="<?php echo esc_attr( $event['title'] ); ?>">
									<img src="<?php echo esc_url( $img['url'] ); ?>"
										alt="<?php echo esc_attr( $img['alt'] ?: $event['title'] ); ?>"
										class="ige-media-img fit-<?php echo esc_attr( $image_fit ); ?>"
										loading="lazy" />
									<span class="ige-zoom-btn" title="<?php esc_attr_e( 'Ampliar cartel', 'iglesia-eventos' ); ?>">
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
											<circle cx="11" cy="11" r="8"></circle>
											<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
											<line x1="11" y1="8" x2="11" y2="14"></line>
											<line x1="8" y1="11" x2="14" y2="11"></line>
										</svg>
										<span><?php esc_html_e( 'Ampliar', 'iglesia-eventos' ); ?></span>
									</span>
								</a>
							</div>
						<?php endforeach; ?>
					</div>

					<?php if ( $is_multi_img ) : ?>
						<button type="button" class="ige-slider-btn prev" aria-label="<?php esc_attr_e( 'Imagen anterior', 'iglesia-eventos' ); ?>">❮</button>
						<button type="button" class="ige-slider-btn next" aria-label="<?php esc_attr_e( 'Siguiente imagen', 'iglesia-eventos' ); ?>">❯</button>
						<div class="ige-slider-dots">
							<?php foreach ( $images as $idx => $img ) : ?>
								<button type="button" class="ige-dot <?php echo $idx === 0 ? 'active' : ''; ?>" data-index="<?php echo esc_attr( (string) $idx ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Ir a diapositiva %d', 'iglesia-eventos' ), $idx + 1 ) ); ?>"></button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<div class="ige-placeholder-media">
					<span class="dashicons dashicons-calendar-alt"></span>
				</div>
			<?php endif; ?>
		</div>

		<!-- Columna de Información -->
		<div class="ige-featured-content">
			<?php if ( ! empty( $event['kicker'] ) ) : ?>
				<div class="ige-kicker"><?php echo esc_html( $event['kicker'] ); ?></div>
			<?php endif; ?>

			<h3 class="ige-featured-title"><?php echo esc_html( $event['title'] ); ?></h3>

			<?php if ( ! empty( $event['excerpt'] ) ) : ?>
				<div class="ige-featured-excerpt">
					<p><?php echo esc_html( $event['excerpt'] ); ?></p>
				</div>
			<?php endif; ?>

			<!-- Meta Info: Fecha y Lugar -->
			<div class="ige-meta-badges">
				<?php if ( ! empty( $event['human_date']['primary'] ) ) : ?>
					<div class="ige-meta-chip date-chip">
						<span class="ige-chip-icon">📅</span>
						<div class="ige-chip-text">
							<span class="ige-chip-label"><?php esc_html_e( 'FECHA Y HORA', 'iglesia-eventos' ); ?></span>
							<strong class="ige-chip-value">
								<?php echo esc_html( $event['human_date']['primary'] ); ?>
								<?php if ( ! empty( $event['human_date']['time'] ) ) : ?>
									<span class="ige-chip-time">| <?php echo esc_html( $event['human_date']['time'] ); ?> h</span>
								<?php endif; ?>
							</strong>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $event['location_name'] ) || ! empty( $event['address'] ) ) : ?>
					<div class="ige-meta-chip location-chip">
						<span class="ige-chip-icon">📍</span>
						<div class="ige-chip-text">
							<span class="ige-chip-label"><?php esc_html_e( 'UBICACIÓN', 'iglesia-eventos' ); ?></span>
							<strong class="ige-chip-value">
								<?php echo esc_html( $event['address'] ?: $event['location_name'] ); ?>
							</strong>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<!-- Temporizador Regresivo (si está habilitado) -->
			<?php if ( $event['countdown'] ) : ?>
				<div class="ige-countdown-wrap" data-start="<?php echo esc_attr( $event['iso_start'] ); ?>">
					<div class="ige-countdown-header"><?php esc_html_e( 'TIEMPO RESTANTE PARA EL EVENTO:', 'iglesia-eventos' ); ?></div>
					<div class="ige-countdown-grid">
						<div class="ige-cd-box">
							<span class="ige-cd-num cd-days">00</span>
							<span class="ige-cd-unit"><?php esc_html_e( 'Días', 'iglesia-eventos' ); ?></span>
						</div>
						<span class="ige-cd-colon">:</span>
						<div class="ige-cd-box">
							<span class="ige-cd-num cd-hours">00</span>
							<span class="ige-cd-unit"><?php esc_html_e( 'Horas', 'iglesia-eventos' ); ?></span>
						</div>
						<span class="ige-cd-colon">:</span>
						<div class="ige-cd-box">
							<span class="ige-cd-num cd-minutes">00</span>
							<span class="ige-cd-unit"><?php esc_html_e( 'Min', 'iglesia-eventos' ); ?></span>
						</div>
						<span class="ige-cd-colon">:</span>
						<div class="ige-cd-box">
							<span class="ige-cd-num cd-seconds">00</span>
							<span class="ige-cd-unit"><?php esc_html_e( 'Seg', 'iglesia-eventos' ); ?></span>
						</div>
					</div>
					<div class="ige-cd-live-msg" style="display:none;"><?php esc_html_e( '¡El evento está comenzando!', 'iglesia-eventos' ); ?></div>
				</div>
			<?php endif; ?>

			<!-- Botones de Acción -->
			<div class="ige-buttons-row">
				<?php if ( ! empty( $event['buttons'] ) ) : ?>
					<?php foreach ( $event['buttons'] as $btn ) : ?>
						<a href="<?php echo esc_url( $btn['url'] ); ?>"
							class="ige-btn ige-btn-<?php echo esc_attr( $btn['style'] ?: 'primary' ); ?>"
							<?php echo ! empty( $btn['new_tab'] ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
							<?php echo esc_html( $btn['text'] ); ?>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if ( ! empty( $event['maps_url'] ) && empty( $event['buttons'] ) ) : ?>
					<a href="<?php echo esc_url( $event['maps_url'] ); ?>" class="ige-btn ige-btn-primary" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Cómo llegar', 'iglesia-eventos' ); ?>
					</a>
				<?php endif; ?>

				<?php if ( ! empty( $event['google_calendar_url'] ) ) : ?>
					<a href="<?php echo esc_url( $event['google_calendar_url'] ); ?>" class="ige-btn ige-btn-calendar" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Añadir a Google Calendar', 'iglesia-eventos' ); ?>">
						📅 <?php esc_html_e( 'Añadir al Calendario', 'iglesia-eventos' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</article>
