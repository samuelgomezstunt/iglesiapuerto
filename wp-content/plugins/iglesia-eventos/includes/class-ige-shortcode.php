<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IGE_Shortcode {

	public static function init(): void {
		add_shortcode( 'iglesia_eventos', [ __CLASS__, 'render_shortcode' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'register_assets' ] );
	}

	public static function register_assets(): void {
		wp_register_style(
			'ige-eventos-css',
			IGE_URL . 'assets/css/eventos.css',
			[],
			IGE_VERSION
		);

		wp_register_script(
			'ige-eventos-js',
			IGE_URL . 'assets/js/eventos.js',
			[],
			IGE_VERSION,
			true
		);

		wp_localize_script( 'ige-eventos-js', 'igeSettings', [
			'timezone'    => get_option( 'timezone_string', 'Atlantic/Canary' ),
			'expiredText' => __( '¡El evento ha comenzado!', 'iglesia-eventos' ),
			'todayText'   => __( '¡Hoy!', 'iglesia-eventos' ),
		] );
	}

	public static function render_shortcode( $atts = [] ): string {
		$args = shortcode_atts( [
			'layout'         => 'auto',       // 'auto' | 'featured_only' | 'grid_only'
			'limit'          => 6,
			'featured'       => 'include',    // 'include' | 'only' | 'exclude'
			'category'       => '',
			'columns'        => 3,            // 2 | 3 | 4
			'show_past'      => 'no',         // 'yes' | 'no'
			'empty_text'     => __( 'Pronto anunciaremos nuevos eventos especiales. ¡Permanece atento!', 'iglesia-eventos' ),
			'regular_title'  => __( 'Otros eventos próximamente', 'iglesia-eventos' ),
		], $atts, 'iglesia_eventos' );

		wp_enqueue_style( 'ige-eventos-css' );
		wp_enqueue_script( 'ige-eventos-js' );

		$query_data = IGE_Query::get_events( [
			'limit'        => (int) $args['limit'],
			'featured'     => $args['featured'],
			'category'     => sanitize_text_field( $args['category'] ),
			'include_past' => $args['show_past'] === 'yes',
		] );

		$featured = $query_data['featured'];
		$regular  = $query_data['regular'];
		$all      = $query_data['all'];

		if ( empty( $all ) ) {
			return sprintf(
				'<div class="ige-empty-notice"><p>%s</p></div>',
				esc_html( $args['empty_text'] )
			);
		}

		ob_start();
		?>
		<div class="ige-events-wrapper layout-<?php echo esc_attr( $args['layout'] ); ?> cols-<?php echo esc_attr( (string) $args['columns'] ); ?>"
			data-empty-text="<?php echo esc_attr( $args['empty_text'] ); ?>">

			<?php if ( $args['layout'] === 'featured_only' ) : ?>
				<?php if ( ! empty( $featured ) ) : ?>
					<div class="ige-featured-section">
						<?php foreach ( $featured as $event ) :
							include IGE_PATH . 'templates/featured.php';
						endforeach; ?>
					</div>
				<?php endif; ?>

			<?php elseif ( $args['layout'] === 'grid_only' ) : ?>
				<div class="ige-regular-section">
					<?php if ( ! empty( $args['regular_title'] ) ) : ?>
						<h4 class="ige-regular-title"><?php echo esc_html( $args['regular_title'] ); ?></h4>
					<?php endif; ?>
					<div class="ige-grid-section ige-cols-<?php echo esc_attr( (string) $args['columns'] ); ?>">
						<?php foreach ( $all as $event ) :
							include IGE_PATH . 'templates/card.php';
						endforeach; ?>
					</div>
				</div>

			<?php else : // 'auto' ?>
				<?php if ( ! empty( $featured ) ) : ?>
					<div class="ige-featured-section">
						<?php foreach ( $featured as $event ) :
							include IGE_PATH . 'templates/featured.php';
						endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $regular ) ) : ?>
					<div class="ige-regular-section">
						<?php if ( ! empty( $args['regular_title'] ) ) : ?>
							<h4 class="ige-regular-title"><?php echo esc_html( $args['regular_title'] ); ?></h4>
						<?php endif; ?>
						<div class="ige-grid-section ige-cols-<?php echo esc_attr( (string) $args['columns'] ); ?>">
							<?php foreach ( $regular as $event ) :
								include IGE_PATH . 'templates/card.php';
							endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>

		</div>
		<?php
		return ob_get_clean() ?: '';
	}
}
