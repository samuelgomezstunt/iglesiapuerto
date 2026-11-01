<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class IGE_Elementor_Widget extends Widget_Base {

	public function get_name(): string {
		return 'iglesia_eventos';
	}

	public function get_title(): string {
		return __( 'Iglesia · Próximos Eventos', 'iglesia-eventos' );
	}

	public function get_icon(): string {
		return 'eicon-calendar';
	}

	public function get_categories(): array {
		return [ 'general' ];
	}

	public function get_keywords(): array {
		return [ 'eventos', 'iglesia', 'calendario', 'navidad', 'retiros', 'campamentos', 'countdown' ];
	}

	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Configuración de Eventos', 'iglesia-eventos' ),
			]
		);

		$this->add_control(
			'layout',
			[
				'label'   => __( 'Diseño de la Sección', 'iglesia-eventos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => [
					'auto'          => __( 'Automático (Destacado arriba + Rejilla debajo)', 'iglesia-eventos' ),
					'featured_only' => __( 'Solo Eventos Destacados (Ancho completo)', 'iglesia-eventos' ),
					'grid_only'     => __( 'Solo Rejilla de Tarjetas', 'iglesia-eventos' ),
				],
			]
		);

		$this->add_control(
			'columns',
			[
				'label'     => __( 'Columnas en Rejilla', 'iglesia-eventos' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '3',
				'options'   => [
					'2' => __( '2 Columnas', 'iglesia-eventos' ),
					'3' => __( '3 Columnas', 'iglesia-eventos' ),
					'4' => __( '4 Columnas', 'iglesia-eventos' ),
				],
				'condition' => [
					'layout!' => 'featured_only',
				],
			]
		);

		$this->add_control(
			'limit',
			[
				'label'   => __( 'Número Máximo de Eventos', 'iglesia-eventos' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 30,
			]
		);

		$this->add_control(
			'show_past',
			[
				'label'        => __( 'Mostrar Eventos Pasados', 'iglesia-eventos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Sí', 'iglesia-eventos' ),
				'label_off'    => __( 'No', 'iglesia-eventos' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		echo IGE_Shortcode::render_shortcode( [
			'layout'    => $settings['layout'] ?? 'auto',
			'columns'   => $settings['columns'] ?? 3,
			'limit'     => $settings['limit'] ?? 6,
			'show_past' => $settings['show_past'] ?? 'no',
		] );
	}
}
