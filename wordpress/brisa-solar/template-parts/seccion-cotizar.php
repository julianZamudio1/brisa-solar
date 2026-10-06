<?php
/**
 * Formulario de cotización y contacto (Cotizar.astro). Envía a admin-post.php.
 *
 * @package brisa-solar
 */

$lat        = (float) brisa_opcion( 'lat' );
$lng        = (float) brisa_opcion( 'lng' );
$mapa       = sprintf( 'https://www.openstreetmap.org/export/embed.html?bbox=%F%%2C%F%%2C%F%%2C%F&layer=mapnik&marker=%F%%2C%F', $lng - 0.006, $lat - 0.004, $lng + 0.006, $lat + 0.004, $lat, $lng );
$como_llegar = sprintf( 'https://www.google.com/maps/dir/?api=1&destination=%F,%F', $lat, $lng );
$direccion  = sprintf( '%s, %s, %s, %s, C.P. %s', brisa_opcion( 'calle' ), brisa_opcion( 'colonia' ), brisa_opcion( 'ciudad' ), brisa_opcion( 'estado' ), brisa_opcion( 'cp' ) );
$opcional   = '<span class="fw-normal text-body-secondary">' . esc_html__( '(opcional)', 'brisa-solar' ) . '</span>';
?>
<section id="cotizar" class="seccion cotizar" aria-labelledby="cotizar-titulo">
	<div class="container">
		<div class="row gy-5 gx-lg-5">
			<div class="col-lg-7" data-revelar>
				<h2 id="cotizar-titulo" class="seccion-titulo mb-3"><?php esc_html_e( 'Agenda tu visita técnica gratis', 'brisa-solar' ); ?></h2>
				<p class="seccion-texto mb-4"><?php esc_html_e( 'Te llamamos en menos de un día hábil para confirmar la fecha. Sin compromiso de compra.', 'brisa-solar' ); ?></p>

				<form class="formulario superficie" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate data-formulario data-gracias="<?php echo esc_url( brisa_url_gracias() ); ?>">
					<input type="hidden" name="action" value="brisa_cotizacion">
					<?php wp_nonce_field( 'brisa_cotizacion', '_brisa_nonce', false ); ?>
					<div class="row g-3">
						<div class="col-md-6">
							<label class="form-label" for="f-nombre"><?php esc_html_e( 'Nombre completo', 'brisa-solar' ); ?></label>
							<input class="form-control form-control-lg" id="f-nombre" name="nombre" type="text" autocomplete="name" required minlength="3" maxlength="80" aria-describedby="f-nombre-error">
							<p class="invalid-feedback" id="f-nombre-error"></p>
						</div>
						<div class="col-md-6">
							<label class="form-label" for="f-telefono"><?php esc_html_e( 'Teléfono o WhatsApp', 'brisa-solar' ); ?></label>
							<input class="form-control form-control-lg" id="f-telefono" name="telefono" type="tel" inputmode="tel" autocomplete="tel" required pattern="[\d\s()+\-]{10,18}" aria-describedby="f-telefono-ayuda f-telefono-error">
							<p class="form-text m-0" id="f-telefono-ayuda"><?php esc_html_e( '10 dígitos, por ejemplo 442 123 4567.', 'brisa-solar' ); ?></p>
							<p class="invalid-feedback" id="f-telefono-error"></p>
						</div>
						<div class="col-md-6">
							<label class="form-label" for="f-correo"><?php esc_html_e( 'Correo', 'brisa-solar' ); ?> <?php echo $opcional; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
							<input class="form-control form-control-lg" id="f-correo" name="correo" type="email" autocomplete="email" maxlength="120" aria-describedby="f-correo-error">
							<p class="invalid-feedback" id="f-correo-error"></p>
						</div>
						<div class="col-md-6">
							<label class="form-label" for="f-cp"><?php esc_html_e( 'Código postal', 'brisa-solar' ); ?></label>
							<input class="form-control form-control-lg" id="f-cp" name="codigo_postal" type="text" inputmode="numeric" autocomplete="postal-code" required pattern="\d{5}" maxlength="5" aria-describedby="f-cp-error">
							<p class="invalid-feedback" id="f-cp-error"></p>
						</div>
						<div class="col-md-6">
							<label class="form-label" for="f-tipo"><?php esc_html_e( 'Tipo de inmueble', 'brisa-solar' ); ?></label>
							<select class="form-select form-select-lg" id="f-tipo" name="tipo_inmueble" required aria-describedby="f-tipo-error">
								<option value="casa"><?php esc_html_e( 'Casa', 'brisa-solar' ); ?></option>
								<option value="negocio"><?php esc_html_e( 'Negocio o industria', 'brisa-solar' ); ?></option>
							</select>
							<p class="invalid-feedback" id="f-tipo-error"></p>
						</div>
						<div class="col-md-6">
							<label class="form-label" for="f-recibo"><?php esc_html_e( 'Recibo bimestral aproximado', 'brisa-solar' ); ?></label>
							<div class="input-group input-group-lg has-validation">
								<span class="input-group-text">$</span>
								<input class="form-control" id="f-recibo" name="recibo_bimestral" type="number" inputmode="numeric" min="100" max="2000000" step="1" required aria-describedby="f-recibo-error">
								<p class="invalid-feedback" id="f-recibo-error"></p>
							</div>
						</div>
						<div class="col-12">
							<label class="form-label" for="f-mensaje"><?php esc_html_e( 'Comentarios', 'brisa-solar' ); ?> <?php echo $opcional; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
							<textarea class="form-control" id="f-mensaje" name="mensaje" rows="3" maxlength="600"></textarea>
						</div>

						<div class="trampa" aria-hidden="true">
							<label for="f-sitio"><?php esc_html_e( 'Sitio web', 'brisa-solar' ); ?></label>
							<input id="f-sitio" name="sitio_web" type="text" tabindex="-1" autocomplete="off">
						</div>
						<input type="hidden" name="inicio" value="" data-inicio>
						<input type="hidden" name="id_evento" value="">
						<input type="hidden" name="origen" value="">

						<div class="col-12">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" id="f-privacidad" name="acepta_privacidad" value="1" required aria-describedby="f-privacidad-error">
								<label class="form-check-label" for="f-privacidad">
									<?php
									printf(
										/* translators: %s: enlace al aviso de privacidad */
										esc_html__( 'Acepto el %s.', 'brisa-solar' ),
										'<a href="' . esc_url( brisa_url_aviso() ) . '" target="_blank">' . esc_html__( 'aviso de privacidad', 'brisa-solar' ) . '</a>'
									);
									?>
								</label>
								<p class="invalid-feedback" id="f-privacidad-error"></p>
							</div>
						</div>

						<div class="col-12">
							<div class="alert alert-danger d-none" role="alert" data-error-envio>
								<span data-error-detalle></span><?php esc_html_e( 'No pudimos enviar tu solicitud. Inténtalo de nuevo o escríbenos por', 'brisa-solar' ); ?>
								<a href="<?php echo esc_url( brisa_enlace_whatsapp() ); ?>" target="_blank" rel="noopener">WhatsApp</a>.
							</div>
							<button class="btn btn-primary btn-lg w-100 enviar" type="submit">
								<span class="enviar-texto"><?php esc_html_e( 'Agendar visita gratis', 'brisa-solar' ); ?></span>
								<span class="enviar-cargando" aria-hidden="true"><?php esc_html_e( 'Enviando…', 'brisa-solar' ); ?></span>
							</button>
						</div>
					</div>
				</form>
			</div>

			<aside class="col-lg-5" aria-label="<?php esc_attr_e( 'Otras formas de contacto', 'brisa-solar' ); ?>" data-revelar style="--orden: 1">
				<ul class="contacto list-unstyled">
					<li><?php brisa_el_icono( 'whatsapp-logo' ); ?><div><strong>WhatsApp</strong><a href="<?php echo esc_url( brisa_enlace_whatsapp() ); ?>" target="_blank" rel="noopener" data-evento="click_whatsapp" data-ubicacion="contacto"><?php esc_html_e( 'Enviar mensaje', 'brisa-solar' ); ?></a></div></li>
					<li><?php brisa_el_icono( 'phone' ); ?><div><strong><?php esc_html_e( 'Teléfono', 'brisa-solar' ); ?></strong><a href="<?php echo esc_url( 'tel:' . brisa_opcion( 'telefono' ) ); ?>" data-evento="click_telefono" data-ubicacion="contacto"><?php echo esc_html( brisa_opcion( 'telefono_visible' ) ); ?></a></div></li>
					<li><?php brisa_el_icono( 'envelope-simple' ); ?><div><strong><?php esc_html_e( 'Correo', 'brisa-solar' ); ?></strong><a href="<?php echo esc_url( 'mailto:' . brisa_opcion( 'correo' ) ); ?>" data-evento="click_correo" data-ubicacion="contacto"><?php echo esc_html( brisa_opcion( 'correo' ) ); ?></a></div></li>
					<li><?php brisa_el_icono( 'clock' ); ?><div><strong><?php esc_html_e( 'Horario', 'brisa-solar' ); ?></strong><span><?php echo esc_html( brisa_opcion( 'horario' ) ); ?></span></div></li>
				</ul>

				<div class="mapa foto" data-mapa="<?php echo esc_url( $mapa ); ?>">
					<div class="mapa-fachada">
						<?php brisa_el_icono( 'map-pin', 'duotone' ); ?>
						<address class="m-0"><?php echo esc_html( $direccion ); ?></address>
						<div class="d-flex flex-wrap justify-content-center gap-2">
							<button type="button" class="btn btn-outline-secondary btn-sm px-3" data-cargar-mapa><?php esc_html_e( 'Ver mapa', 'brisa-solar' ); ?></button>
							<a class="btn btn-outline-secondary btn-sm px-3" href="<?php echo esc_url( $como_llegar ); ?>" target="_blank" rel="noopener" data-evento="click_como_llegar" data-ubicacion="contacto"><?php esc_html_e( 'Cómo llegar', 'brisa-solar' ); ?></a>
						</div>
					</div>
				</div>
			</aside>
		</div>
	</div>
</section>
