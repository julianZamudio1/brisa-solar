<?php
/**
 * Página no encontrada (404.astro).
 *
 * @package brisa-solar
 */

get_header();
?>
<main id="contenido" class="seccion">
	<div class="container" style="max-width: 40rem">
		<h1 class="seccion-titulo mb-3"><?php esc_html_e( 'Esta página no existe', 'brisa-solar' ); ?></h1>
		<p class="seccion-texto mb-4"><?php esc_html_e( 'Puede que el enlace esté mal escrito o que la página se haya movido.', 'brisa-solar' ); ?></p>
		<div class="d-flex flex-wrap gap-3">
			<a class="btn btn-primary btn-lg" href="<?php echo esc_url( home_url( '/#calculadora' ) ); ?>"><?php esc_html_e( 'Calcular mi ahorro', 'brisa-solar' ); ?></a>
			<a class="btn btn-outline-secondary btn-lg" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ir al inicio', 'brisa-solar' ); ?></a>
		</div>
	</div>
</main>
<?php
get_footer();
