<?php
/**
 * Página de agradecimiento después de enviar el formulario (gracias.astro).
 * La plantilla se elige por el slug de la página: "gracias".
 *
 * @package brisa-solar
 */

get_header();
$folio = isset( $_GET['folio'] ) ? sanitize_text_field( wp_unslash( $_GET['folio'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo se muestra.
$folio = preg_match( '/^[A-Z0-9-]{4,20}$/', $folio ) ? $folio : '';
?>
<main id="contenido" class="seccion">
	<div class="container" style="max-width: 46rem">
		<div class="confirmacion">
			<?php brisa_el_icono( 'check-circle', 'duotone', 'check' ); ?>
			<h1 class="seccion-titulo mt-3 mb-3"><?php the_title(); ?></h1>
			<p class="seccion-texto">
				<?php esc_html_e( 'Un asesor te llamará en menos de un día hábil para agendar la visita técnica. Tu folio es', 'brisa-solar' ); ?>
				<strong data-folio><?php echo esc_html( $folio ?: __( 'en proceso', 'brisa-solar' ) ); ?></strong>.
			</p>

			<h2 class="h5 mt-5 mb-3"><?php esc_html_e( 'Para adelantar la visita, ten a la mano:', 'brisa-solar' ); ?></h2>
			<ul class="lista">
				<li><?php esc_html_e( 'Tu último recibo de CFE (una foto basta).', 'brisa-solar' ); ?></li>
				<li><?php esc_html_e( 'Fotos del techo o la azotea donde irían los paneles.', 'brisa-solar' ); ?></li>
				<li><?php esc_html_e( 'La ubicación de tu medidor de luz.', 'brisa-solar' ); ?></li>
			</ul>

			<div class="d-flex flex-wrap gap-3 mt-5">
				<a class="btn btn-whatsapp btn-lg d-inline-flex align-items-center gap-2" href="<?php echo esc_url( brisa_enlace_whatsapp( 'casa. Ya llené el formulario y quiero enviar mi recibo' ) ); ?>" target="_blank" rel="noopener" data-evento="click_whatsapp" data-ubicacion="gracias">
					<?php brisa_el_icono( 'whatsapp-logo', 'fill' ); ?>
					<?php esc_html_e( 'Enviar recibo por WhatsApp', 'brisa-solar' ); ?>
				</a>
				<a class="btn btn-outline-secondary btn-lg" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Volver al inicio', 'brisa-solar' ); ?></a>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
