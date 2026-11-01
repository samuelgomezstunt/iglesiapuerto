<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IGE_Admin {

	public static function init(): void {
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
		add_action( 'save_post_iglesia_evento', [ __CLASS__, 'save_meta_boxes' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
		add_filter( 'manage_iglesia_evento_posts_columns', [ __CLASS__, 'manage_columns' ] );
		add_action( 'manage_iglesia_evento_posts_custom_column', [ __CLASS__, 'render_column' ], 10, 2 );
		add_filter( 'manage_edit-iglesia_evento_sortable_columns', [ __CLASS__, 'sortable_columns' ] );
		add_action( 'pre_get_posts', [ __CLASS__, 'order_by_event_date' ] );
	}

	public static function enqueue_admin_assets( string $hook ): void {
		global $post_type;
		if ( $post_type !== 'iglesia_evento' ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-sortable' );

		wp_enqueue_style(
			'ige-admin-css',
			IGE_URL . 'assets/css/admin.css',
			[],
			IGE_VERSION
		);

		wp_enqueue_script(
			'ige-admin-js',
			IGE_URL . 'assets/js/admin.js',
			[ 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ],
			IGE_VERSION,
			true
		);
	}

	public static function add_meta_boxes(): void {
		add_meta_box(
			'ige_box_details',
			__( 'Fecha, Horario y Ubicación', 'iglesia-eventos' ),
			[ __CLASS__, 'render_box_details' ],
			'iglesia_evento',
			'normal',
			'high'
		);

		add_meta_box(
			'ige_box_display',
			__( 'Opciones de Visualización y Destacado', 'iglesia-eventos' ),
			[ __CLASS__, 'render_box_display' ],
			'iglesia_evento',
			'normal',
			'high'
		);

		add_meta_box(
			'ige_box_gallery',
			__( 'Galería de Imágenes / Slider', 'iglesia-eventos' ),
			[ __CLASS__, 'render_box_gallery' ],
			'iglesia_evento',
			'normal',
			'default'
		);

		add_meta_box(
			'ige_box_buttons',
			__( 'Botones de Acción (Enlaces Externos, Formularios)', 'iglesia-eventos' ),
			[ __CLASS__, 'render_box_buttons' ],
			'iglesia_evento',
			'normal',
			'default'
		);
	}

	public static function render_box_details( WP_Post $post ): void {
		wp_nonce_field( 'ige_save_meta', 'ige_meta_nonce' );

		$start         = get_post_meta( $post->ID, 'ige_start', true );
		$end           = get_post_meta( $post->ID, 'ige_end', true );
		$all_day       = get_post_meta( $post->ID, 'ige_all_day', true ) === '1';
		$kicker        = get_post_meta( $post->ID, 'ige_kicker', true );
		$location_name = get_post_meta( $post->ID, 'ige_location_name', true );
		$address       = get_post_meta( $post->ID, 'ige_address', true );
		$maps_url      = get_post_meta( $post->ID, 'ige_maps_url', true );

		// Separar fecha y hora para inputs amigables
		$start_date = '';
		$start_time = '';
		if ( ! empty( $start ) ) {
			$parts      = explode( ' ', $start );
			$start_date = $parts[0] ?? '';
			$start_time = $parts[1] ?? '';
		}

		$end_date = '';
		$end_time = '';
		if ( ! empty( $end ) ) {
			$parts    = explode( ' ', $end );
			$end_date = $parts[0] ?? '';
			$end_time = $parts[1] ?? '';
		}
		?>
		<div class="ige-admin-grid">
			<div class="ige-field-group">
				<label for="ige_kicker"><strong><?php esc_html_e( 'Antetítulo / Etiqueta superior:', 'iglesia-eventos' ); ?></strong></label>
				<input type="text" id="ige_kicker" name="ige_kicker" value="<?php echo esc_attr( $kicker ); ?>" class="widefat" placeholder="Ej: Te invitamos, Campamento Nacional..." />
				<span class="description"><?php esc_html_e( 'Texto pequeño que precede al título del evento.', 'iglesia-eventos' ); ?></span>
			</div>

			<div class="ige-field-row">
				<div class="ige-col">
					<label for="ige_start_date"><strong><?php esc_html_e( 'Fecha de Inicio (*):', 'iglesia-eventos' ); ?></strong></label>
					<input type="date" id="ige_start_date" name="ige_start_date" value="<?php echo esc_attr( $start_date ); ?>" required class="widefat" />
				</div>
				<div class="ige-col">
					<label for="ige_start_time"><strong><?php esc_html_e( 'Hora de Inicio:', 'iglesia-eventos' ); ?></strong></label>
					<input type="time" id="ige_start_time" name="ige_start_time" value="<?php echo esc_attr( $start_time ); ?>" class="widefat" />
				</div>
			</div>

			<div class="ige-field-row">
				<div class="ige-col">
					<label for="ige_end_date"><strong><?php esc_html_e( 'Fecha de Finalización (opcional):', 'iglesia-eventos' ); ?></strong></label>
					<input type="date" id="ige_end_date" name="ige_end_date" value="<?php echo esc_attr( $end_date ); ?>" class="widefat" />
				</div>
				<div class="ige-col">
					<label for="ige_end_time"><strong><?php esc_html_e( 'Hora de Fin (opcional):', 'iglesia-eventos' ); ?></strong></label>
					<input type="time" id="ige_end_time" name="ige_end_time" value="<?php echo esc_attr( $end_time ); ?>" class="widefat" />
				</div>
			</div>

			<div class="ige-field-group">
				<label>
					<input type="checkbox" name="ige_all_day" value="1" <?php checked( $all_day ); ?> />
					<strong><?php esc_html_e( 'Evento de todo el día (ocultar horas específicas)', 'iglesia-eventos' ); ?></strong>
				</label>
			</div>

			<hr style="margin: 15px 0;" />

			<div class="ige-field-group">
				<label for="ige_location_name"><strong><?php esc_html_e( 'Nombre del Lugar / Recinto:', 'iglesia-eventos' ); ?></strong></label>
				<input type="text" id="ige_location_name" name="ige_location_name" value="<?php echo esc_attr( $location_name ); ?>" class="widefat" placeholder="Ej: Iglesia, Hotel Valle Orotava..." />
			</div>

			<div class="ige-field-group">
				<label for="ige_address"><strong><?php esc_html_e( 'Dirección física completa:', 'iglesia-eventos' ); ?></strong></label>
				<input type="text" id="ige_address" name="ige_address" value="<?php echo esc_attr( $address ); ?>" class="widefat" placeholder="Ej: Calle Don Quijote, 42, esquina La Mancha, Puerto del Rosario" />
			</div>

			<div class="ige-field-group">
				<label for="ige_maps_url"><strong><?php esc_html_e( 'Enlace a Google Maps:', 'iglesia-eventos' ); ?></strong></label>
				<input type="url" id="ige_maps_url" name="ige_maps_url" value="<?php echo esc_attr( $maps_url ); ?>" class="widefat" placeholder="https://maps.google.com/?q=..." />
				<span class="description"><?php esc_html_e( 'Si se deja vacío pero se indica la dirección, se creará un enlace automático a Google Maps.', 'iglesia-eventos' ); ?></span>
			</div>
		</div>
		<?php
	}

	public static function render_box_display( WP_Post $post ): void {
		$featured        = get_post_meta( $post->ID, 'ige_featured', true ) === '1';
		$countdown       = get_post_meta( $post->ID, 'ige_countdown', true ) === '1';
		$calendar_button = get_post_meta( $post->ID, 'ige_calendar_button', true ) !== '0'; // default true
		$hide_after_end  = get_post_meta( $post->ID, 'ige_hide_after_end', true ) !== '0';  // default true
		$image_fit       = get_post_meta( $post->ID, 'ige_image_fit', true ) ?: 'contain';
		?>
		<div class="ige-admin-grid">
			<div class="ige-field-group">
				<label>
					<input type="checkbox" name="ige_featured" value="1" <?php checked( $featured ); ?> />
					<strong style="color: #1e3a8a; font-size: 14px;"><?php esc_html_e( '⭐ Mostrar como Evento Destacado a ancho completo', 'iglesia-eventos' ); ?></strong>
				</label>
				<p class="description"><?php esc_html_e( 'Aparecerá en la parte superior con un diseño amplio de doble columna (cartel + información).', 'iglesia-eventos' ); ?></p>
			</div>

			<div class="ige-field-group">
				<label>
					<input type="checkbox" name="ige_countdown" value="1" <?php checked( $countdown ); ?> />
					<strong><?php esc_html_e( '⏱ Mostrar temporizador con cuenta atrás en vivo', 'iglesia-eventos' ); ?></strong>
				</label>
				<p class="description"><?php esc_html_e( 'Calcula los días, horas, minutos y segundos restantes según la zona horaria Atlantic/Canary.', 'iglesia-eventos' ); ?></p>
			</div>

			<div class="ige-field-group">
				<label>
					<input type="checkbox" name="ige_calendar_button" value="1" <?php checked( $calendar_button ); ?> />
					<strong><?php esc_html_e( '📅 Incluir botón "Añadir a Google Calendar"', 'iglesia-eventos' ); ?></strong>
				</label>
			</div>

			<div class="ige-field-group">
				<label>
					<input type="checkbox" name="ige_hide_after_end" value="1" <?php checked( $hide_after_end ); ?> />
					<strong><?php esc_html_e( 'Ocultar automáticamente de la portada cuando el evento finalice', 'iglesia-eventos' ); ?></strong>
				</label>
			</div>

			<div class="ige-field-group">
				<label for="ige_image_fit"><strong><?php esc_html_e( 'Ajuste del cartel / imágenes:', 'iglesia-eventos' ); ?></strong></label>
				<select name="ige_image_fit" id="ige_image_fit" class="widefat" style="max-width: 300px;">
					<option value="contain" <?php selected( $image_fit, 'contain' ); ?>><?php esc_html_e( 'Ajustar completo (contain - recomendado para carteles con texto)', 'iglesia-eventos' ); ?></option>
					<option value="cover" <?php selected( $image_fit, 'cover' ); ?>><?php esc_html_e( 'Rellenar contenedor (cover - recorte inteligente)', 'iglesia-eventos' ); ?></option>
				</select>
			</div>
		</div>
		<?php
	}

	public static function render_box_gallery( WP_Post $post ): void {
		$gallery      = get_post_meta( $post->ID, 'ige_gallery', true );
		$gallery_mode = get_post_meta( $post->ID, 'ige_gallery_mode', true ) ?: 'slider';
		$ids          = array_filter( array_map( 'absint', explode( ',', $gallery ) ) );
		?>
		<div class="ige-admin-grid">
			<div class="ige-field-group">
				<label for="ige_gallery_mode"><strong><?php esc_html_e( 'Modo de visualización de imágenes:', 'iglesia-eventos' ); ?></strong></label>
				<select name="ige_gallery_mode" id="ige_gallery_mode" style="max-width: 250px;">
					<option value="slider" <?php selected( $gallery_mode, 'slider' ); ?>><?php esc_html_e( 'Slider con flechas y puntos', 'iglesia-eventos' ); ?></option>
					<option value="grid" <?php selected( $gallery_mode, 'grid' ); ?>><?php esc_html_e( 'Rejilla de fotos (Lightbox)', 'iglesia-eventos' ); ?></option>
				</select>
			</div>

			<div class="ige-gallery-wrapper">
				<input type="hidden" name="ige_gallery" id="ige_gallery_input" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" />
				<div id="ige_gallery_container" class="ige-gallery-thumbs">
					<?php foreach ( $ids as $img_id ) :
						$src = wp_get_attachment_image_url( $img_id, 'thumbnail' );
						if ( ! $src ) continue;
					?>
						<div class="ige-thumb-item" data-id="<?php echo esc_attr( (string) $img_id ); ?>">
							<img src="<?php echo esc_url( $src ); ?>" alt="" />
							<button type="button" class="ige-remove-img" title="<?php esc_attr_e( 'Eliminar imagen', 'iglesia-eventos' ); ?>">×</button>
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="button button-primary" id="ige_add_gallery_btn">
					<?php esc_html_e( '+ Seleccionar / Añadir Imágenes a la Galería', 'iglesia-eventos' ); ?>
				</button>
				<p class="description"><?php esc_html_e( 'Puedes seleccionar varias imágenes a la vez. Arrastra las miniaturas para reordenar el slider.', 'iglesia-eventos' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Repara secuencias Unicode corruptas tipo u00f3 o \u00f3 producidas
	 * por stripslashes_deep() en wp_postmeta.
	 */
	public static function repair_unicode_string( string $str ): string {
		if ( strpos( $str, 'u00' ) !== false || strpos( $str, '\u00' ) !== false ) {
			$str = (string) preg_replace_callback( '/\\\\?u([0-9a-fA-F]{4})/', function( $m ) {
				return mb_chr( hexdec( $m[1] ), 'UTF-8' );
			}, $str );
		}
		return $str;
	}

	public static function render_box_buttons( WP_Post $post ): void {
		$raw_buttons = get_post_meta( $post->ID, 'ige_buttons', true );
		if ( ! empty( $raw_buttons ) ) {
			$raw_buttons = self::repair_unicode_string( (string) $raw_buttons );
		}
		$buttons     = json_decode( $raw_buttons ?: '[]', true );
		if ( ! is_array( $buttons ) ) {
			$buttons = [];
		}
		foreach ( $buttons as &$b ) {
			if ( isset( $b['text'] ) ) {
				$b['text'] = self::repair_unicode_string( (string) $b['text'] );
			}
		}
		unset( $b );
		?>
		<div class="ige-admin-grid">
			<input type="hidden" name="ige_buttons" id="ige_buttons_json" value="<?php echo esc_attr( wp_json_encode( $buttons, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?>" />
			<table class="widefat striped" id="ige_buttons_table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Texto del Botón', 'iglesia-eventos' ); ?></th>
						<th><?php esc_html_e( 'URL / Enlace externo', 'iglesia-eventos' ); ?></th>
						<th style="width: 120px;"><?php esc_html_e( 'Nueva pestaña', 'iglesia-eventos' ); ?></th>
						<th style="width: 140px;"><?php esc_html_e( 'Estilo', 'iglesia-eventos' ); ?></th>
						<th style="width: 70px;"><?php esc_html_e( 'Acción', 'iglesia-eventos' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $buttons as $idx => $btn ) : ?>
						<tr data-index="<?php echo esc_attr( (string) $idx ); ?>">
							<td><input type="text" class="widefat btn-text" value="<?php echo esc_attr( $btn['text'] ?? '' ); ?>" placeholder="Ej: Inscribirme" /></td>
							<td><input type="url" class="widefat btn-url" value="<?php echo esc_attr( $btn['url'] ?? '' ); ?>" placeholder="https://..." /></td>
							<td style="text-align: center;"><input type="checkbox" class="btn-newtab" <?php checked( ! empty( $btn['new_tab'] ) ); ?> /></td>
							<td>
								<select class="widefat btn-style">
									<option value="primary" <?php selected( ( $btn['style'] ?? 'primary' ), 'primary' ); ?>><?php esc_html_e( 'Principal', 'iglesia-eventos' ); ?></option>
									<option value="secondary" <?php selected( ( $btn['style'] ?? '' ), 'secondary' ); ?>><?php esc_html_e( 'Secundario', 'iglesia-eventos' ); ?></option>
								</select>
							</td>
							<td><button type="button" class="button ige-remove-btn">✕</button></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p>
				<button type="button" class="button button-secondary" id="ige_add_button_row">
					<?php esc_html_e( '+ Añadir Otro Botón', 'iglesia-eventos' ); ?>
				</button>
			</p>
		</div>
		<?php
	}

	public static function save_meta_boxes( int $post_id, WP_Post $post ): void {
		if ( ! isset( $_POST['ige_meta_nonce'] ) || ! wp_verify_nonce( $_POST['ige_meta_nonce'], 'ige_save_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Fecha y hora de inicio
		$start_date = sanitize_text_field( $_POST['ige_start_date'] ?? '' );
		$start_time = sanitize_text_field( $_POST['ige_start_time'] ?? '' );
		if ( ! empty( $start_date ) ) {
			$start = ! empty( $start_time ) ? "$start_date $start_time" : "$start_date 00:00";
			update_post_meta( $post_id, 'ige_start', $start );
		}

		// Fecha y hora de fin
		$end_date = sanitize_text_field( $_POST['ige_end_date'] ?? '' );
		$end_time = sanitize_text_field( $_POST['ige_end_time'] ?? '' );
		if ( ! empty( $end_date ) ) {
			$end = ! empty( $end_time ) ? "$end_date $end_time" : "$end_date 23:59";
			update_post_meta( $post_id, 'ige_end', $end );
		} else {
			delete_post_meta( $post_id, 'ige_end' );
		}

		// Checkboxes
		update_post_meta( $post_id, 'ige_all_day', ! empty( $_POST['ige_all_day'] ) ? '1' : '0' );
		update_post_meta( $post_id, 'ige_featured', ! empty( $_POST['ige_featured'] ) ? '1' : '0' );
		update_post_meta( $post_id, 'ige_countdown', ! empty( $_POST['ige_countdown'] ) ? '1' : '0' );
		update_post_meta( $post_id, 'ige_calendar_button', ! empty( $_POST['ige_calendar_button'] ) ? '1' : '0' );
		update_post_meta( $post_id, 'ige_hide_after_end', ! empty( $_POST['ige_hide_after_end'] ) ? '1' : '0' );

		// Textos
		if ( isset( $_POST['ige_kicker'] ) ) {
			update_post_meta( $post_id, 'ige_kicker', sanitize_text_field( $_POST['ige_kicker'] ) );
		}
		if ( isset( $_POST['ige_location_name'] ) ) {
			update_post_meta( $post_id, 'ige_location_name', sanitize_text_field( $_POST['ige_location_name'] ) );
		}
		if ( isset( $_POST['ige_address'] ) ) {
			update_post_meta( $post_id, 'ige_address', sanitize_text_field( $_POST['ige_address'] ) );
		}
		if ( isset( $_POST['ige_maps_url'] ) ) {
			update_post_meta( $post_id, 'ige_maps_url', esc_url_raw( $_POST['ige_maps_url'] ) );
		}
		if ( isset( $_POST['ige_image_fit'] ) ) {
			$fit = sanitize_key( $_POST['ige_image_fit'] );
			update_post_meta( $post_id, 'ige_image_fit', in_array( $fit, [ 'contain', 'cover' ], true ) ? $fit : 'contain' );
		}
		if ( isset( $_POST['ige_gallery_mode'] ) ) {
			$mode = sanitize_key( $_POST['ige_gallery_mode'] );
			update_post_meta( $post_id, 'ige_gallery_mode', in_array( $mode, [ 'slider', 'grid' ], true ) ? $mode : 'slider' );
		}

		// Galería
		if ( isset( $_POST['ige_gallery'] ) ) {
			$clean_ids = implode( ',', array_filter( array_map( 'absint', explode( ',', $_POST['ige_gallery'] ) ) ) );
			update_post_meta( $post_id, 'ige_gallery', $clean_ids );
		}

		// Botones JSON
		if ( isset( $_POST['ige_buttons'] ) ) {
			$raw     = wp_unslash( $_POST['ige_buttons'] );
			$raw     = self::repair_unicode_string( (string) $raw );
			$decoded = json_decode( $raw, true );
			$clean   = [];
			if ( is_array( $decoded ) ) {
				foreach ( $decoded as $b ) {
					if ( empty( $b['text'] ) && empty( $b['url'] ) ) continue;
					$btn_text = self::repair_unicode_string( sanitize_text_field( $b['text'] ?? '' ) );
					$clean[] = [
						'text'    => $btn_text,
						'url'     => esc_url_raw( $b['url'] ?? '' ),
						'new_tab' => ! empty( $b['new_tab'] ),
						'style'   => in_array( $b['style'] ?? '', [ 'primary', 'secondary' ], true ) ? $b['style'] : 'primary',
					];
				}
			}
			update_post_meta( $post_id, 'ige_buttons', wp_slash( wp_json_encode( $clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
		}
	}

	public static function manage_columns( array $columns ): array {
		$new_cols = [];
		foreach ( $columns as $k => $v ) {
			$new_cols[ $k ] = $v;
			if ( $k === 'title' ) {
				$new_cols['ige_thumb']     = __( 'Cartel', 'iglesia-eventos' );
				$new_cols['ige_date']      = __( 'Fecha Evento', 'iglesia-eventos' );
				$new_cols['ige_location']  = __( 'Lugar', 'iglesia-eventos' );
				$new_cols['ige_featured']  = __( '⭐ Destacado', 'iglesia-eventos' );
				$new_cols['ige_countdown'] = __( '⏱ Cuenta Atrás', 'iglesia-eventos' );
				$new_cols['ige_status']    = __( 'Estado Vigencia', 'iglesia-eventos' );
			}
		}
		return $new_cols;
	}

	public static function render_column( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'ige_thumb':
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, [ 50, 50 ], [ 'style' => 'border-radius: 4px; object-fit: contain;' ] );
				} else {
					$gallery = get_post_meta( $post_id, 'ige_gallery', true );
					$ids     = array_filter( array_map( 'absint', explode( ',', $gallery ) ) );
					if ( ! empty( $ids ) ) {
						echo wp_get_attachment_image( $ids[0], [ 50, 50 ], false, [ 'style' => 'border-radius: 4px; object-fit: contain;' ] );
					} else {
						echo '<span style="color:#aaa;">—</span>';
					}
				}
				break;

			case 'ige_date':
				$start = get_post_meta( $post_id, 'ige_start', true );
				if ( ! empty( $start ) ) {
					$dt = date_create( $start );
					echo $dt ? esc_html( date_format( $dt, 'd/m/Y H:i' ) ) : esc_html( $start );
				} else {
					echo '<span style="color:#aaa;">Sin fecha</span>';
				}
				break;

			case 'ige_location':
				$loc = get_post_meta( $post_id, 'ige_location_name', true );
				echo $loc ? esc_html( $loc ) : '<span style="color:#aaa;">—</span>';
				break;

			case 'ige_featured':
				$feat = get_post_meta( $post_id, 'ige_featured', true ) === '1';
				echo $feat ? '<span style="color: #ca8a04; font-weight: bold; font-size: 16px;">★ Sí</span>' : '<span style="color:#9ca3af;">No</span>';
				break;

			case 'ige_countdown':
				$cd = get_post_meta( $post_id, 'ige_countdown', true ) === '1';
				echo $cd ? '<span style="color: #2563eb; font-weight: bold;">Activo</span>' : '<span style="color:#9ca3af;">No</span>';
				break;

			case 'ige_status':
				$start = get_post_meta( $post_id, 'ige_start', true );
				$end   = get_post_meta( $post_id, 'ige_end', true ) ?: ( ! empty( $start ) ? explode( ' ', $start )[0] . ' 23:59' : '' );
				$now   = current_time( 'Y-m-d H:i' );
				if ( empty( $start ) ) {
					echo '<span class="badge" style="background:#e5e7eb; color:#374151; padding:2px 6px; border-radius:4px;">Sin fecha</span>';
				} elseif ( $end < $now ) {
					echo '<span class="badge" style="background:#fee2e2; color:#b91c1c; padding:2px 6px; border-radius:4px;">Finalizado</span>';
				} else {
					echo '<span class="badge" style="background:#dcfce7; color:#15803d; padding:2px 6px; border-radius:4px; font-weight:600;">Próximo</span>';
				}
				break;
		}
	}

	public static function sortable_columns( array $columns ): array {
		$columns['ige_date'] = 'ige_date';
		return $columns;
	}

	public static function order_by_event_date( WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( $query->get( 'post_type' ) === 'iglesia_evento' && ! $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', 'ige_start' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
		}
	}
}
