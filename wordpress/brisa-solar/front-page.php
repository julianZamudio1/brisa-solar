<?php
/**
 * Portada: la landing completa, sección por sección.
 *
 * @package brisa-solar
 */

get_header();
?>
<main id="contenido">
	<?php
	foreach ( array( 'hero', 'calculadora', 'como-funciona', 'beneficios', 'proyectos', 'testimonios', 'preguntas', 'cotizar' ) as $seccion ) {
		get_template_part( 'template-parts/seccion', $seccion );
	}
	?>
</main>
<?php
get_footer();
