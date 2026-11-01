<?php
/**
 * Plugin Name: Iglesia · Próximos Eventos
 * Description: Sistema dinámico de eventos, campamentos, retiros y celebraciones con temporizador en vivo, sección destacada a ancho completo, galería slider y botones personalizables.
 * Version: 1.0.0
 * Author: Iglesia
 * Text Domain: iglesia-eventos
 * Network: true
 * Requires PHP: 8.1
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IGE_VERSION', '1.0.2' );
define( 'IGE_PATH', plugin_dir_path( __FILE__ ) );
define( 'IGE_URL', plugin_dir_url( __FILE__ ) );

require_once IGE_PATH . 'includes/class-ige-cpt.php';
require_once IGE_PATH . 'includes/class-ige-query.php';
require_once IGE_PATH . 'includes/class-ige-shortcode.php';
require_once IGE_PATH . 'includes/class-ige-elementor.php';

if ( is_admin() ) {
	require_once IGE_PATH . 'includes/class-ige-admin.php';
}

register_activation_hook( __FILE__, function() {
	IGE_CPT::register_post_type();
	IGE_CPT::register_taxonomy();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function() {
	flush_rewrite_rules();
} );

add_action( 'plugins_loaded', function() {
	IGE_CPT::init();
	IGE_Shortcode::init();
	IGE_Elementor::init();

	if ( is_admin() ) {
		IGE_Admin::init();
	}
} );
