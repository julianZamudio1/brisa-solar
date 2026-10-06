<?php
/**
 * Google Consent Mode v2 + Google Tag Manager.
 * El consentimiento se declara antes que GTM; las etiquetas (GA4, Pixel de Meta)
 * se configuran en el contenedor, no en el tema. Plan de medición: docs/medicion.md.
 *
 * @package brisa-solar
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_head',
	function () {
		?>
<script>
(function () {
	var d = document.documentElement;
	d.classList.add('js');
	var guardado = null;
	try { guardado = localStorage.getItem('tema'); } catch (e) {}
	var oscuro = guardado ? guardado === 'dark' : matchMedia('(prefers-color-scheme: dark)').matches;
	d.setAttribute('data-bs-theme', oscuro ? 'dark' : 'light');
})();
window.dataLayer = window.dataLayer || [];
function gtag() { dataLayer.push(arguments); }
var consentimiento = null;
try { consentimiento = JSON.parse(localStorage.getItem('consentimiento') || 'null'); } catch (e) {}
gtag('consent', 'default', {
	ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied',
	analytics_storage: 'denied', functionality_storage: 'granted', security_storage: 'granted',
	wait_for_update: 500
});
if (consentimiento) {
	gtag('consent', 'update', {
		analytics_storage: consentimiento.analitica ? 'granted' : 'denied',
		ad_storage: consentimiento.publicidad ? 'granted' : 'denied',
		ad_user_data: consentimiento.publicidad ? 'granted' : 'denied',
		ad_personalization: consentimiento.publicidad ? 'granted' : 'denied'
	});
}
</script>
		<?php
		$gtm = brisa_opcion( 'gtm_id' );
		if ( $gtm ) {
			?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s);j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',<?php echo wp_json_encode( $gtm ); ?>);</script>
			<?php
		}
	},
	1
);

add_action(
	'wp_body_open',
	function () {
		$gtm = brisa_opcion( 'gtm_id' );
		if ( $gtm ) {
			printf(
				'<noscript><iframe src="%s" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>',
				esc_url( 'https://www.googletagmanager.com/ns.html?id=' . $gtm )
			);
		}
	}
);
