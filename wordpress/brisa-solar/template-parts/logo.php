<?php
/**
 * Logotipo: el personalizado si se subió uno, si no la marca del tema.
 *
 * @package brisa-solar
 */

$logo = get_theme_mod( 'custom_logo' );
if ( $logo ) {
	echo wp_get_attachment_image( $logo, 'full', false, array( 'class' => 'logo-imagen', 'alt' => get_bloginfo( 'name' ) ) );
	return;
}
?>
<span class="logo">
	<svg viewBox="0 0 32 32" width="32" height="32" aria-hidden="true">
		<rect class="fondo" width="32" height="32" rx="9" fill="#10202b"></rect>
		<path d="M8.5 19.5a7.5 7.5 0 0 1 15 0z" fill="#f4a51c"></path>
		<rect x="6" y="22" width="20" height="2" rx="1" fill="#f4a51c" opacity=".55"></rect>
	</svg>
	<span>Brisa<strong>Solar</strong></span>
</span>
