<?php
/**
 * Preguntas frecuentes (Preguntas.astro), del tipo de contenido brisa_pregunta.
 *
 * @package brisa-solar
 */

$preguntas = get_posts(
	array(
		'post_type'      => 'brisa_pregunta',
		'posts_per_page' => 20,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	)
);
if ( ! $preguntas ) {
	return;
}
?>
<section id="preguntas" class="seccion" aria-labelledby="preguntas-titulo">
	<div class="container">
		<div class="row gy-5 gx-lg-5">
			<div class="col-lg-4" data-revelar>
				<h2 id="preguntas-titulo" class="seccion-titulo mb-3"><?php esc_html_e( 'Preguntas frecuentes', 'brisa-solar' ); ?></h2>
				<div class="ayuda superficie">
					<p class="mb-3"><?php esc_html_e( '¿No encuentras tu respuesta? Un asesor te contesta por WhatsApp en horario de oficina.', 'brisa-solar' ); ?></p>
					<a class="btn btn-whatsapp d-inline-flex align-items-center gap-2" href="<?php echo esc_url( brisa_enlace_whatsapp( 'casa y tengo una duda' ) ); ?>" target="_blank" rel="noopener" data-evento="click_whatsapp" data-ubicacion="preguntas">
						<?php brisa_el_icono( 'whatsapp-logo', 'fill' ); ?>
						<?php esc_html_e( 'Preguntar por WhatsApp', 'brisa-solar' ); ?>
					</a>
				</div>
			</div>
			<div class="col-lg-8" data-revelar style="--orden: 1">
				<div class="acordeon">
					<?php foreach ( $preguntas as $p ) : ?>
						<details name="preguntas">
							<summary><span><?php echo esc_html( get_the_title( $p ) ); ?></span><?php brisa_el_icono( 'plus', 'regular', 'mas' ); ?></summary>
							<p><?php echo esc_html( wp_strip_all_tags( $p->post_content ) ); ?></p>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>
