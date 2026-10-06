# Rendimiento y SEO técnico

## Resultados de Lighthouse

Medido con Lighthouse 12 sobre el build de producción (`npm run build` + `astro preview`), sin compresión del servidor. Los reportes completos están en [`lighthouse/`](lighthouse/).

| | Rendimiento | Accesibilidad | Buenas prácticas | SEO |
|---|---|---|---|---|
| Móvil (Moto G emulado, 4G lenta) | 99 | 100 | 100 | 100 |
| Escritorio | 100 | 100 | 100 | 100 |

| Métrica | Móvil | Escritorio | Meta de Core Web Vitals |
|---|---|---|---|
| LCP (pintado del elemento más grande) | 2.1 s | 0.5 s | < 2.5 s |
| CLS (saltos de diseño) | 0 | 0 | < 0.1 |
| TBT (bloqueo del hilo principal) | 30 ms | 0 ms | < 200 ms (aproxima INP) |
| FCP | 1.3 s | 0.4 s | < 1.8 s |
| Peso total transferido | 199 KB | 337 KB | |

## Qué lo hace rápido

**Imágenes**
- Astro genera AVIF y WebP en 4 anchos con `<picture>` y `srcset`; el navegador baja solo el tamaño que necesita. La foto del hero en AVIF pesa de 9 a 42 KB, contra 550 KB del JPG original.
- La foto del hero lleva `loading="eager"` y `fetchpriority="high"`; las demás, `loading="lazy"`.
- Todas las imágenes tienen `width` y `height`, así que el espacio se reserva antes de cargar (CLS 0).

**CSS**
- Bootstrap se importa por módulos (sin carrusel, modal, dropdown, etc.) y PurgeCSS quita las utilidades que no aparecen en el código: de 165 KB a 52 KB, unos 10 KB comprimido.
- Va incrustado en el HTML: no hay una petición extra que bloquee el primer pintado.

**Fuentes**
- Autoalojadas con Fontsource (sin Google Fonts: sin conexión a otro dominio ni cookies de terceros), solo el subconjunto latino y en formato variable (un archivo por familia para todos los pesos).
- Precargadas con `<link rel="preload">` y `font-display: swap`.

**JavaScript**
- Sin frameworks de JS en el navegador. Astro empaqueta solo los módulos de cada sección, unos 14 KB en total.
- Sin `addEventListener('scroll')`: las animaciones de aparición, el scroll del 50/90% y las flechas del carrusel usan `IntersectionObserver`.
- La calculadora agrupa los eventos de medición: uno por interacción, no uno por cada pixel que se mueve la barra.

**Terceros bajo demanda**
- **YouTube:** se muestra un póster propio y el reproductor (unos 800 KB) carga solo al dar clic, desde `youtube-nocookie.com`.
- **Mapa:** OpenStreetMap carga al pulsar *Ver mapa*; *Cómo llegar* es un enlace simple.
- **GTM:** asíncrono; con el consentimiento denegado no deja cookies.

**Animación**
- Solo se animan `transform` y `opacity`. El texto del hero se anima sin pasar por `opacity: 0`, para que el navegador lo cuente para LCP desde el primer pintado.
- Todo respeta `prefers-reduced-motion`.
- El contenido es visible sin JavaScript: la clase `js` que activa las animaciones la pone un script en línea.

**Servidor (`.htaccess`)**
- Compresión `mod_deflate`, caché de un año e `immutable` para `/_astro/` (nombres con hash), HTML sin caché.

## SEO técnico

- HTML semántico: un solo `h1`, encabezados sin saltos de nivel (verificado en las pruebas), `main`, `nav`, `header`, `footer`, `address`, `figure`, `blockquote`, `details`.
- `title` y meta descripción únicos por página, `canonical`, `lang="es-MX"`, `theme-color` para claro y oscuro.
- Open Graph y Twitter Card con imagen de 1200×630 generada en el build.
- Datos estructurados JSON-LD: `HomeAndConstructionBusiness` (dirección, geo, horario, zona de servicio) y `FAQPage` generado desde las mismas preguntas del acordeón.
- `sitemap-index.xml` automático (sin la página de gracias) y `robots.txt` que apunta al sitemap y bloquea `/api/`.
- La página de gracias lleva `noindex`, así no aparece en Google ni infla las conversiones con visitas orgánicas.
- Página 404 propia con salida a la calculadora.
- HTML validado con `html-validate` (reglas recomendadas) en CI.

## Accesibilidad

- 0 fallas graves o críticas de axe-core (WCAG 2.1 AA), verificado en las pruebas.
- Contraste AA en ambos temas: el texto de los botones ámbar es oscuro (8:1).
- Enlace para saltar al contenido, foco visible y menú móvil que se cierra con Escape.
- Formularios con etiqueta visible arriba de cada campo, errores debajo ligados con `aria-describedby` y `aria-invalid`, y foco en el primer campo con error.
- La calculadora anuncia los resultados con `aria-live` y la barra describe su valor en pesos con `aria-valuetext`.
