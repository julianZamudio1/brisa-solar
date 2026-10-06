<?php
/**
 * Beneficios en cuadrícula (Beneficios.astro).
 *
 * @package brisa-solar
 */

?>
<section class="seccion pt-0" aria-labelledby="beneficios-titulo">
	<div class="container">
		<h2 id="beneficios-titulo" class="seccion-titulo mb-5" data-revelar><?php esc_html_e( 'Lo que cambia cuando instalas con nosotros', 'brisa-solar' ); ?></h2>

		<div class="bento">
			<article class="celda celda-panel" data-revelar>
				<?php brisa_imagen( 'panel-detalle', array( 800, 1100 ), '(min-width: 992px) 62vw, 92vw', '', array( 'class' => 'celda-fondo', 'proporcion' => 0.6667 ) ); ?>
				<div class="celda-contenido">
					<h3 class="h4"><?php esc_html_e( 'Paneles y microinversores con 25 años de garantía', 'brisa-solar' ); ?></h3>
					<p class="mb-0"><?php esc_html_e( 'Usamos marcas de primer nivel con garantía de fábrica por escrito. Si algo falla, nosotros hacemos el reclamo.', 'brisa-solar' ); ?></p>
				</div>
			</article>

			<article class="celda celda-cielo" data-revelar style="--orden: 1">
				<?php brisa_el_icono( 'device-mobile', 'duotone', 'celda-icono' ); ?>
				<h3 class="h5"><?php esc_html_e( 'Monitoreo desde tu celular', 'brisa-solar' ); ?></h3>
				<p class="mb-0 text-body-secondary"><?php esc_html_e( 'Ves cuánto genera tu sistema cada día. Si la producción baja, te avisamos antes de que lo notes en el recibo.', 'brisa-solar' ); ?></p>
			</article>

			<article class="celda celda-ambar" data-revelar>
				<p class="cifra-grande" aria-hidden="true">100%</p>
				<h3 class="h5"><?php esc_html_e( 'Deducible para tu negocio', 'brisa-solar' ); ?></h3>
				<p class="mb-0"><?php esc_html_e( 'La Ley del ISR (art. 34, fracc. XIII) permite deducir en un año el equipo de energía renovable. Confírmalo con tu contador.', 'brisa-solar' ); ?></p>
			</article>

			<article class="celda celda-foto" data-revelar style="--orden: 1">
				<?php brisa_imagen( 'garantia-casco', array( 640 ), '(min-width: 992px) 30vw, 92vw', __( 'Casco de seguridad sobre un panel solar', 'brisa-solar' ), array( 'proporcion' => 0.6667 ) ); ?>
				<div class="celda-pie">
					<h3 class="h5 mb-1"><?php esc_html_e( 'Instaladores certificados', 'brisa-solar' ); ?></h3>
					<p class="mb-0 small"><?php esc_html_e( 'Personal con estándar CONOCER EC0586 y seguro de responsabilidad civil.', 'brisa-solar' ); ?></p>
				</div>
			</article>

			<article class="celda" data-revelar style="--orden: 2">
				<?php brisa_el_icono( 'hand-coins', 'duotone', 'celda-icono' ); ?>
				<h3 class="h5"><?php esc_html_e( 'Paga con lo que ahorras', 'brisa-solar' ); ?></h3>
				<p class="mb-0 text-body-secondary"><?php esc_html_e( 'Financiamiento de 12 a 48 meses. En muchos casos la mensualidad es menor que el recibo que dejas de pagar.', 'brisa-solar' ); ?></p>
			</article>
		</div>
	</div>
</section>
