<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IGE_Query {

	/**
	 * Consulta los eventos según criterios y devuelve estructura normalizada.
	 *
	 * @param array $args
	 * @return array{featured: array, regular: array, all: array}
	 */
	public static function get_events( array $args = [] ): array {
		$defaults = [
			'limit'        => 10,
			'featured'     => 'include', // 'include' | 'only' | 'exclude'
			'category'     => '',
			'include_past' => false,
			'post_status'  => 'publish',
		];
		$args     = wp_parse_args( $args, $defaults );

		$tz         = wp_timezone();
		$now_dt     = new DateTime( 'now', $tz );
		$now_str    = $now_dt->format( 'Y-m-d H:i' );
		$today_str  = $now_dt->format( 'Y-m-d' );

		$query_args = [
			'post_type'      => 'iglesia_evento',
			'post_status'    => $args['post_status'],
			'posts_per_page' => $args['limit'],
			'meta_key'       => 'ige_start',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => [],
		];

		if ( ! empty( $args['category'] ) ) {
			$query_args['tax_query'] = [
				[
					'taxonomy' => 'iglesia_evento_tipo',
					'field'    => is_numeric( $args['category'] ) ? 'term_id' : 'slug',
					'terms'    => $args['category'],
				],
			];
		}

		if ( $args['featured'] === 'only' ) {
			$query_args['meta_query'][] = [
				'key'     => 'ige_featured',
				'value'   => '1',
				'compare' => '=',
			];
		} elseif ( $args['featured'] === 'exclude' ) {
			$query_args['meta_query'][] = [
				'relation' => 'OR',
				[
					'key'     => 'ige_featured',
					'value'   => '1',
					'compare' => '!=',
				],
				[
					'key'     => 'ige_featured',
					'compare' => 'NOT EXISTS',
				],
			];
		}

		$q        = new WP_Query( $query_args );
		$featured = [];
		$regular  = [];
		$all      = [];

		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$post_id = get_the_ID();

				$start    = (string) get_post_meta( $post_id, 'ige_start', true );
				$end      = (string) get_post_meta( $post_id, 'ige_end', true );
				$all_day  = get_post_meta( $post_id, 'ige_all_day', true ) === '1';
				$is_feat  = get_post_meta( $post_id, 'ige_featured', true ) === '1';
				$hide_end = get_post_meta( $post_id, 'ige_hide_after_end', true ) !== '0';

				// Calcular fecha límite de vigencia
				$limit_dt_str = ! empty( $end ) ? $end : ( ! empty( $start ) ? explode( ' ', $start )[0] . ' 23:59' : '' );
				$is_past      = ! empty( $limit_dt_str ) && ( $limit_dt_str < $now_str );

				// Si no se deben incluir pasados y expiró, omitir
				if ( ! $args['include_past'] && $hide_end && $is_past ) {
					continue;
				}

				$event_data = self::format_event( $post_id, $start, $end, $all_day, $is_feat, $is_past, $tz );
				$all[]      = $event_data;

				if ( $is_feat && $args['featured'] !== 'exclude' ) {
					$featured[] = $event_data;
				} else {
					$regular[] = $event_data;
				}
			}
			wp_reset_postdata();
		}

		return [
			'featured' => $featured,
			'regular'  => $regular,
			'all'      => $all,
		];
	}

	private static function format_event( int $post_id, string $start, string $end, bool $all_day, bool $is_feat, bool $is_past, DateTimeZone $tz ): array {
		$title         = get_the_title( $post_id );
		$kicker        = (string) get_post_meta( $post_id, 'ige_kicker', true );
		$location_name = (string) get_post_meta( $post_id, 'ige_location_name', true );
		$address       = (string) get_post_meta( $post_id, 'ige_address', true );
		$maps_url      = (string) get_post_meta( $post_id, 'ige_maps_url', true );
		$countdown     = get_post_meta( $post_id, 'ige_countdown', true ) === '1';
		$calendar_btn  = get_post_meta( $post_id, 'ige_calendar_button', true ) !== '0';
		$image_fit     = (string) ( get_post_meta( $post_id, 'ige_image_fit', true ) ?: 'contain' );
		$gallery_mode  = (string) ( get_post_meta( $post_id, 'ige_gallery_mode', true ) ?: 'slider' );
		$raw_buttons   = (string) get_post_meta( $post_id, 'ige_buttons', true );
		$buttons       = json_decode( $raw_buttons ?: '[]', true ) ?: [];

		if ( empty( $maps_url ) && ! empty( $address ) ) {
			$maps_url = 'https://maps.google.com/?q=' . rawurlencode( $address );
		}

		// Galería de imágenes
		$gallery_raw = (string) get_post_meta( $post_id, 'ige_gallery', true );
		$img_ids     = array_filter( array_map( 'absint', explode( ',', $gallery_raw ) ) );

		if ( has_post_thumbnail( $post_id ) ) {
			$thumb_id = get_post_thumbnail_id( $post_id );
			if ( $thumb_id && ! in_array( $thumb_id, $img_ids, true ) ) {
				array_unshift( $img_ids, $thumb_id );
			}
		}

		$images = [];
		foreach ( $img_ids as $id ) {
			$url  = wp_get_attachment_image_url( $id, 'large' );
			$full = wp_get_attachment_image_url( $id, 'full' );
			$alt  = get_post_meta( $id, '_wp_attachment_image_alt', true ) ?: $title;
			if ( $url ) {
				$images[] = [
					'id'   => $id,
					'url'  => $url,
					'full' => $full ?: $url,
					'alt'  => $alt,
				];
			}
		}

		// Convertir fechas a objetos y formatear
		$start_dt = ! empty( $start ) ? DateTime::createFromFormat( 'Y-m-d H:i', $start, $tz ) : null;
		$end_dt   = ! empty( $end ) ? DateTime::createFromFormat( 'Y-m-d H:i', $end, $tz ) : null;

		$iso_start = $start_dt ? $start_dt->format( 'c' ) : '';
		$iso_end   = $end_dt ? $end_dt->format( 'c' ) : ( $start_dt ? ( new DateTime( $start_dt->format( 'Y-m-d 23:59:59' ), $tz ) )->format( 'c' ) : '' );

		// Formato legible de fecha en español
		$human_date = self::get_human_date( $start_dt, $end_dt, $all_day );

		// Generador de Google Calendar URL
		$gcal_url = '';
		if ( $calendar_btn && $start_dt ) {
			$gcal_start = ( clone $start_dt )->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' );
			$target_end = $end_dt ?: ( clone $start_dt )->modify( '+3 hours' );
			$gcal_end   = ( clone $target_end )->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' );

			$gcal_params = [
				'action'   => 'TEMPLATE',
				'text'     => $title,
				'dates'    => "{$gcal_start}/{$gcal_end}",
				'details'  => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
				'location' => $address ?: $location_name,
			];
			$gcal_url = 'https://calendar.google.com/calendar/render?' . http_build_query( $gcal_params );
		}

		return [
			'id'                  => $post_id,
			'title'               => $title,
			'permalink'           => get_permalink( $post_id ),
			'kicker'              => $kicker,
			'excerpt'             => get_the_excerpt( $post_id ),
			'content'             => apply_filters( 'the_content', get_the_content( null, false, $post_id ) ),
			'location_name'       => $location_name,
			'address'             => $address,
			'maps_url'            => $maps_url,
			'start_str'           => $start,
			'end_str'             => $end,
			'iso_start'           => $iso_start,
			'iso_end'             => $iso_end,
			'human_date'          => $human_date,
			'all_day'             => $all_day,
			'is_featured'         => $is_feat,
			'countdown'           => $countdown,
			'calendar_button'     => $calendar_btn,
			'google_calendar_url' => $gcal_url,
			'image_fit'           => $image_fit,
			'gallery_mode'        => $gallery_mode,
			'images'              => $images,
			'buttons'             => $buttons,
			'is_past'             => $is_past,
		];
	}

	private static function get_human_date( ?DateTime $start, ?DateTime $end, bool $all_day ): array {
		if ( ! $start ) {
			return [ 'primary' => '', 'time' => '', 'badge_day' => '', 'badge_month' => '' ];
		}

		$months = [
			1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
			5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
			9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
		];
		$months_short = [
			1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR',
			5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
			9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC'
		];

		$day_num   = (int) $start->format( 'j' );
		$month_num = (int) $start->format( 'n' );
		$year      = $start->format( 'Y' );
		$time_str  = $all_day ? '' : $start->format( 'H:i' );

		if ( $end && $end->format( 'Y-m-d' ) !== $start->format( 'Y-m-d' ) ) {
			$end_day   = (int) $end->format( 'j' );
			$end_month = (int) $end->format( 'n' );
			if ( $month_num === $end_month ) {
				$primary = sprintf( 'Del %d al %d de %s de %s', $day_num, $end_day, $months[ $month_num ], $year );
			} else {
				$primary = sprintf( 'Del %d de %s al %d de %s de %s', $day_num, $months[ $month_num ], $end_day, $months[ $end_month ], $year );
			}
		} else {
			$primary = sprintf( '%d de %s', $day_num, $months[ $month_num ] );
		}

		return [
			'primary'     => $primary,
			'time'        => $time_str,
			'badge_day'   => str_pad( (string) $day_num, 2, '0', STR_PAD_LEFT ),
			'badge_month' => $months_short[ $month_num ] ?? '',
		];
	}
}
