<?php
/**
 * Brisa Solar: arranque del tema.
 *
 * @package brisa-solar
 */

defined( 'ABSPATH' ) || exit;

define( 'BRISA_VERSION', '1.0.0' );
define( 'BRISA_DIR', get_template_directory() );
define( 'BRISA_URI', get_template_directory_uri() );

require BRISA_DIR . '/inc/generado/validacion.php';
require BRISA_DIR . '/inc/configuracion.php';
require BRISA_DIR . '/inc/plantillas.php';
require BRISA_DIR . '/inc/tipos-de-contenido.php';
require BRISA_DIR . '/inc/formulario.php';
require BRISA_DIR . '/inc/medicion.php';
require BRISA_DIR . '/inc/seo.php';
require BRISA_DIR . '/inc/demo.php';

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'brisa-solar', BRISA_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'custom-logo', array( 'height' => 32, 'width' => 160, 'flex-width' => true ) );
		add_image_size( 'brisa-tarjeta', 860, 645, true );
		register_nav_menus( array( 'principal' => __( 'Menú principal', 'brisa-solar' ) ) );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'brisa-fuentes', BRISA_URI . '/assets/css/fuentes.css', array(), BRISA_VERSION );
		wp_enqueue_style( 'brisa', BRISA_URI . '/assets/css/brisa.css', array( 'brisa-fuentes' ), BRISA_VERSION );

		// Módulo ES diferido: no bloquea el render (WordPress 6.5+).
		wp_enqueue_script_module( 'brisa', BRISA_URI . '/assets/js/brisa.js', array(), BRISA_VERSION );

		// La portada no usa bloques: se ahorra el CSS de la biblioteca de bloques.
		if ( is_front_page() ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'global-styles' );
			wp_dequeue_style( 'classic-theme-styles' );
		}
	}
);

// Precarga de fuentes para evitar el salto de texto (CLS).
add_action(
	'wp_head',
	function () {
		foreach ( array( 'figtree', 'bricolage-grotesque' ) as $fuente ) {
			printf(
				'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
				esc_url( BRISA_URI . "/assets/fuentes/$fuente.woff2" )
			);
		}
	},
	2
);

// Sin emojis ni oEmbed del propio sitio: scripts que la landing no necesita.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
