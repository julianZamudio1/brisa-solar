<?php
/**
 * Páginas de contenido (por ejemplo el aviso de privacidad).
 *
 * @package brisa-solar
 */

get_header();
?>
<main id="contenido" class="seccion">
	<?php while ( have_posts() ) : the_post(); ?>
		<article class="container legal">
			<h1 class="seccion-titulo mb-4"><?php the_title(); ?></h1>
			<div class="contenido-editor"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
