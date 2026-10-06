# Sistema de diseño

Los valores viven en `site/src/styles/_tokens.scss` como variables de Bootstrap. Son los mismos que se capturarían como estilos y variables en Figma, de modo que diseño y código hablan el mismo idioma.

## Dirección

Landing de captación para dueños de casa y negocios en México que comparan instaladores. Tiene que transmitir confianza más que verse llamativa: neutros fríos, un solo color de acento para las acciones y fotos reales de instalaciones. Pensada primero para móvil, porque la mayoría del tráfico de Google Ads y Meta llega desde el teléfono.

## Color

| Token | Claro | Oscuro | Uso |
|---|---|---|---|
| `$ink` | `#10202b` | `#e6edf1` | Texto principal |
| `$ink-soft` | `#4a5a66` | `#9fb0bb` | Texto secundario |
| `$paper` | `#f4f7f8` | `#0b151c` | Fondo de página |
| `$surface` | `#fcfdfd` | `#111e27` | Tarjetas y formularios |
| `$sky` | `#e7f0f4` | `#0f1d26` | Secciones alternas |
| `$line` | `#dce3e7` | `#22323d` | Bordes |
| `$amber` | `#f4a51c` | `#f4a51c` | **Único acento**: botones de acción, íconos, foco |
| WhatsApp | `#1d7a4a` | `#1d7a4a` | Botón de WhatsApp (texto blanco 5.4:1) |

El ámbar lleva texto oscuro (8:1 de contraste) en lugar de blanco, que no pasaría AA. Ningún color es negro ni blanco puro. El modo oscuro sigue la preferencia del sistema y tiene botón para cambiarlo.

## Tipografía

| Rol | Fuente | Tamaño |
|---|---|---|
| Títulos | Bricolage Grotesque (variable) | h1: `clamp(2.375rem, 3.4vw, 3.75rem)`; h2: `clamp(2rem, 2.4vw, 3rem)` |
| Texto | Figtree (variable) | 17 px, interlineado 1.6, máximo 60–65 caracteres por línea |
| Cifras | Bricolage Grotesque 700–800 | Números tabulares para que no salten al cambiar |

## Forma y espacio

- **Regla de esquinas:** botones en píldora, tarjetas e imágenes de 16 px, campos de formulario de 10 px.
- **Ritmo vertical:** secciones de `clamp(4rem, 9vw, 7rem)`.
- **Márgenes:** contenedor máximo de 1280 px con 16 px de margen lateral en móvil.
- **Sombras:** suaves y teñidas del color de la tinta, solo en elementos elevados (resultado de la calculadora, formulario, aviso de cookies).

## Componentes

| Componente | Notas |
|---|---|
| Botón primario | Ámbar con texto oscuro, se hunde 1 px al presionar. Las etiquetas caben en una línea y cada intención tiene un solo texto: *Cotizar gratis*, *Calcular mi ahorro*, *Agendar visita gratis* |
| Botón de WhatsApp | Verde oscuro con el ícono de WhatsApp |
| Campo de formulario | Etiqueta arriba, ayuda y error abajo, foco ámbar |
| Segmentado | Casa o negocio, implementado como radios accesibles |
| Tarjeta de proyecto | Foto 4:3, lugar, kWp y recibo antes y después |
| Celdas de beneficios | Cuadrícula de 5 celdas con fondos distintos (foto, cielo, ámbar, superficie) |

## Estructura de la página

Cada sección usa un acomodo distinto para que la página no se sienta repetitiva:

1. **Hero:** texto y foto en dos columnas, con garantías debajo.
2. **Calculadora:** controles a la izquierda y tarjeta de resultado a la derecha.
3. **Proceso:** línea de tiempo de 4 pasos y video a todo lo ancho.
4. **Beneficios:** cuadrícula asimétrica.
5. **Proyectos:** carrusel horizontal que sale del contenedor.
6. **Testimonios:** una cita grande y dos chicas.
7. **Preguntas:** ayuda por WhatsApp junto a un acordeón.
8. **Contacto:** formulario, datos de contacto y mapa.

En móvil todo pasa a una columna y aparece una barra fija con *Llamar*, *WhatsApp* y *Cotizar*.

## Íconos y fotos

- Íconos de [Phosphor](https://phosphoricons.com) (una sola familia), incrustados como SVG.
- Fotos de Unsplash con su licencia; créditos en [creditos.md](creditos.md).
