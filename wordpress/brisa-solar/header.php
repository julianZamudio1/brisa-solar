<?php
/**
 * Encabezado (mismo marcado que Encabezado.astro).
 *
 * @package brisa-solar
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#f4f7f8" media="(prefers-color-scheme: light)">
	<meta name="theme-color" content="#0b151c" media="(prefers-color-scheme: dark)">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="enlace-saltar" href="#contenido"><?php esc_html_e( 'Saltar al contenido', 'brisa-solar' ); ?></a>

<header class="encabezado">
	<div class="container d-flex align-items-center gap-3">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="marca" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) . ', inicio' ); ?>">
			<?php get_template_part( 'template-parts/logo' ); ?>
		</a>

		<nav id="menu-principal" class="menu" aria-label="<?php esc_attr_e( 'Principal', 'brisa-solar' ); ?>">
			<?php
			if ( has_nav_menu( 'principal' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'principal',
						'container'      => false,
						'menu_class'     => 'list-unstyled d-flex flex-column flex-lg-row gap-1 gap-lg-4 m-0',
						'depth'          => 1,
					)
				);
			} else {
				$enlaces = array(
					'calculadora'   => __( 'Calculadora', 'brisa-solar' ),
					'como-funciona' => __( 'Cómo funciona', 'brisa-solar' ),
					'proyectos'     => __( 'Proyectos', 'brisa-solar' ),
					'preguntas'     => __( 'Preguntas', 'brisa-solar' ),
				);
				echo '<ul class="list-unstyled d-flex flex-column flex-lg-row gap-1 gap-lg-4 m-0">';
				foreach ( $enlaces as $ancla => $texto ) {
					printf( '<li><a href="%s">%s</a></li>', esc_url( brisa_ancla( $ancla ) ), esc_html( $texto ) );
				}
				echo '</ul>';
			}
			?>
		</nav>

		<div class="ms-auto d-flex align-items-center gap-2">
			<a class="telefono d-none d-xl-inline-flex" href="<?php echo esc_url( 'tel:' . brisa_opcion( 'telefono' ) ); ?>" data-evento="click_telefono" data-ubicacion="encabezado">
				<?php brisa_el_icono( 'phone', 'regular', 'icono-sm' ); ?>
				<?php echo esc_html( brisa_opcion( 'telefono_visible' ) ); ?>
			</a>
			<button type="button" class="boton-icono" data-tema-boton aria-label="<?php esc_attr_e( 'Cambiar tema claro u oscuro', 'brisa-solar' ); ?>">
				<span class="tema-claro"><?php brisa_el_icono( 'moon' ); ?></span>
				<span class="tema-oscuro"><?php brisa_el_icono( 'sun' ); ?></span>
			</button>
			<a class="btn btn-primary d-none d-sm-inline-block" href="<?php echo esc_url( brisa_ancla( 'cotizar' ) ); ?>" data-evento="click_cta" data-ubicacion="encabezado"><?php esc_html_e( 'Cotizar gratis', 'brisa-solar' ); ?></a>
			<button type="button" class="boton-icono d-lg-none" data-menu-boton aria-expanded="false" aria-controls="menu-principal" aria-label="<?php esc_attr_e( 'Abrir menú', 'brisa-solar' ); ?>">
				<?php brisa_el_icono( 'list' ); ?>
			</button>
		</div>
	</div>
</header>
