<?php
/**
 * Tipos de contenido: proyectos, testimonios, preguntas y prospectos.
 *
 * @package brisa-solar
 */

defined( 'ABSPATH' ) || exit;

/** Campos personalizados por tipo: clave => [etiqueta, tipo]. */
function brisa_campos(): array {
	return array(
		'brisa_proyecto'   => array(
			'kwp'     => array( __( 'Potencia (kWp)', 'brisa-solar' ), 'number' ),
			'paneles' => array( __( 'Número de paneles', 'brisa-solar' ), 'integer' ),
			'antes'   => array( __( 'Recibo bimestral antes ($)', 'brisa-solar' ), 'integer' ),
			'despues' => array( __( 'Recibo bimestral después ($)', 'brisa-solar' ), 'integer' ),
		),
		'brisa_testimonio' => array(
			'detalle'   => array( __( 'Detalle (lugar o puesto)', 'brisa-solar' ), 'string' ),
			'destacado' => array( __( 'Mostrar como testimonio principal', 'brisa-solar' ), 'boolean' ),
		),
	);
}

add_action(
	'init',
	function () {
		register_post_type(
			'brisa_proyecto',
			array(
				'labels'       => array(
					'name'          => __( 'Proyectos', 'brisa-solar' ),
					'singular_name' => __( 'Proyecto', 'brisa-solar' ),
					'add_new_item'  => __( 'Agregar proyecto', 'brisa-solar' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-portfolio',
				'supports'     => array( 'title', 'thumbnail', 'page-attributes' ),
			)
		);

		register_post_type(
			'brisa_testimonio',
			array(
				'labels'       => array(
					'name'          => __( 'Testimonios', 'brisa-solar' ),
					'singular_name' => __( 'Testimonio', 'brisa-solar' ),
					'add_new_item'  => __( 'Agregar testimonio (título = nombre)', 'brisa-solar' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-format-quote',
				'supports'     => array( 'title', 'editor', 'page-attributes' ),
			)
		);

		register_post_type(
			'brisa_pregunta',
			array(
				'labels'       => array(
					'name'          => __( 'Preguntas frecuentes', 'brisa-solar' ),
					'singular_name' => __( 'Pregunta', 'brisa-solar' ),
					'add_new_item'  => __( 'Agregar pregunta', 'brisa-solar' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-editor-help',
				'supports'     => array( 'title', 'editor', 'page-attributes' ),
			)
		);

		// Los prospectos son privados: solo se ven en el administrador.
		register_post_type(
			'brisa_prospecto',
			array(
				'labels'          => array(
					'name'          => __( 'Prospectos', 'brisa-solar' ),
					'singular_name' => __( 'Prospecto', 'brisa-solar' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_rest'    => false,
				'menu_icon'       => 'dashicons-id-alt',
				'menu_position'   => 3,
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);

		foreach ( brisa_campos() as $tipo => $campos ) {
			foreach ( $campos as $clave => list( , $formato ) ) {
				register_post_meta(
					$tipo,
					$clave,
					array(
						'type'          => $formato,
						'single'        => true,
						'show_in_rest'  => true,
						'auth_callback' => fn () => current_user_can( 'edit_posts' ),
					)
				);
			}
		}
	}
);

/* Caja de campos en el editor de proyectos y testimonios. */

add_action(
	'add_meta_boxes',
	function () {
		foreach ( array_keys( brisa_campos() ) as $tipo ) {
			add_meta_box( 'brisa_campos', __( 'Datos', 'brisa-solar' ), 'brisa_caja_campos', $tipo, 'side' );
		}
		add_meta_box( 'brisa_prospecto', __( 'Datos del prospecto', 'brisa-solar' ), 'brisa_caja_prospecto', 'brisa_prospecto', 'normal', 'high' );
	}
);

function brisa_caja_campos( WP_Post $post ): void {
	wp_nonce_field( 'brisa_guardar_campos', 'brisa_campos_nonce' );
	foreach ( brisa_campos()[ $post->post_type ] as $clave => list( $etiqueta, $formato ) ) {
		$valor = get_post_meta( $post->ID, $clave, true );
		echo '<p>';
		if ( 'boolean' === $formato ) {
			printf(
				'<label><input type="checkbox" name="brisa[%1$s]" value="1" %2$s> %3$s</label>',
				esc_attr( $clave ),
				checked( (bool) $valor, true, false ),
				esc_html( $etiqueta )
			);
		} else {
			printf(
				'<label for="brisa-%1$s">%2$s</label><br><input class="widefat" id="brisa-%1$s" name="brisa[%1$s]" type="%3$s" step="any" value="%4$s">',
				esc_attr( $clave ),
				esc_html( $etiqueta ),
				'string' === $formato ? 'text' : 'number',
				esc_attr( (string) $valor )
			);
		}
		echo '</p>';
	}
}

add_action(
	'save_post',
	function ( int $post_id, WP_Post $post ) {
		$campos = brisa_campos()[ $post->post_type ] ?? null;
		if (
			! $campos
			|| ! isset( $_POST['brisa_campos_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['brisa_campos_nonce'] ), 'brisa_guardar_campos' )
			|| ! current_user_can( 'edit_post', $post_id )
			|| wp_is_post_autosave( $post_id )
		) {
			return;
		}
		$entrada = isset( $_POST['brisa'] ) ? (array) wp_unslash( $_POST['brisa'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- se sanea por campo.
		foreach ( $campos as $clave => list( , $formato ) ) {
			$valor = $entrada[ $clave ] ?? '';
			$valor = match ( $formato ) {
				'integer' => absint( $valor ),
				'number'  => (float) $valor,
				'boolean' => '1' === $valor,
				default   => sanitize_text_field( $valor ),
			};
			update_post_meta( $post_id, $clave, $valor );
		}
	},
	10,
	2
);

/* Prospectos: vista de solo lectura, columnas y exportación a CSV. */

/** @return array<string, string> */
function brisa_campos_prospecto(): array {
	return array(
		'folio'            => __( 'Folio', 'brisa-solar' ),
		'nombre'           => __( 'Nombre', 'brisa-solar' ),
		'telefono'         => __( 'Teléfono', 'brisa-solar' ),
		'correo'           => __( 'Correo', 'brisa-solar' ),
		'codigo_postal'    => __( 'Código postal', 'brisa-solar' ),
		'tipo_inmueble'    => __( 'Inmueble', 'brisa-solar' ),
		'recibo_bimestral' => __( 'Recibo bimestral', 'brisa-solar' ),
		'mensaje'          => __( 'Comentarios', 'brisa-solar' ),
		'origen'           => __( 'Origen', 'brisa-solar' ),
		'campana'          => __( 'Campaña (UTM)', 'brisa-solar' ),
	);
}

/** Texto legible de un dato del prospecto para la ficha y las columnas. */
function brisa_valor_prospecto( string $clave, mixed $valor ): string {
	if ( 'recibo_bimestral' === $clave && $valor ) {
		return brisa_pesos( (float) $valor );
	}
	if ( 'tipo_inmueble' === $clave ) {
		return 'negocio' === $valor ? __( 'Negocio', 'brisa-solar' ) : __( 'Casa', 'brisa-solar' );
	}
	if ( 'campana' === $clave ) {
		if ( ! is_array( $valor ) || ! $valor ) {
			return __( 'Sin datos de campaña', 'brisa-solar' );
		}
		return implode( ', ', array_map( fn ( $k, $v ) => "$k: $v", array_keys( $valor ), $valor ) );
	}
	return is_scalar( $valor ) ? (string) $valor : '';
}

function brisa_caja_prospecto( WP_Post $post ): void {
	echo '<table class="widefat striped"><tbody>';
	foreach ( brisa_campos_prospecto() as $clave => $etiqueta ) {
		$valor = get_post_meta( $post->ID, $clave, true );
		if ( 'telefono' === $clave && $valor ) {
			$valor = sprintf( '<a href="%s">%s</a> · <a href="%s" target="_blank" rel="noopener">WhatsApp</a>', esc_url( "tel:$valor" ), esc_html( $valor ), esc_url( "https://wa.me/52$valor" ) );
		} else {
			$valor = esc_html( brisa_valor_prospecto( $clave, $valor ) );
		}
		printf( '<tr><th style="width:12rem">%s</th><td>%s</td></tr>', esc_html( $etiqueta ), $valor ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado arriba.
	}
	echo '</tbody></table>';
}

add_filter(
	'manage_brisa_prospecto_posts_columns',
	fn ( $columnas ) => array(
		'cb'               => $columnas['cb'],
		'title'            => __( 'Prospecto', 'brisa-solar' ),
		'telefono'         => __( 'Teléfono', 'brisa-solar' ),
		'tipo_inmueble'    => __( 'Inmueble', 'brisa-solar' ),
		'recibo_bimestral' => __( 'Recibo', 'brisa-solar' ),
		'origen'           => __( 'Origen', 'brisa-solar' ),
		'date'             => __( 'Fecha', 'brisa-solar' ),
	)
);

add_action(
	'manage_brisa_prospecto_posts_custom_column',
	function ( string $columna, int $post_id ) {
		echo esc_html( brisa_valor_prospecto( $columna, get_post_meta( $post_id, $columna, true ) ) );
	},
	10,
	2
);

add_action(
	'restrict_manage_posts',
	function ( string $tipo ) {
		if ( 'brisa_prospecto' !== $tipo ) {
			return;
		}
		printf(
			'<a class="button" href="%s">%s</a>',
			esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=brisa_exportar_prospectos' ), 'brisa_exportar' ) ),
			esc_html__( 'Exportar CSV', 'brisa-solar' )
		);
	}
);

add_action(
	'admin_post_brisa_exportar_prospectos',
	function () {
		if ( ! current_user_can( 'edit_posts' ) || ! check_admin_referer( 'brisa_exportar' ) ) {
			wp_die( esc_html__( 'No tienes permiso para exportar prospectos.', 'brisa-solar' ), 403 );
		}
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=prospectos-' . gmdate( 'Y-m-d' ) . '.csv' );

		$salida = fopen( 'php://output', 'w' );
		fwrite( $salida, "\xEF\xBB\xBF" ); // BOM para que Excel respete los acentos.
		fputcsv( $salida, array_merge( array( __( 'Fecha', 'brisa-solar' ) ), array_values( brisa_campos_prospecto() ) ) );
		$ids = get_posts(
			array(
				'post_type'      => 'brisa_prospecto',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		foreach ( $ids as $id ) {
			$fila = array( get_the_date( 'Y-m-d H:i', $id ) );
			foreach ( array_keys( brisa_campos_prospecto() ) as $clave ) {
				$valor = get_post_meta( $id, $clave, true );
				// Evita inyección de fórmulas al abrir el CSV en Excel.
				$valor  = is_array( $valor ) ? wp_json_encode( $valor ) : (string) $valor;
				$fila[] = preg_match( '/^[=+\-@]/', $valor ) ? "'$valor" : $valor;
			}
			fputcsv( $salida, $fila );
		}
		exit;
	}
);
