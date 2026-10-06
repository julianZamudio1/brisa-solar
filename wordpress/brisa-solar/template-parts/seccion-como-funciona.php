<?php
/**
 * Proceso y video (ComoFunciona.astro + VideoYoutube.astro).
 *
 * @package brisa-solar
 */

$pasos = array(
	array( 'ruler', __( 'Visita técnica', 'brisa-solar' ), __( 'Revisamos tu techo, la orientación, las sombras y tu historial de consumo. Sin costo.', 'brisa-solar' ) ),
	array( 'blueprint', __( 'Diseño y cotización', 'brisa-solar' ), __( 'Te entregamos el diseño del sistema, la cotización cerrada y opciones de pago.', 'brisa-solar' ) ),
	array( 'wrench', __( 'Instalación', 'brisa-solar' ), __( 'Nuestro equipo instala paneles, inversor y protecciones en uno o dos días.', 'brisa-solar' ) ),
	array( 'plugs-connected', __( 'Interconexión con CFE', 'brisa-solar' ), __( 'Hacemos el trámite y CFE cambia tu medidor por uno bidireccional.', 'brisa-solar' ) ),
);
$video  = brisa_opcion( 'video_youtube' );
$titulo = __( '¿Cómo funcionan los paneles solares?', 'brisa-solar' );
?>
<section id="como-funciona" class="seccion" aria-labelledby="como-titulo">
	<div class="container">
		<h2 id="como-titulo" class="seccion-titulo mb-3" data-revelar><?php esc_html_e( 'De la visita al ahorro en tres semanas', 'brisa-solar' ); ?></h2>
		<p class="seccion-texto mb-5" data-revelar><?php esc_html_e( 'Nos encargamos de todo el proceso. Tú solo eliges la fecha de la visita.', 'brisa-solar' ); ?></p>

		<ol class="pasos list-unstyled">
			<?php foreach ( $pasos as $i => list( $icono, $paso, $texto ) ) : ?>
				<li data-revelar style="--orden: <?php echo (int) $i; ?>">
					<span class="paso-icono"><?php brisa_el_icono( $icono ); ?></span>
					<h3 class="h5 mt-3 mb-2"><?php echo esc_html( $paso ); ?></h3>
					<p class="text-body-secondary mb-0"><?php echo esc_html( $texto ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>

		<?php if ( $video ) : ?>
			<figure class="mt-5 mb-0" data-revelar>
				<div class="video foto" data-video="<?php echo esc_url( "https://www.youtube-nocookie.com/embed/$video?autoplay=1&rel=0&cc_load_policy=1&cc_lang_pref=es&hl=es" ); ?>" data-titulo="<?php echo esc_attr( $titulo ); ?>">
					<?php brisa_imagen( 'proceso-cableado', array( 960, 1280 ), '(min-width: 1200px) 1140px, 92vw', __( 'Manos con guantes conectando el cableado de un sistema solar en un techo', 'brisa-solar' ), array( 'proporcion' => 0.6667 ) ); ?>
					<button type="button" class="video-play" aria-label="<?php echo esc_attr( sprintf( __( 'Reproducir video: %s', 'brisa-solar' ), $titulo ) ); ?>">
						<?php brisa_el_icono( 'play', 'fill' ); ?>
					</button>
				</div>
				<figcaption class="small text-body-secondary mt-2"><?php esc_html_e( '¿Cómo funcionan los paneles solares? Video de TED-Ed con subtítulos en español.', 'brisa-solar' ); ?></figcaption>
			</figure>
		<?php endif; ?>
	</div>
</section>
