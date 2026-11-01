<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IGE_Elementor {

	public static function init(): void {
		add_action( 'elementor/widgets/register', [ __CLASS__, 'register_widget' ] );
	}

	public static function register_widget( $widgets_manager ): void {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		require_once __DIR__ . '/class-ige-elementor-widget.php';
		$widgets_manager->register( new IGE_Elementor_Widget() );
	}
}
