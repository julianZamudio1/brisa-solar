<?php
/**
 * Pie, botones de contacto flotantes y aviso de cookies (mismo marcado que Pie.astro y Consentimiento.astro).
 *
 * @package brisa-solar
 */

?>
<footer class="pie">
	<div class="container">
		<div class="row gy-4">
			<div class="col-lg-5">
				<?php get_template_part( 'template-parts/logo' ); ?>
				<p class="text-body-secondary mt-3 mb-0" style="max-width: 36ch"><?php echo esc_html( brisa_opcion( 'descripcion' ) ); ?></p>
			</div>
			<div class="col-6 col-lg-3">
				<h2 class="h6"><?php esc_html_e( 'Oficina', 'brisa-solar' ); ?></h2>
				<address class="text-body-secondary mb-0">
					<?php echo esc_html( brisa_opcion( 'calle' ) ); ?><br>
					<?php echo esc_html( brisa_opcion( 'colonia' ) . ', C.P. ' . brisa_opcion( 'cp' ) ); ?><br>
					<?php echo esc_html( brisa_opcion( 'ciudad' ) . ', ' . brisa_opcion( 'estado' ) ); ?>
				</address>
			</div>
			<div class="col-6 col-lg-4">
				<h2 class="h6"><?php esc_html_e( 'Contacto', 'brisa-solar' ); ?></h2>
				<ul class="list-unstyled text-body-secondary mb-3">
					<li><a href="<?php echo esc_url( 'tel:' . brisa_opcion( 'telefono' ) ); ?>" data-evento="click_telefono" data-ubicacion="pie"><?php echo esc_html( brisa_opcion( 'telefono_visible' ) ); ?></a></li>
					<li><a href="<?php echo esc_url( 'mailto:' . brisa_opcion( 'correo' ) ); ?>" data-evento="click_correo" data-ubicacion="pie"><?php echo esc_html( brisa_opcion( 'correo' ) ); ?></a></li>
				</ul>
				<div class="d-flex gap-2">
					<?php foreach ( array( 'facebook' => 'Facebook', 'instagram' => 'Instagram' ) as $red => $nombre ) : ?>
						<?php if ( brisa_opcion( $red ) ) : ?>
							<a class="red" href="<?php echo esc_url( brisa_opcion( $red ) ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $nombre ); ?>">
								<?php brisa_el_icono( "$red-logo" ); ?>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<div class="pie-legal">
			<p class="m-0">© <?php echo esc_html( gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?>. <?php esc_html_e( 'Empresa ficticia creada como proyecto de portafolio; datos y testimonios de demostración.', 'brisa-solar' ); ?></p>
			<ul class="list-unstyled d-flex flex-wrap gap-3 m-0">
				<li><a href="<?php echo esc_url( brisa_url_aviso() ); ?>"><?php esc_html_e( 'Aviso de privacidad', 'brisa-solar' ); ?></a></li>
				<li><a href="#" data-abrir-consentimiento><?php esc_html_e( 'Preferencias de cookies', 'brisa-solar' ); ?></a></li>
			</ul>
		</div>
	</div>
</footer>

<a class="whatsapp-flotante d-none d-lg-grid" href="<?php echo esc_url( brisa_enlace_whatsapp() ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Escríbenos por WhatsApp', 'brisa-solar' ); ?>" data-evento="click_whatsapp" data-ubicacion="flotante">
	<?php brisa_el_icono( 'whatsapp-logo', 'fill' ); ?>
</a>

<nav class="barra-movil d-lg-none" aria-label="<?php esc_attr_e( 'Acciones rápidas', 'brisa-solar' ); ?>">
	<a href="<?php echo esc_url( 'tel:' . brisa_opcion( 'telefono' ) ); ?>" data-evento="click_telefono" data-ubicacion="barra_movil"><?php brisa_el_icono( 'phone' ); ?><?php esc_html_e( 'Llamar', 'brisa-solar' ); ?></a>
	<a href="<?php echo esc_url( brisa_enlace_whatsapp() ); ?>" target="_blank" rel="noopener" data-evento="click_whatsapp" data-ubicacion="barra_movil"><?php brisa_el_icono( 'whatsapp-logo' ); ?>WhatsApp</a>
	<a class="principal" href="<?php echo esc_url( brisa_ancla( 'cotizar' ) ); ?>" data-evento="click_cta" data-ubicacion="barra_movil"><?php brisa_el_icono( 'calendar-check' ); ?><?php esc_html_e( 'Cotizar', 'brisa-solar' ); ?></a>
</nav>

<section class="consentimiento" aria-labelledby="consentimiento-titulo" hidden data-consentimiento>
	<div class="consentimiento-caja">
		<h2 id="consentimiento-titulo" class="h6 mb-1"><?php esc_html_e( 'Cookies y medición', 'brisa-solar' ); ?></h2>
		<p class="mb-3 small">
			<?php esc_html_e( 'Usamos cookies para medir visitas y mostrar anuncios relevantes. Solo se activan si aceptas.', 'brisa-solar' ); ?>
			<a href="<?php echo esc_url( brisa_url_aviso() ); ?>"><?php esc_html_e( 'Aviso de privacidad', 'brisa-solar' ); ?></a>.
		</p>
		<div class="mb-3" role="group" aria-label="Tipos de cookies" data-consentimiento-detalle hidden>
			<div class="form-check">
				<input class="form-check-input" type="checkbox" id="c-necesarias" checked disabled>
				<label class="form-check-label" for="c-necesarias"><?php esc_html_e( 'Necesarias (siempre activas)', 'brisa-solar' ); ?></label>
			</div>
			<div class="form-check">
				<input class="form-check-input" type="checkbox" id="c-analitica" name="analitica">
				<label class="form-check-label" for="c-analitica"><?php esc_html_e( 'Analítica (Google Analytics)', 'brisa-solar' ); ?></label>
			</div>
			<div class="form-check">
				<input class="form-check-input" type="checkbox" id="c-publicidad" name="publicidad">
				<label class="form-check-label" for="c-publicidad"><?php esc_html_e( 'Publicidad (Google Ads y Meta)', 'brisa-solar' ); ?></label>
			</div>
		</div>
		<div class="d-flex flex-wrap gap-2">
			<button type="button" class="btn btn-primary btn-sm px-3" data-accion="aceptar"><?php esc_html_e( 'Aceptar todas', 'brisa-solar' ); ?></button>
			<button type="button" class="btn btn-outline-secondary btn-sm px-3" data-accion="rechazar"><?php esc_html_e( 'Solo necesarias', 'brisa-solar' ); ?></button>
			<button type="button" class="btn btn-link btn-sm" data-accion="configurar"><?php esc_html_e( 'Configurar', 'brisa-solar' ); ?></button>
		</div>
	</div>
</section>

<?php wp_footer(); ?>
</body>
</html>
