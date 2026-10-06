<?php
/**
 * Calculadora de ahorro (Calculadora.astro). Los valores iniciales salen del mismo
 * cálculo en TypeScript, generado en el build (inc/generado/calculadora.php).
 *
 * @package brisa-solar
 */

$calc   = brisa_calculadora();
$ini    = $calc['inicial'];
$s      = $calc['supuestos'];
$t      = $calc['tarifa']['domestica'];
$hsp    = $calc['hsp'];
$regiones = array(
	'norte'  => __( 'Norte (Son., Chih., N.L.)', 'brisa-solar' ),
	'centro' => __( 'Centro y Bajío (Qro., Gto., CDMX)', 'brisa-solar' ),
	'sur'    => __( 'Sur y Sureste (Ver., Tab., Yuc.)', 'brisa-solar' ),
);
$metricas = array(
	array( __( 'Paneles de 550 W', 'brisa-solar' ), '<span data-r="paneles">' . (int) $ini['paneles'] . '</span>' ),
	array( __( 'Potencia del sistema', 'brisa-solar' ), '<span data-r="potenciaKwp">' . esc_html( brisa_numero( $ini['potenciaKwp'] ) ) . '</span> kWp' ),
	array( __( 'Inversión aproximada', 'brisa-solar' ), '<span data-r="inversion">' . esc_html( brisa_pesos( $ini['inversion']['minima'] ) . ' a ' . brisa_pesos( $ini['inversion']['maxima'] ) ) . '</span>' ),
	array( __( 'Recuperas tu inversión en', 'brisa-solar' ), '<span data-r="retornoAnios">' . esc_html( brisa_numero( $ini['retornoAnios'] ) ) . '</span> años' ),
	array( __( 'Ahorro en 25 años', 'brisa-solar' ), '<span data-r="ahorro25Anios">' . esc_html( brisa_pesos( $ini['ahorro25Anios'] ) ) . '</span>' ),
	array( __( 'CO₂ evitado al año', 'brisa-solar' ), '<span data-r="co2">' . esc_html( brisa_numero( $ini['co2EvitadoToneladasAnio'] ) ) . '</span> t' ),
);
?>
<section id="calculadora" class="seccion calculadora" aria-labelledby="calc-titulo">
	<div class="container">
		<div class="row gy-5 gx-lg-5 align-items-start">
			<div class="col-lg-5" data-revelar>
				<h2 id="calc-titulo" class="seccion-titulo mb-3"><?php esc_html_e( '¿Cuánto ahorrarías con paneles?', 'brisa-solar' ); ?></h2>
				<p class="seccion-texto mb-4"><?php esc_html_e( 'Mueve la barra con el monto de tu último recibo de CFE. El cálculo es inmediato y no pedimos tus datos para verlo.', 'brisa-solar' ); ?></p>

				<form class="calc-form" data-calculadora novalidate>
					<div class="mb-4">
						<div class="d-flex justify-content-between align-items-end mb-2">
							<label for="calc-recibo" class="form-label m-0"><?php esc_html_e( 'Recibo bimestral', 'brisa-solar' ); ?></label>
							<div class="monto">
								<span aria-hidden="true">$</span>
								<input type="number" id="calc-recibo-numero" name="recibo_numero" inputmode="numeric" min="0" max="200000" step="50" value="2500" aria-label="<?php esc_attr_e( 'Monto del recibo bimestral en pesos', 'brisa-solar' ); ?>">
							</div>
						</div>
						<input type="range" class="form-range" id="calc-recibo" name="recibo" min="300" max="15000" step="50" value="2500" aria-valuetext="2,500 pesos">
						<div class="d-flex justify-content-between small text-body-secondary"><span>$300</span><span><?php esc_html_e( '$15,000 o más', 'brisa-solar' ); ?></span></div>
					</div>

					<fieldset class="mb-4">
						<legend class="form-label"><?php esc_html_e( '¿Dónde se instalaría?', 'brisa-solar' ); ?></legend>
						<div class="segmentado">
							<input type="radio" class="btn-check" name="tipo" id="calc-casa" value="casa" checked>
							<label for="calc-casa"><?php brisa_el_icono( 'house-line' ); ?><?php esc_html_e( 'Casa', 'brisa-solar' ); ?></label>
							<input type="radio" class="btn-check" name="tipo" id="calc-negocio" value="negocio">
							<label for="calc-negocio"><?php brisa_el_icono( 'storefront' ); ?><?php esc_html_e( 'Negocio', 'brisa-solar' ); ?></label>
						</div>
					</fieldset>

					<div>
						<label for="calc-region" class="form-label"><?php esc_html_e( 'Región', 'brisa-solar' ); ?></label>
						<select id="calc-region" name="region" class="form-select form-select-lg">
							<?php foreach ( $regiones as $valor => $texto ) : ?>
								<option value="<?php echo esc_attr( $valor ); ?>" <?php selected( $valor, 'centro' ); ?>><?php echo esc_html( $texto ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<button type="submit" class="visually-hidden"><?php esc_html_e( 'Calcular', 'brisa-solar' ); ?></button>
				</form>
			</div>

			<div class="col-lg-7" data-revelar style="--orden: 1">
				<div class="resultado" data-resultado aria-live="polite">
					<div class="resultado-principal">
						<p class="m-0 text-body-secondary"><?php esc_html_e( 'Ahorro estimado al año', 'brisa-solar' ); ?></p>
						<p class="cifra m-0" data-r="ahorroAnual"><?php echo esc_html( brisa_pesos( $ini['ahorroAnual'] ) ); ?></p>
						<p class="m-0">
							<?php
							printf(
								/* translators: 1: recibo actual, 2: recibo nuevo */
								esc_html__( 'Tu recibo pasaría de %1$s a %2$s al bimestre.', 'brisa-solar' ),
								'<strong data-r="recibo">' . esc_html( brisa_pesos( 2500 ) ) . '</strong>',
								'<strong data-r="reciboNuevo">' . esc_html( brisa_pesos( $ini['reciboNuevo'] ) ) . '</strong>'
							);
							?>
						</p>
					</div>

					<dl class="metricas">
						<?php foreach ( $metricas as list( $etiqueta, $valor ) ) : ?>
							<div>
								<dt><?php echo esc_html( $etiqueta ); ?></dt>
								<dd><?php echo $valor; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado al armar $metricas. ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>

					<p class="aviso aviso-dac" data-aviso-dac hidden>
						<?php brisa_el_icono( 'warning-circle' ); ?>
						<span><?php esc_html_e( 'Tu recibo indica tarifa DAC, la más cara de CFE. Es el caso donde los paneles se pagan más rápido.', 'brisa-solar' ); ?></span>
					</p>
					<p class="aviso" data-aviso-bajo hidden>
						<?php brisa_el_icono( 'info' ); ?>
						<span><?php printf( esc_html__( 'Con un recibo menor a %s el ahorro suele ser pequeño. Escríbenos y te decimos con honestidad si te conviene.', 'brisa-solar' ), esc_html( brisa_pesos( $s['reciboMinimoRecomendado'] ) ) ); ?></span>
					</p>

					<div class="d-flex flex-wrap align-items-center gap-3 mt-4">
						<a class="btn btn-primary btn-lg" href="#cotizar" data-cotizar-calculo><?php esc_html_e( 'Quiero esta cotización', 'brisa-solar' ); ?></a>
						<span class="small text-body-secondary"><?php esc_html_e( 'Estimación ilustrativa. La cifra final sale de la visita técnica.', 'brisa-solar' ); ?></span>
					</div>

					<details class="supuestos">
						<summary><?php esc_html_e( 'Cómo calculamos', 'brisa-solar' ); ?></summary>
						<ul class="small text-body-secondary mb-0">
							<li><?php printf( esc_html__( 'Convertimos tu recibo a kWh con los bloques de la tarifa 1 de CFE (básico $%1$s, intermedio $%2$s y excedente $%3$s por kWh, IVA incluido), o con la tarifa DAC o PDBT si aplica.', 'brisa-solar' ), esc_html( number_format( $t['basico']['precio'], 2 ) ), esc_html( number_format( $t['intermedio']['precio'], 2 ) ), esc_html( number_format( $t['excedente']['precio'], 2 ) ) ); ?></li>
							<li><?php printf( esc_html__( 'Horas solares pico: norte %1$s, centro %2$s, sur %3$s. Rendimiento del sistema: %4$s%%.', 'brisa-solar' ), esc_html( $hsp['norte'] ), esc_html( $hsp['centro'] ), esc_html( $hsp['sur'] ), esc_html( $s['rendimiento'] * 100 ) ); ?></li>
							<li><?php printf( esc_html__( 'Con medición neta pagas solo el mínimo de CFE. Suponemos un alza de tarifa de %1$s%% anual y una pérdida de %2$s%% anual en los paneles.', 'brisa-solar' ), esc_html( $s['incrementoTarifaAnual'] * 100 ), esc_html( $s['degradacionAnual'] * 100 ) ); ?></li>
						</ul>
					</details>
				</div>
			</div>
		</div>
	</div>
</section>
