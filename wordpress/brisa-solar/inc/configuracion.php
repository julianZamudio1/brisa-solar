<?php
/**
 * Datos del negocio editables desde Apariencia > Personalizar.
 * Los valores predeterminados vienen de site/src/data/sitio.ts (inc/generado/sitio.php).
 *
 * @package brisa-solar
 */

defined( 'ABSPATH' ) || exit;

/** @return array<string, string> */
function brisa_predeterminados(): array {
	static $valores = null;
	if ( null === $valores ) {
		$s       = require BRISA_DIR . '/inc/generado/sitio.php';
		$valores = array(
			'telefono'         => $s['telefono'],
			'telefono_visible' => $s['telefonoVisible'],
			'whatsapp'         => $s['whatsapp'],
			'mensaje_whatsapp' => $s['mensajeWhatsapp'],
			'correo'           => $s['correo'],
			'horario'          => $s['horario'],
			'calle'            => $s['direccion']['calle'],
			'colonia'          => $s['direccion']['colonia'],
			'ciudad'           => $s['direccion']['ciudad'],
			'estado'           => $s['direccion']['estado'],
			'cp'               => $s['direccion']['cp'],
			'lat'              => (string) $s['geo']['lat'],
			'lng'              => (string) $s['geo']['lng'],
			'facebook'         => $s['redes']['facebook'],
			'instagram'        => $s['redes']['instagram'],
			'descripcion'      => $s['descripcion'],
			'hero_etiqueta'    => 'Instalación en Querétaro y el Bajío',
			'hero_titulo'      => 'Paga hasta 95% menos de luz con energía solar',
			'hero_texto'       => 'Diseñamos el sistema, lo instalamos y tramitamos tu interconexión con CFE. Calcula tu ahorro en 30 segundos.',
			'video_youtube'    => 'xKxrkht7CpY',
			'gtm_id'           => '',
			'correo_ventas'    => '',
		);
	}
	return $valores;
}

function brisa_opcion( string $clave ): string {
	$predeterminados = brisa_predeterminados();
	return (string) get_theme_mod( "brisa_$clave", $predeterminados[ $clave ] ?? '' );
}

function brisa_enlace_whatsapp( string $complemento = 'casa' ): string {
	$texto = brisa_opcion( 'mensaje_whatsapp' ) . ' ' . $complemento . '.';
	return 'https://wa.me/' . rawurlencode( brisa_opcion( 'whatsapp' ) ) . '?text=' . rawurlencode( $texto );
}

function brisa_sanear_gtm( string $valor ): string {
	$valor = strtoupper( trim( $valor ) );
	return preg_match( '/^GTM-[A-Z0-9]{4,10}$/', $valor ) ? $valor : '';
}

// sanitize_key() pasaría el ID a minúsculas y los IDs de YouTube distinguen mayúsculas.
function brisa_sanear_youtube( string $valor ): string {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', $valor ) ?? '';
}

function brisa_sanear_digitos( string $valor ): string {
	return preg_replace( '/[^\d+]/', '', $valor ) ?? '';
}

add_action(
	'customize_register',
	function ( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_panel(
			'brisa',
			array(
				'title'    => __( 'Brisa Solar', 'brisa-solar' ),
				'priority' => 30,
			)
		);

		$secciones = array(
			'brisa_negocio'  => array(
				'titulo' => __( 'Datos del negocio', 'brisa-solar' ),
				'campos' => array(
					'telefono'         => array( __( 'Teléfono (formato internacional, +52…)', 'brisa-solar' ), 'brisa_sanear_digitos' ),
					'telefono_visible' => array( __( 'Teléfono como se muestra', 'brisa-solar' ), 'sanitize_text_field' ),
					'whatsapp'         => array( __( 'WhatsApp (solo dígitos, con 52)', 'brisa-solar' ), 'brisa_sanear_digitos' ),
					'mensaje_whatsapp' => array( __( 'Mensaje inicial de WhatsApp', 'brisa-solar' ), 'sanitize_text_field' ),
					'correo'           => array( __( 'Correo público', 'brisa-solar' ), 'sanitize_email' ),
					'horario'          => array( __( 'Horario', 'brisa-solar' ), 'sanitize_text_field' ),
					'calle'            => array( __( 'Calle y número', 'brisa-solar' ), 'sanitize_text_field' ),
					'colonia'          => array( __( 'Colonia', 'brisa-solar' ), 'sanitize_text_field' ),
					'ciudad'           => array( __( 'Ciudad', 'brisa-solar' ), 'sanitize_text_field' ),
					'estado'           => array( __( 'Estado', 'brisa-solar' ), 'sanitize_text_field' ),
					'cp'               => array( __( 'Código postal', 'brisa-solar' ), 'sanitize_text_field' ),
					'lat'              => array( __( 'Latitud (mapa)', 'brisa-solar' ), 'sanitize_text_field' ),
					'lng'              => array( __( 'Longitud (mapa)', 'brisa-solar' ), 'sanitize_text_field' ),
					'facebook'         => array( __( 'URL de Facebook', 'brisa-solar' ), 'esc_url_raw' ),
					'instagram'        => array( __( 'URL de Instagram', 'brisa-solar' ), 'esc_url_raw' ),
				),
			),
			'brisa_portada'  => array(
				'titulo' => __( 'Portada', 'brisa-solar' ),
				'campos' => array(
					'hero_etiqueta' => array( __( 'Etiqueta sobre el título', 'brisa-solar' ), 'sanitize_text_field' ),
					'hero_titulo'   => array( __( 'Título principal', 'brisa-solar' ), 'sanitize_text_field' ),
					'hero_texto'    => array( __( 'Texto bajo el título (máx. 20 palabras)', 'brisa-solar' ), 'sanitize_text_field' ),
					'video_youtube' => array( __( 'ID del video de YouTube', 'brisa-solar' ), 'brisa_sanear_youtube' ),
					'descripcion'   => array( __( 'Descripción para buscadores', 'brisa-solar' ), 'sanitize_text_field' ),
				),
			),
			'brisa_medicion' => array(
				'titulo' => __( 'Medición y prospectos', 'brisa-solar' ),
				'campos' => array(
					'gtm_id'        => array( __( 'ID de Google Tag Manager (GTM-XXXXXXX)', 'brisa-solar' ), 'brisa_sanear_gtm' ),
					'correo_ventas' => array( __( 'Correo que recibe los prospectos (vacío: correo del administrador)', 'brisa-solar' ), 'sanitize_email' ),
				),
			),
		);

		$predeterminados = brisa_predeterminados();
		foreach ( $secciones as $id => $seccion ) {
			$wp_customize->add_section(
				$id,
				array(
					'title' => $seccion['titulo'],
					'panel' => 'brisa',
				)
			);
			foreach ( $seccion['campos'] as $clave => list( $etiqueta, $saneador ) ) {
				$wp_customize->add_setting(
					"brisa_$clave",
					array(
						'default'           => $predeterminados[ $clave ],
						'sanitize_callback' => $saneador,
					)
				);
				$wp_customize->add_control(
					"brisa_$clave",
					array(
						'label'   => $etiqueta,
						'section' => $id,
						'type'    => 'correo_ventas' === $clave || 'correo' === $clave ? 'email' : 'text',
					)
				);
			}
		}
	}
);
