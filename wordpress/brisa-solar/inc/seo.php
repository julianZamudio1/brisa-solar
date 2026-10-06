<?php
/**
 * SEO técnico básico: meta descripción, Open Graph y datos estructurados.
 * Si hay un plugin de SEO activo (Yoast, Rank Math) se le deja la meta descripción y OG.
 *
 * @package brisa-solar
 */

defined( 'ABSPATH' ) || exit;

function brisa_hay_plugin_seo(): bool {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' );
}

add_action(
	'wp_head',
	function () {
		if ( brisa_hay_plugin_seo() ) {
			return;
		}
		$descripcion = is_singular() && has_excerpt() ? get_the_excerpt() : brisa_opcion( 'descripcion' );
		$url         = is_front_page() ? home_url( '/' ) : get_permalink();
		$imagen      = BRISA_URI . '/assets/img/hero-instalador-1280.webp';

		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $descripcion ) );
		printf( '<meta property="og:type" content="website">' . "\n" );
		printf( '<meta property="og:locale" content="es_MX">' . "\n" );
		printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( wp_get_document_title() ) );
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $descripcion ) );
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( (string) $url ) );
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $imagen ) );
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

		if ( is_page( 'gracias' ) ) {
			echo '<meta name="robots" content="noindex, follow">' . "\n";
		}
	},
	3
);

add_action(
	'wp_head',
	function () {
		if ( ! is_front_page() ) {
			return;
		}
		$url     = home_url( '/' );
		$negocio = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'HomeAndConstructionBusiness',
			'@id'         => $url . '#negocio',
			'name'        => get_bloginfo( 'name' ),
			'description' => brisa_opcion( 'descripcion' ),
			'url'         => $url,
			'telephone'   => brisa_opcion( 'telefono' ),
			'email'       => brisa_opcion( 'correo' ),
			'priceRange'  => '$$',
			'address'     => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => brisa_opcion( 'calle' ),
				'addressLocality' => brisa_opcion( 'ciudad' ),
				'addressRegion'   => brisa_opcion( 'estado' ),
				'postalCode'      => brisa_opcion( 'cp' ),
				'addressCountry'  => 'MX',
			),
			'geo'         => array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) brisa_opcion( 'lat' ),
				'longitude' => (float) brisa_opcion( 'lng' ),
			),
			'sameAs'      => array_values( array_filter( array( brisa_opcion( 'facebook' ), brisa_opcion( 'instagram' ) ) ) ),
		);

		$preguntas = get_posts(
			array(
				'post_type'      => 'brisa_pregunta',
				'posts_per_page' => 20,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			)
		);
		$esquemas = array( $negocio );
		if ( $preguntas ) {
			$esquemas[] = array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => array_map(
					fn ( $p ) => array(
						'@type'          => 'Question',
						'name'           => get_the_title( $p ),
						'acceptedAnswer' => array(
							'@type' => 'Answer',
							'text'  => wp_strip_all_tags( $p->post_content ),
						),
					),
					$preguntas
				),
			);
		}
		foreach ( $esquemas as $esquema ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $esquema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "</script>\n";
		}
	}
);
