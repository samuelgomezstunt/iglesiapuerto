<?php
/**
 * Script de Despliegue Dinámico de Eventos en WordPress (Standalone o Multisite)
 *
 * Uso con WP-CLI:
 *   wp eval-file deploy_eventos.php
 *
 * O ejecutando directamente con PHP cargando el entorno de WordPress:
 *   php deploy_eventos.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    $wp_load_candidates = [
        __DIR__ . '/../wp-load.php',
        __DIR__ . '/../../wp-load.php',
        dirname( __DIR__, 2 ) . '/wp-load.php',
        'D:/laragon/www/Iglesia/wp-load.php',
    ];
    $loaded = false;
    foreach ( $wp_load_candidates as $cand ) {
        if ( file_exists( $cand ) ) {
            require_once $cand;
            $loaded = true;
            break;
        }
    }
    if ( ! $loaded ) {
        die( "Error: No se pudo localizar wp-load.php. Ejecute el script dentro del directorio de WordPress o vía WP-CLI.\n" );
    }
}

echo "=== Despliegue de Sistema de Eventos (iglesia-eventos) ===\n";

// Activar plugin
if ( is_multisite() ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    activate_plugin( 'iglesia-eventos/iglesia-eventos.php', '', true );
    echo "[OK] Plugin activado en la red multisite.\n";
} else {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    activate_plugin( 'iglesia-eventos/iglesia-eventos.php' );
    echo "[OK] Plugin activado.\n";
}

function deploy_eventos_for_current_blog() {
    $blog_id = get_current_blog_id();
    echo "\n--- Configurando Sitio {$blog_id} ---\n";

    // 1. Imagen Cena de Navidad
    $navidad_media_id = 0;
    $existing_navidad_media = get_posts([
        'post_type'   => 'attachment',
        'title'       => 'Cena de Navidad 2026',
        'post_status' => 'inherit',
        'numberposts' => 1,
    ]);

    if ( ! empty( $existing_navidad_media ) ) {
        $navidad_media_id = $existing_navidad_media[0]->ID;
        echo "  [Media] Imagen 'Cena de Navidad' ya existente: ID {$navidad_media_id}\n";
    } else {
        $source_img = WP_CONTENT_DIR . '/uploads/2026/10/cena-navidad-2026.png';
        if ( file_exists( $source_img ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $upload_dir = wp_upload_dir();
            $filename   = wp_unique_filename( $upload_dir['path'], 'cena-navidad-2026.png' );
            $new_file   = $upload_dir['path'] . '/' . $filename;
            copy( $source_img, $new_file );

            $wp_filetype = wp_check_filetype( $filename, null );
            $attachment  = [
                'post_mime_type' => $wp_filetype['type'],
                'post_title'     => 'Cena de Navidad 2026',
                'post_content'   => '',
                'post_status'    => 'inherit'
            ];
            $navidad_media_id = wp_insert_attachment( $attachment, $new_file );
            $attach_data      = wp_generate_attachment_metadata( $navidad_media_id, $new_file );
            wp_update_attachment_metadata( $navidad_media_id, $attach_data );
            update_post_meta( $navidad_media_id, '_wp_attachment_image_alt', 'Invitación a la cena de Navidad: 19 de diciembre a las 20:00 en Calle Don Quijote 42, esquina La Mancha, Puerto del Rosario' );
            echo "  [Media] Imagen importada con éxito: ID {$navidad_media_id}\n";
        } else {
            echo "  [Aviso] Imagen 'cena-navidad-2026.png' no encontrada en uploads.\n";
        }
    }

    // 2. Evento Destacado: Cena de Navidad
    $existing_navidad = get_posts([
        'post_type'   => 'iglesia_evento',
        'name'        => 'cena-de-navidad-2026',
        'post_status' => 'any',
        'numberposts' => 1,
    ]);

    if ( ! empty( $existing_navidad ) ) {
        $navidad_id = $existing_navidad[0]->ID;
        echo "  [Evento] 'Cena de Navidad' ya existente: ID {$navidad_id}. Actualizando metadatos...\n";
    } else {
        $navidad_id = wp_insert_post([
            'post_type'    => 'iglesia_evento',
            'post_title'   => 'Cena de Navidad',
            'post_name'    => 'cena-de-navidad-2026',
            'post_status'  => 'publish',
            'post_excerpt' => 'Ven a celebrar y recordar con nosotros el sentido y propósito de la Navidad.',
            'post_content' => '<p>Te invitamos a la cena de Navidad. Ven a celebrar y recordar con nosotros el sentido y propósito de la Navidad.</p><p><strong>19 de diciembre | 20:00</strong><br>Calle Don Quijote, 42, esquina La Mancha (Puerto del Rosario)</p>',
        ]);
        echo "  [Evento] 'Cena de Navidad' creado: ID {$navidad_id}\n";
    }

    $buttons = [
        [
            'text'     => 'Cómo llegar',
            'url'      => 'https://maps.google.com/?q=Calle+Don+Quijote+42+Puerto+del+Rosario',
            'new_tab'  => true,
            'style'    => 'primary',
        ],
    ];

    update_post_meta( $navidad_id, 'ige_kicker', 'Te invitamos' );
    update_post_meta( $navidad_id, 'ige_start', '2026-12-19 20:00' );
    update_post_meta( $navidad_id, 'ige_end', '2026-12-19 23:30' );
    update_post_meta( $navidad_id, 'ige_all_day', '0' );
    update_post_meta( $navidad_id, 'ige_location_name', 'Iglesia de Puerto del Rosario' );
    update_post_meta( $navidad_id, 'ige_address', 'Calle Don Quijote, 42, esquina La Mancha (Puerto del Rosario)' );
    update_post_meta( $navidad_id, 'ige_maps_url', 'https://maps.google.com/?q=Calle+Don+Quijote+42+Puerto+del+Rosario' );
    update_post_meta( $navidad_id, 'ige_featured', '1' );
    update_post_meta( $navidad_id, 'ige_countdown', '1' );
    update_post_meta( $navidad_id, 'ige_calendar_button', '1' );
    update_post_meta( $navidad_id, 'ige_hide_after_end', '1' );
    update_post_meta( $navidad_id, 'ige_image_fit', 'contain' );
    update_post_meta( $navidad_id, 'ige_buttons', wp_json_encode( $buttons ) );
    if ( $navidad_media_id > 0 ) {
        update_post_meta( $navidad_id, '_thumbnail_id', $navidad_media_id );
        update_post_meta( $navidad_id, 'ige_gallery', (string) $navidad_media_id );
        update_post_meta( $navidad_id, 'ige_gallery_mode', 'slider' );
    }

    // 3. Evento No Destacado: 3º Retiro de Mujeres
    $existing_retiro = get_posts([
        'post_type'   => 'iglesia_evento',
        'name'        => '3o-retiro-de-mujeres',
        'post_status' => 'any',
        'numberposts' => 1,
    ]);

    if ( ! empty( $existing_retiro ) ) {
        $retiro_id = $existing_retiro[0]->ID;
        echo "  [Evento] '3º Retiro de Mujeres' ya existente: ID {$retiro_id}. Actualizando metadatos...\n";
    } else {
        $retiro_id = wp_insert_post([
            'post_type'    => 'iglesia_evento',
            'post_title'   => '3º Retiro de Mujeres',
            'post_name'    => '3o-retiro-de-mujeres',
            'post_status'  => 'publish',
            'post_excerpt' => 'Retiro de mujeres de las Asambleas de Hermanos de Canarias.',
            'post_content' => '<p>3º Retiro de Mujeres en el Hotel Valle Orotava (Tenerife).</p>',
        ]);
        echo "  [Evento] '3º Retiro de Mujeres' creado: ID {$retiro_id}\n";
    }

    $retiro_buttons = [
        [
            'text'     => 'Inscribirme en el Retiro',
            'url'      => 'https://forms.gle/iwLbyra2NkK95Ens6',
            'new_tab'  => true,
            'style'    => 'primary',
        ],
    ];

    update_post_meta( $retiro_id, 'ige_kicker', 'Asambleas de Hermanos de Canarias' );
    update_post_meta( $retiro_id, 'ige_start', '2026-10-02 16:00' );
    update_post_meta( $retiro_id, 'ige_end', '2026-10-04 18:00' );
    update_post_meta( $retiro_id, 'ige_all_day', '0' );
    update_post_meta( $retiro_id, 'ige_location_name', 'Hotel Valle Orotava – Tenerife' );
    update_post_meta( $retiro_id, 'ige_address', 'Hotel Valle Orotava – Tenerife' );
    update_post_meta( $retiro_id, 'ige_featured', '0' );
    update_post_meta( $retiro_id, 'ige_countdown', '1' );
    update_post_meta( $retiro_id, 'ige_calendar_button', '1' );
    update_post_meta( $retiro_id, 'ige_hide_after_end', '1' );
    update_post_meta( $retiro_id, 'ige_image_fit', 'contain' );
    update_post_meta( $retiro_id, 'ige_buttons', wp_json_encode( $retiro_buttons ) );

    // 4. Integrar shortcode en la página de Inicio de Elementor si procede
    $home_page = get_posts([
        'post_type'   => 'page',
        'title'       => 'Inicio',
        'post_status' => 'publish',
        'numberposts' => 1,
    ]);

    if ( ! empty( $home_page ) ) {
        $page_id = $home_page[0]->ID;
        $elem_raw = get_post_meta( $page_id, '_elementor_data', true );
        if ( ! empty( $elem_raw ) && strpos( $elem_raw, '[iglesia_eventos]' ) === false ) {
            $elements = json_decode( $elem_raw, true );
            if ( is_array( $elements ) ) {
                // Reemplazo en el contenedor
                $replaced = false;
                $replace_fn = function( &$items ) use ( &$replace_fn, &$replaced ) {
                    foreach ( $items as &$it ) {
                        if ( isset( $it['id'] ) && $it['id'] === '1545f73' && ! empty( $it['elements'] ) ) {
                            $new_children = [];
                            foreach ( $it['elements'] as $child ) {
                                if ( $child['id'] === '782bc84' ) {
                                    $new_children[] = [
                                        'id'         => substr( md5( uniqid( (string) mt_rand(), true ) ), 0, 7 ),
                                        'elType'     => 'widget',
                                        'widgetType' => 'shortcode',
                                        'settings'   => [ 'shortcode' => '[iglesia_eventos]' ],
                                        'elements'   => []
                                    ];
                                    $replaced = true;
                                } else {
                                    $new_children[] = $child;
                                }
                            }
                            $it['elements'] = $new_children;
                            return true;
                        }
                        if ( ! empty( $it['elements'] ) ) {
                            $res = $replace_fn( $it['elements'] );
                            if ( $res ) return true;
                        }
                    }
                    return false;
                };

                $replace_fn( $elements );

                if ( $replaced ) {
                    update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
                    delete_post_meta( $page_id, '_elementor_css' );
                    echo "  [Elementor] Widget [iglesia_eventos] integrado en la página de Inicio (ID {$page_id}).\n";
                }
            }
        } else {
            echo "  [Elementor] Página de Inicio ya contiene [iglesia_eventos].\n";
        }
    }
}

if ( is_multisite() ) {
    $sites = get_sites();
    foreach ( $sites as $s ) {
        switch_to_blog( (int) $s->blog_id );
        deploy_eventos_for_current_blog();
        restore_current_blog();
    }
} else {
    deploy_eventos_for_current_blog();
}

echo "\n[FIN] Despliegue completado con éxito.\n";
