<?php
/**
 * Recibe el formulario de cotización por admin-post.php.
 * Responde JSON al fetch del tema y redirige si el navegador no tiene JavaScript.
 * Usa las mismas reglas de validación que el endpoint PHP de la landing estática.
 *
 * @package brisa-solar
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_nopriv_brisa_cotizacion', 'brisa_recibir_cotizacion' );
add_action( 'admin_post_brisa_cotizacion', 'brisa_recibir_cotizacion' );

function brisa_recibir_cotizacion(): void {
	$quiere_json = str_contains( sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT'] ?? '' ) ), 'application/json' );
	$error_url   = home_url( '/#cotizar' );

	$responder = function ( int $estado, array $cuerpo, string $redireccion ) use ( $quiere_json ) {
		if ( $quiere_json ) {
			wp_send_json( $cuerpo, $estado );
		}
		wp_safe_redirect( $redireccion, 303 );
		exit;
	};

	// El nonce evita envíos desde otros sitios. Si la página viene de una caché vieja, el nonce
	// puede haber caducado: se le pide al visitante recargar en lugar de perder el prospecto en silencio.
	$nonce = sanitize_key( wp_unslash( $_POST['_brisa_nonce'] ?? '' ) );
	if ( ! wp_verify_nonce( $nonce, 'brisa_cotizacion' ) ) {
		$responder( 403, array( 'ok' => false, 'error' => __( 'La página caducó. Recárgala e inténtalo de nuevo.', 'brisa-solar' ) ), $error_url );
	}

	$entrada = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- validar_prospecto() sanea cada campo.
	$ahora   = current_datetime();

	if ( parece_bot( $entrada, (int) floor( microtime( true ) * 1000 ) ) ) {
		$responder( 200, array( 'ok' => true, 'folio' => 'BS-' . $ahora->format( 'ymd' ) . '-0000' ), brisa_url_gracias() );
	}

	list( $datos, $errores ) = validar_prospecto( $entrada );
	if ( $errores ) {
		$responder(
			422,
			array(
				'ok'     => false,
				'error'  => __( 'Revisa los campos marcados.', 'brisa-solar' ),
				'campos' => $errores,
			),
			$error_url
		);
	}

	// Límite de 5 envíos por IP cada 10 minutos (transients: funcionan con o sin caché de objetos).
	$ip_hash  = hash( 'sha256', sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ) . wp_salt( 'nonce' ) );
	$clave    = 'brisa_envios_' . substr( $ip_hash, 0, 32 );
	$intentos = (int) get_transient( $clave );
	if ( $intentos >= 5 ) {
		$responder( 429, array( 'ok' => false, 'error' => __( 'Demasiados envíos. Intenta en unos minutos.', 'brisa-solar' ) ), $error_url );
	}
	set_transient( $clave, $intentos + 1, 10 * MINUTE_IN_SECONDS );

	$folio = 'BS-' . $ahora->format( 'ymd' ) . '-' . strtoupper( wp_generate_password( 4, false, false ) );
	$id    = wp_insert_post(
		array(
			'post_type'   => 'brisa_prospecto',
			'post_status' => 'private',
			'post_title'  => sprintf( '%s (%s)', $datos['nombre'], $folio ),
			'meta_input'  => array_merge( $datos, array( 'folio' => $folio ) ),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		$responder( 500, array( 'ok' => false, 'error' => __( 'No pudimos guardar tu solicitud.', 'brisa-solar' ) ), $error_url );
	}

	brisa_notificar_prospecto( $datos, $folio, (int) $id );

	/**
	 * Permite conectar el prospecto con un CRM, la API de Conversiones de Meta, etc.
	 *
	 * @param array  $datos Datos validados.
	 * @param string $folio Folio asignado.
	 */
	do_action( 'brisa_prospecto_guardado', $datos, $folio );

	$responder( 201, array( 'ok' => true, 'folio' => $folio ), add_query_arg( 'folio', $folio, brisa_url_gracias() ) );
}

function brisa_notificar_prospecto( array $datos, string $folio, int $id ): void {
	$destino = brisa_opcion( 'correo_ventas' ) ?: get_option( 'admin_email' );
	$tipo    = 'negocio' === $datos['tipo_inmueble'] ? __( 'Negocio', 'brisa-solar' ) : __( 'Casa', 'brisa-solar' );
	$lineas  = array(
		sprintf( __( 'Folio: %s', 'brisa-solar' ), $folio ),
		sprintf( __( 'Nombre: %s', 'brisa-solar' ), $datos['nombre'] ),
		sprintf( __( 'Teléfono: %s', 'brisa-solar' ), $datos['telefono'] ),
		sprintf( __( 'Inmueble: %s', 'brisa-solar' ), $tipo ),
		sprintf( __( 'Recibo bimestral: %s', 'brisa-solar' ), brisa_pesos( (float) $datos['recibo_bimestral'] ) ),
		'',
		sprintf( __( 'Ver en el administrador: %s', 'brisa-solar' ), admin_url( "post.php?post=$id&action=edit" ) ),
	);
	$cabeceras = $datos['correo'] ? array( 'Reply-To: ' . $datos['correo'] ) : array();
	wp_mail( $destino, sprintf( __( 'Nuevo prospecto %1$s (%2$s)', 'brisa-solar' ), $folio, $tipo ), implode( "\n", $lineas ), $cabeceras );
}
