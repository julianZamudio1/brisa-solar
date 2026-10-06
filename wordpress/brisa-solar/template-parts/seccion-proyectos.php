<?php
/**
 * Carrusel de proyectos (Proyectos.astro), alimentado por el tipo de contenido brisa_proyecto.
 *
 * @package brisa-solar
 */

$proyectos = get_posts(
	array(
		'post_type'      => 'brisa_proyecto',
		'posts_per_page' => 12,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	)
);
if ( ! $proyectos ) {
	return;
}
?>
<section id="proyectos" class="seccion proyectos" aria-labelledby="proyectos-titulo">
	<div class="container">
		<div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4" data-revelar>
			<div>
				<span class="etiqueta"><?php esc_html_e( 'Proyectos recientes', 'brisa-solar' ); ?></span>
				<h2 id="proyectos-titulo" class="seccion-titulo m-0"><?php esc_html_e( 'Recibos antes y después', 'brisa-solar' ); ?></h2>
			</div>
			<div class="d-flex gap-2" data-carrusel-controles>
				<button type="button" class="flecha" data-dir="-1" aria-label="<?php esc_attr_e( 'Proyecto anterior', 'brisa-solar' ); ?>" aria-controls="carrusel-proyectos"><?php brisa_el_icono( 'arrow-left' ); ?></button>
				<button type="button" class="flecha" data-dir="1" aria-label="<?php esc_attr_e( 'Proyecto siguiente', 'brisa-solar' ); ?>" aria-controls="carrusel-proyectos"><?php brisa_el_icono( 'arrow-right' ); ?></button>
			</div>
		</div>
	</div>

	<ul id="carrusel-proyectos" class="carrusel list-unstyled" tabindex="0" aria-label="<?php esc_attr_e( 'Carrusel de proyectos', 'brisa-solar' ); ?>" data-carrusel>
		<?php foreach ( $proyectos as $p ) : ?>
			<li class="tarjeta">
				<div class="foto tarjeta-foto">
					<?php echo get_the_post_thumbnail( $p, 'brisa-tarjeta', array( 'sizes' => '(min-width: 992px) 420px, 82vw' ) ); ?>
				</div>
				<h3 class="h5 mt-3 mb-2"><?php echo esc_html( get_the_title( $p ) ); ?></h3>
				<p class="text-body-secondary mb-3">
					<?php
					printf(
						/* translators: 1: potencia en kWp, 2: número de paneles */
						esc_html__( '%1$s kWp, %2$d paneles', 'brisa-solar' ),
						esc_html( brisa_numero( (float) get_post_meta( $p->ID, 'kwp', true ) ) ),
						(int) get_post_meta( $p->ID, 'paneles', true )
					);
					?>
				</p>
				<div class="antes-despues">
					<div><span><?php esc_html_e( 'Antes', 'brisa-solar' ); ?></span><s><?php echo esc_html( brisa_pesos( (float) get_post_meta( $p->ID, 'antes', true ) ) ); ?></s></div>
					<?php brisa_el_icono( 'arrow-right', 'regular', 'icono-sm' ); ?>
					<div><span><?php esc_html_e( 'Ahora', 'brisa-solar' ); ?></span><strong><?php echo esc_html( brisa_pesos( (float) get_post_meta( $p->ID, 'despues', true ) ) ); ?></strong></div>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
