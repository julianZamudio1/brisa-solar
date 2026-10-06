<?php
/**
 * Sección principal (Hero.astro).
 *
 * @package brisa-solar
 */

$garantias = array(
	'shield-check'   => __( '25 años de garantía en paneles', 'brisa-solar' ),
	'file-text'      => __( 'Trámite con CFE incluido', 'brisa-solar' ),
	'credit-card'    => __( 'Financiamiento de 12 a 48 meses', 'brisa-solar' ),
	'calendar-check' => __( 'Instalación en 1 o 2 días', 'brisa-solar' ),
);
?>
<section class="hero" aria-labelledby="hero-titulo">
	<div class="container">
		<div class="row align-items-center gy-5">
			<div class="col-lg-6 hero-texto">
				<span class="etiqueta"><?php echo esc_html( brisa_opcion( 'hero_etiqueta' ) ); ?></span>
				<h1 id="hero-titulo"><?php echo esc_html( brisa_opcion( 'hero_titulo' ) ); ?></h1>
				<p class="lead"><?php echo esc_html( brisa_opcion( 'hero_texto' ) ); ?></p>
				<div class="d-flex flex-wrap gap-3">
					<a class="btn btn-primary btn-lg" href="#calculadora" data-evento="click_cta" data-ubicacion="hero"><?php esc_html_e( 'Calcular mi ahorro', 'brisa-solar' ); ?></a>
					<a class="btn btn-whatsapp btn-lg d-inline-flex align-items-center gap-2" href="<?php echo esc_url( brisa_enlace_whatsapp() ); ?>" target="_blank" rel="noopener" data-evento="click_whatsapp" data-ubicacion="hero">
						<?php brisa_el_icono( 'whatsapp-logo', 'fill' ); ?>
						WhatsApp
					</a>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="foto hero-foto">
					<?php
					brisa_imagen(
						'hero-instalador',
						array( 720, 1280 ),
						'(min-width: 1400px) 620px, (min-width: 992px) 46vw, 92vw',
						__( 'Instalador con arnés fijando un panel solar en el techo de una casa', 'brisa-solar' ),
						array(
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'proporcion'    => 0.5625,
						)
					);
					?>
				</div>
			</div>
		</div>

		<ul class="garantias list-unstyled" aria-label="<?php esc_attr_e( 'Lo que incluye cada instalación', 'brisa-solar' ); ?>">
			<?php foreach ( $garantias as $icono => $texto ) : ?>
				<li><?php brisa_el_icono( $icono, 'duotone' ); ?><span><?php echo esc_html( $texto ); ?></span></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
