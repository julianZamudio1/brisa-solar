# Tema de WordPress

`wordpress/brisa-solar/` es un tema clásico, escrito a mano, que reproduce la landing sin constructores visuales. Usa el mismo CSS y el mismo JavaScript que la versión en Astro, compilados con `npm run tema`.

## Una sola fuente

```
site/src/styles/*.scss ─┐
site/src/scripts/*.ts  ─┼─ npm run tema ─▶ wordpress/brisa-solar/assets/  (CSS, JS, fuentes, íconos, fotos)
site/src/data/*.ts     ─┤                ─▶ wordpress/brisa-solar/inc/generado/  (datos y cálculo inicial en PHP)
site/public/api/inc/validacion.php ──────▶ wordpress/brisa-solar/inc/generado/validacion.php
```

Un cambio de diseño se hace una vez en `site/` y llega a las dos versiones. Los archivos de `assets/` e `inc/generado/` se versionan para que el tema se pueda instalar directo desde el repositorio, y llevan un aviso de no editarlos a mano.

El script también pasa el CSS por PurgeCSS contra las plantillas PHP: de 165 KB con todas las utilidades de Bootstrap quedan unos 53 KB.

## Estructura

```
brisa-solar/
├── style.css               Cabecera del tema
├── functions.php           Soportes, carga de CSS y JS (módulo diferido), precarga de fuentes
├── header.php, footer.php  Encabezado, pie, botones flotantes y aviso de cookies
├── front-page.php          Portada: arma la landing con template-parts/seccion-*.php
├── page.php                Páginas de contenido (aviso de privacidad)
├── page-gracias.php        Agradecimiento con folio (se elige por el slug "gracias")
├── index.php, 404.php
├── template-parts/         Una plantilla por sección de la landing
├── inc/
│   ├── configuracion.php   Personalizador: datos del negocio, portada, GTM
│   ├── tipos-de-contenido.php  Proyectos, testimonios, preguntas y prospectos
│   ├── formulario.php      Recepción del formulario por admin-post.php
│   ├── medicion.php        Consent Mode v2 y GTM
│   ├── seo.php             Meta descripción, Open Graph, JSON-LD
│   ├── plantillas.php      Íconos, imágenes con srcset, formato de pesos
│   ├── demo.php            Importador del contenido de ejemplo
│   └── generado/           Generado desde site/ (no editar)
└── demo/                   Fotos y textos del contenido de ejemplo
```

## Lo que edita el cliente

| Dónde | Qué |
|---|---|
| *Apariencia → Personalizar → Brisa Solar* | Teléfono, WhatsApp y su mensaje, correo, horario, dirección y coordenadas del mapa, redes, textos del hero, video de YouTube, descripción para buscadores, ID de GTM y correo de ventas |
| *Proyectos* | Foto destacada, lugar, kWp, paneles y recibo antes y después. El orden se controla con *Atributos → Orden* |
| *Testimonios* | Nombre (título), texto, detalle y casilla de destacado |
| *Preguntas frecuentes* | Pregunta (título) y respuesta. Alimentan el acordeón y el JSON-LD `FAQPage` |
| *Apariencia → Menús* | Menú principal (si no hay, se usan los anclajes de la landing) |
| *Apariencia → Personalizar → Identidad del sitio* | Logotipo propio en lugar de la marca del tema |

## Prospectos

Cada envío del formulario:

1. Pasa por las mismas validaciones que el endpoint PHP de la landing (`validar_prospecto()`), además de nonce, campo trampa, tiempo mínimo de 3 segundos y límite de 5 envíos por IP cada 10 minutos.
2. Se guarda como entrada privada del tipo *Prospecto*, con folio, datos, origen (calculadora o formulario) y UTM.
3. Envía un correo al responsable de ventas con un enlace directo al prospecto.
4. Dispara la acción `brisa_prospecto_guardado` para conectar un CRM o la API de Conversiones de Meta sin modificar el tema:

```php
add_action( 'brisa_prospecto_guardado', function ( array $datos, string $folio ) {
    wp_remote_post( 'https://crm.ejemplo.com/api/prospectos', array( 'body' => $datos ) );
}, 10, 2 );
```

En el administrador, *Prospectos* muestra teléfono, inmueble, recibo y origen en columnas, y cada ficha trae enlaces para llamar o abrir WhatsApp. El botón *Exportar CSV* descarga todo en un archivo que Excel abre con acentos, protegido contra inyección de fórmulas. Nadie puede crear prospectos a mano.

## Seguridad

- Todo lo que se imprime pasa por `esc_html`, `esc_attr` o `esc_url`, y todo lo que entra por `sanitize_*` o por las reglas de `validar_prospecto()`.
- Los campos personalizados se guardan con nonce, permiso `edit_post` y saneado por tipo.
- La exportación exige permiso y nonce.
- El nonce del formulario puede caducar si la portada se sirve desde una caché vieja. En ese caso el visitante ve un aviso para recargar, en lugar de perder el prospecto sin saberlo. Con un plugin de caché, conviene excluir la portada o renovar la caché cada 12 horas.

## Rendimiento

- En la portada no se cargan los estilos de bloques de WordPress, ni emojis, ni oEmbed.
- El JS se carga como módulo ES diferido con `wp_enqueue_script_module` (WordPress 6.5+).
- Las fuentes están autoalojadas y precargadas, y las fotos van en WebP con `srcset` y dimensiones fijas (sin saltos de diseño).
- Las fotos de proyectos usan los tamaños que genera WordPress (`brisa-tarjeta` de 860×645).

## Probarlo sin instalar nada

```bash
cd herramientas
npm install
npm run wordpress          # WordPress en http://127.0.0.1:9400 con el tema y la demo cargados
node probar-wordpress.mjs  # Envía el formulario y verifica el prospecto en el administrador
```

Usa [WordPress Playground](https://wordpress.github.io/wordpress-playground/): WordPress y PHP 8.3 corren en WebAssembly dentro de Node, sin XAMPP ni Docker. El tema se monta desde la carpeta, así que los cambios se ven al recargar.

`wordpress/blueprint.json` hace lo mismo en el navegador, instalando el tema directo desde GitHub en playground.wordpress.net.
