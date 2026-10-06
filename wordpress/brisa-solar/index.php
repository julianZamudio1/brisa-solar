<?php
/**
 * Plantilla de respaldo (blog, archivos). La landing vive en front-page.php.
 *
 * @package brisa-solar
 */

get_header();
?>
<main id="contenido" class="seccion">
	<div class="container legal">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'mb-5' ); ?>>
					<h2 class="h3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php the_excerpt(); ?>
				</article>
			<?php endwhile; ?>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No hay contenido para mostrar.', 'brisa-solar' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
