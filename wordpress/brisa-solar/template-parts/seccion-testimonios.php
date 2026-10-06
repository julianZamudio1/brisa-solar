<?php
/**
 * Testimonios (Testimonios.astro): uno destacado y el resto en tarjetas.
 *
 * @package brisa-solar
 */

$testimonios = get_posts(
	array(
		'post_type'      => 'brisa_testimonio',
		'posts_per_page' => 3,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	)
);
if ( ! $testimonios ) {
	return;
}
$destacado = current( array_filter( $testimonios, fn ( $t ) => get_post_meta( $t->ID, 'destacado', true ) ) ) ?: $testimonios[0];
$otros     = array_filter( $testimonios, fn ( $t ) => $t->ID !== $destacado->ID );
$texto     = fn ( WP_Post $t ) => wp_strip_all_tags( $t->post_content );
?>
<section class="seccion testimonios" aria-labelledby="testimonios-titulo">
	<div class="container">
		<h2 id="testimonios-titulo" class="visually-hidden"><?php esc_html_e( 'Lo que dicen nuestros clientes', 'brisa-solar' ); ?></h2>
		<div class="row gy-4 gx-lg-5 align-items-center">
			<figure class="col-lg-7 m-0 destacado" data-revelar>
				<div class="estrellas" role="img" aria-label="<?php esc_attr_e( 'Calificación: 5 de 5', 'brisa-solar' ); ?>">
					<?php echo str_repeat( brisa_icono( 'star', 'fill' ), 5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<blockquote class="m-0"><p>“<?php echo esc_html( $texto( $destacado ) ); ?>”</p></blockquote>
				<figcaption>
					<strong><?php echo esc_html( get_the_title( $destacado ) ); ?></strong>
					<span><?php echo esc_html( get_post_meta( $destacado->ID, 'detalle', true ) ); ?></span>
				</figcaption>
			</figure>

			<div class="col-lg-5 d-grid gap-3">
				<?php $i = 0; foreach ( $otros as $t ) : ?>
					<figure class="m-0 cita superficie" data-revelar style="--orden: <?php echo (int) ++$i; ?>">
						<blockquote class="m-0"><p class="mb-3">“<?php echo esc_html( $texto( $t ) ); ?>”</p></blockquote>
						<figcaption>
							<strong><?php echo esc_html( get_the_title( $t ) ); ?></strong>
							<span><?php echo esc_html( get_post_meta( $t->ID, 'detalle', true ) ); ?></span>
						</figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
