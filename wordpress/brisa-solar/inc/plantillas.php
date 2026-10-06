<?php
/**
 * Funciones auxiliares para las plantillas.
 *
 * @package brisa-solar
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ícono de Phosphor incrustado (mismo resultado que el componente Icono.astro).
 * Los SVG vienen del paquete @phosphor-icons/core, no de entrada de usuarios.
 */
function brisa_icono( string $nombre, string $estilo = 'regular', string $clase = '' ): string {
	$archivo = 'regular' === $estilo ? "$nombre.svg" : "$nombre-$estilo.svg";
	$ruta    = BRISA_DIR . '/assets/iconos/' . sanitize_file_name( $estilo ) . '/' . sanitize_file_name( $archivo );
	if ( ! is_readable( $ruta ) ) {
		return '';
	}
	$svg = (string) file_get_contents( $ruta ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	return str_replace( '<svg', '<svg class="icono ' . esc_attr( $clase ) . '" aria-hidden="true" focusable="false"', $svg );
}

/** Imprime un ícono. */
function brisa_el_icono( string $nombre, string $estilo = 'regular', string $clase = '' ): void {
	echo brisa_icono( $nombre, $estilo, $clase ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Imagen del tema en WebP con srcset (generada por npm run tema).
 *
 * @param int[]                 $anchos   Anchos disponibles.
 * @param array<string, string> $atributos Atributos extra.
 */
function brisa_imagen( string $nombre, array $anchos, string $sizes, string $alt, array $atributos = array() ): void {
	$srcset = array();
	foreach ( $anchos as $ancho ) {
		$srcset[] = esc_url( BRISA_URI . "/assets/img/$nombre-$ancho.webp" ) . " {$ancho}w";
	}
	$mayor = max( $anchos );
	$alto  = (int) round( $mayor * ( $atributos['proporcion'] ?? 0.75 ) );
	unset( $atributos['proporcion'] );

	$extra = '';
	foreach ( array_merge( array( 'loading' => 'lazy', 'decoding' => 'async' ), $atributos ) as $clave => $valor ) {
		$extra .= sprintf( ' %s="%s"', esc_attr( $clave ), esc_attr( $valor ) );
	}
	printf(
		'<img src="%s" srcset="%s" sizes="%s" width="%d" height="%d" alt="%s"%s>',
		esc_url( BRISA_URI . "/assets/img/$nombre-$mayor.webp" ),
		esc_attr( implode( ', ', $srcset ) ),
		esc_attr( $sizes ),
		(int) $mayor,
		(int) $alto,
		esc_attr( $alt ),
		$extra // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado arriba.
	);
}

function brisa_pesos( float $monto ): string {
	return '$' . number_format( $monto, 0, '.', ',' );
}

function brisa_numero( float $valor ): string {
	return rtrim( rtrim( number_format( $valor, 1, '.', ',' ), '0' ), '.' );
}

/** URL de la página de agradecimiento (slug "gracias"); si no existe, la portada. */
function brisa_url_gracias(): string {
	$pagina = get_page_by_path( 'gracias' );
	return $pagina ? get_permalink( $pagina ) : home_url( '/' );
}

function brisa_url_aviso(): string {
	$id = (int) get_option( 'wp_page_for_privacy_policy' );
	if ( $id && 'publish' === get_post_status( $id ) ) {
		return get_permalink( $id );
	}
	$pagina = get_page_by_path( 'aviso-de-privacidad' );
	return $pagina ? get_permalink( $pagina ) : home_url( '/' );
}

/** En la portada los anclajes son locales; en otras páginas apuntan a la portada. */
function brisa_ancla( string $id ): string {
	return is_front_page() ? "#$id" : home_url( "/#$id" );
}

/** @return array<string, mixed> Datos de la calculadora calculados en el build. */
function brisa_calculadora(): array {
	static $datos = null;
	return $datos ??= require BRISA_DIR . '/inc/generado/calculadora.php';
}
