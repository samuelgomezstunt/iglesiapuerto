<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IGE_CPT {

	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_post_type' ] );
		add_action( 'init', [ __CLASS__, 'register_taxonomy' ] );
		add_action( 'init', [ __CLASS__, 'register_meta_fields' ] );
	}

	public static function register_post_type(): void {
		$labels = [
			'name'                  => _x( 'Eventos', 'Post Type General Name', 'iglesia-eventos' ),
			'singular_name'         => _x( 'Evento', 'Post Type Singular Name', 'iglesia-eventos' ),
			'menu_name'             => __( 'Eventos', 'iglesia-eventos' ),
			'name_admin_bar'        => __( 'Evento', 'iglesia-eventos' ),
			'archives'              => __( 'Archivo de Eventos', 'iglesia-eventos' ),
			'attributes'            => __( 'Atributos de Evento', 'iglesia-eventos' ),
			'parent_item_colon'     => __( 'Evento superior:', 'iglesia-eventos' ),
			'all_items'             => __( 'Todos los Eventos', 'iglesia-eventos' ),
			'add_new_item'          => __( 'Añadir Nuevo Evento', 'iglesia-eventos' ),
			'add_new'               => __( 'Añadir Nuevo', 'iglesia-eventos' ),
			'new_item'              => __( 'Nuevo Evento', 'iglesia-eventos' ),
			'edit_item'             => __( 'Editar Evento', 'iglesia-eventos' ),
			'update_item'           => __( 'Actualizar Evento', 'iglesia-eventos' ),
			'view_item'             => __( 'Ver Evento', 'iglesia-eventos' ),
			'view_items'            => __( 'Ver Eventos', 'iglesia-eventos' ),
			'search_items'          => __( 'Buscar Eventos', 'iglesia-eventos' ),
			'not_found'             => __( 'No se encontraron eventos', 'iglesia-eventos' ),
			'not_found_in_trash'    => __( 'No se encontraron eventos en la papelera', 'iglesia-eventos' ),
			'featured_image'        => __( 'Cartel / Imagen Destacada', 'iglesia-eventos' ),
			'set_featured_image'    => __( 'Asignar cartel / imagen', 'iglesia-eventos' ),
			'remove_featured_image' => __( 'Quitar cartel', 'iglesia-eventos' ),
			'use_featured_image'    => __( 'Usar como cartel del evento', 'iglesia-eventos' ),
		];

		$args = [
			'label'               => __( 'Evento', 'iglesia-eventos' ),
			'description'         => __( 'Eventos, campamentos, retiros y celebraciones de la iglesia', 'iglesia-eventos' ),
			'labels'              => $labels,
			'supports'            => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
			'taxonomies'          => [ 'iglesia_evento_tipo' ],
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 21,
			'menu_icon'           => 'dashicons-calendar-alt',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => 'eventos',
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
			'rewrite'             => [ 'slug' => 'evento', 'with_front' => false ],
		];

		register_post_type( 'iglesia_evento', $args );
	}

	public static function register_taxonomy(): void {
		$labels = [
			'name'              => _x( 'Tipos de Evento', 'taxonomy general name', 'iglesia-eventos' ),
			'singular_name'     => _x( 'Tipo de Evento', 'taxonomy singular name', 'iglesia-eventos' ),
			'search_items'      => __( 'Buscar Tipos', 'iglesia-eventos' ),
			'all_items'         => __( 'Todos los Tipos', 'iglesia-eventos' ),
			'parent_item'       => __( 'Tipo Superior', 'iglesia-eventos' ),
			'parent_item_colon' => __( 'Tipo Superior:', 'iglesia-eventos' ),
			'edit_item'         => __( 'Editar Tipo', 'iglesia-eventos' ),
			'update_item'       => __( 'Actualizar Tipo', 'iglesia-eventos' ),
			'add_new_item'      => __( 'Añadir Nuevo Tipo de Evento', 'iglesia-eventos' ),
			'new_item_name'     => __( 'Nuevo Nombre de Tipo', 'iglesia-eventos' ),
			'menu_name'         => __( 'Tipos de Evento', 'iglesia-eventos' ),
		];

		register_taxonomy( 'iglesia_evento_tipo', [ 'iglesia_evento' ], [
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_rest'      => true,
			'rewrite'           => [ 'slug' => 'tipo-evento' ],
		] );

		// Añadir términos iniciales si no existen
		if ( ! get_option( 'ige_terms_initialized' ) ) {
			$defaults = [ 'Celebraciones', 'Retiros', 'Campamentos', 'Conferencias', 'Jóvenes', 'Otros' ];
			foreach ( $defaults as $term ) {
				if ( ! term_exists( $term, 'iglesia_evento_tipo' ) ) {
					wp_insert_term( $term, 'iglesia_evento_tipo' );
				}
			}
			update_option( 'ige_terms_initialized', 1 );
		}
	}

	public static function register_meta_fields(): void {
		$string_fields = [
			'ige_start',
			'ige_end',
			'ige_kicker',
			'ige_location_name',
			'ige_address',
			'ige_maps_url',
			'ige_gallery',
			'ige_gallery_mode',
			'ige_image_fit',
			'ige_buttons',
			'ige_accent',
		];

		foreach ( $string_fields as $field ) {
			register_post_meta( 'iglesia_evento', $field, [
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => fn() => current_user_can( 'edit_posts' ),
			] );
		}

		$bool_fields = [
			'ige_all_day',
			'ige_featured',
			'ige_countdown',
			'ige_calendar_button',
			'ige_hide_after_end',
		];

		foreach ( $bool_fields as $field ) {
			register_post_meta( 'iglesia_evento', $field, [
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => fn() => current_user_can( 'edit_posts' ),
			] );
		}
	}
}
