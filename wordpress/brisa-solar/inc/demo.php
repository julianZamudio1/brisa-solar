<?php
/**
 * Importa el contenido de demostración (el mismo que la landing en Astro).
 * Se ejecuta desde el blueprint de WordPress Playground o con WP-CLI:
 *   wp eval 'brisa_importar_demo();'
 *
 * @package brisa-solar
 */

defined( 'ABSPATH' ) || exit;

function brisa_importar_demo(): void {
	if ( get_option( 'brisa_demo_importada' ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$demo = require BRISA_DIR . '/inc/generado/demo.php';

	// Páginas: portada, agradecimiento y aviso de privacidad.
	$inicio = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => 'Inicio',
			'post_name'   => 'inicio',
		)
	);
	wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => 'Recibimos tu solicitud',
			'post_name'   => 'gracias',
		)
	);
	$aviso = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Aviso de privacidad',
			'post_name'    => 'aviso-de-privacidad',
			'post_content' => (string) file_get_contents( BRISA_DIR . '/demo/aviso-de-privacidad.html' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		)
	);
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $inicio );
	update_option( 'wp_page_for_privacy_policy', $aviso );
	update_option( 'blogname', 'Brisa Solar' );
	update_option( 'blogdescription', 'Paneles solares en Querétaro' );
	update_option( 'permalink_structure', '/%postname%/' );
	update_option( 'timezone_string', 'America/Mexico_City' );
	flush_rewrite_rules();

	foreach ( $demo['preguntas'] as $orden => $p ) {
		wp_insert_post(
			array(
				'post_type'    => 'brisa_pregunta',
				'post_status'  => 'publish',
				'post_title'   => $p['pregunta'],
				'post_content' => $p['respuesta'],
				'menu_order'   => $orden,
			)
		);
	}

	foreach ( $demo['testimonios'] as $orden => $t ) {
		wp_insert_post(
			array(
				'post_type'    => 'brisa_testimonio',
				'post_status'  => 'publish',
				'post_title'   => $t['nombre'],
				'post_content' => $t['texto'],
				'menu_order'   => $orden,
				'meta_input'   => array(
					'detalle'   => $t['detalle'],
					'destacado' => $t['destacado'],
				),
			)
		);
	}

	foreach ( $demo['proyectos'] as $orden => $p ) {
		$id = wp_insert_post(
			array(
				'post_type'   => 'brisa_proyecto',
				'post_status' => 'publish',
				'post_title'  => $p['lugar'],
				'menu_order'  => $orden,
				'meta_input'  => array(
					'kwp'     => $p['kwp'],
					'paneles' => $p['paneles'],
					'antes'   => $p['antes'],
					'despues' => $p['despues'],
				),
			)
		);
		// media_handle_sideload() mueve el archivo: se trabaja sobre una copia temporal.
		$temporal = wp_tempnam( $p['foto'] . '.jpg' );
		copy( BRISA_DIR . '/demo/fotos/' . $p['foto'] . '.jpg', $temporal );
		$adjunto = media_handle_sideload(
			array(
				'name'     => $p['foto'] . '.jpg',
				'tmp_name' => $temporal,
			),
			$id,
			$p['alt']
		);
		if ( ! is_wp_error( $adjunto ) ) {
			update_post_meta( $adjunto, '_wp_attachment_image_alt', $p['alt'] );
			set_post_thumbnail( $id, $adjunto );
		}
	}

	update_option( 'brisa_demo_importada', time() );
}
