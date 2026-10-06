# Plan de medición

La página no carga GA4 ni el Pixel de Meta directamente. Solo empuja eventos al `dataLayer`, y **Google Tag Manager** decide qué etiqueta dispara cada uno. Así, el equipo de campañas puede agregar o cambiar etiquetas sin tocar el código.

```
Página ──push──▶ dataLayer ──▶ GTM ──▶ GA4 (eventos y conversiones)
                                 ├──▶ Pixel de Meta (Lead, Contact)
                                 └──▶ Google Ads (conversión de prospecto)
```

## Consentimiento (Google Consent Mode v2)

Antes de que cargue GTM, el `<head>` declara todo como denegado:

```js
gtag('consent', 'default', {
  ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied',
  analytics_storage: 'denied', functionality_storage: 'granted', security_storage: 'granted',
  wait_for_update: 500,
});
```

El aviso de cookies tiene tres opciones: *Aceptar todas*, *Solo necesarias* y *Configurar* (analítica y publicidad por separado). La decisión:

1. Se envía con `gtag('consent', 'update', …)`.
2. Se guarda en `localStorage` y se vuelve a aplicar en el `<head>` en la siguiente visita, antes de GTM.
3. Se puede cambiar desde *Preferencias de cookies* en el pie de página.

Con el consentimiento denegado, GA4 y Google Ads trabajan en modo sin cookies (modelado de conversiones). El Pixel de Meta no tiene Consent Mode: su etiqueta en GTM debe exigir el consentimiento `ad_storage` (*Configuración avanzada → Configuración de consentimiento*).

## Eventos

| Evento | Cuándo | Parámetros |
|---|---|---|
| `calculadora_uso` | Primera vez que el visitante mueve la calculadora (espera 800 ms a que deje de moverla) | `recibo_bimestral`, `tipo_inmueble`, `region`, `paneles`, `ahorro_anual` |
| `calculadora_ajuste` | Cambios posteriores en la calculadora | Los mismos |
| `click_cta` | Clic en un botón de cotizar | `ubicacion` (`encabezado`, `hero`, `calculadora`, `barra_movil`), `texto` |
| `click_whatsapp` | Clic en cualquier enlace de WhatsApp | `ubicacion` (`hero`, `preguntas`, `contacto`, `flotante`, `barra_movil`, `gracias`) |
| `click_telefono` | Clic en un enlace `tel:` | `ubicacion` |
| `click_correo` | Clic en un enlace `mailto:` | `ubicacion` |
| `click_como_llegar` | Clic en *Cómo llegar* | `ubicacion` |
| `formulario_inicio` | Primer foco en el formulario | `formulario` |
| `formulario_error` | Envío con errores de validación | `campos` (lista separada por comas) |
| `formulario_fallo_envio` | El servidor no respondió bien | `formulario` |
| `generate_lead` | El servidor guardó el prospecto | `formulario`, `tipo_inmueble`, `recibo_bimestral`, `origen` (`calculadora` o `formulario`), `value`, `currency`, `event_id` |
| `video_reproducido` | Clic en reproducir el video | `titulo` |
| `pregunta_abierta` | Se abre una pregunta frecuente | `pregunta` |
| `carrusel_interaccion` | Primer uso de las flechas del carrusel | `carrusel` |
| `profundidad_scroll` | El visitante llega al 50% y al 90% de la página | `porcentaje` |
| `consentimiento_actualizado` | El visitante decide sobre cookies | `analitica`, `publicidad` |

`generate_lead` usa el nombre de [evento recomendado de GA4](https://support.google.com/analytics/answer/9267735) para que GA4 lo reconozca como conversión de prospecto sin configuración extra.

Los clics se miden de forma declarativa. Cualquier elemento con `data-evento` se mide sin escribir JavaScript:

```html
<a href="https://wa.me/…" data-evento="click_whatsapp" data-ubicacion="hero">WhatsApp</a>
```

## Valor del prospecto

`generate_lead` envía `value` = recibo bimestral × 6 × 0.9 (aproximación del ahorro anual) en `MXN`. Sirve para que Google Ads y Meta pujen más por prospectos con recibos altos. El equipo comercial puede cambiar la fórmula en `site/src/scripts/formulario.ts`.

## Deduplicación con la API de Conversiones de Meta

Cada envío genera un `event_id` (UUID) que viaja:

- en el `dataLayer`, para el Pixel (`eventID` en la etiqueta de GTM), y
- en el POST al servidor (`id_evento`), donde se guarda con el prospecto.

Si después se conecta la API de Conversiones (en WordPress con la acción `brisa_prospecto_guardado`), Meta descarta el duplicado porque ambos eventos comparten el id.

## Atribución (UTM)

En la primera página de la sesión se guardan `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`, `gclid` y `fbclid` en `sessionStorage`. Al enviar el formulario se adjuntan al prospecto, así que ventas ve en el correo y en el administrador de qué campaña llegó cada persona.

## Configuración del contenedor de GTM

**Variables (capa de datos):** `recibo_bimestral`, `tipo_inmueble`, `origen`, `value`, `currency`, `event_id`, `ubicacion`.

**Activadores (evento personalizado):** `generate_lead`, `calculadora_uso`, `click_whatsapp`, `click_telefono`.

**Etiquetas:**

| Etiqueta | Tipo | Activador | Consentimiento requerido |
|---|---|---|---|
| GA4 Configuración | Etiqueta de Google (`G-…`) | Initialization, todas las páginas | Integrado (Consent Mode) |
| GA4 generate_lead | Evento GA4 con `value`, `currency`, `tipo_inmueble`, `origen` | `generate_lead` | Integrado |
| GA4 calculadora_uso | Evento GA4 | `calculadora_uso` | Integrado |
| GA4 contacto | Evento GA4 `contact` con `metodo` = whatsapp o telefono | `click_whatsapp`, `click_telefono` | Integrado |
| Google Ads conversión | Seguimiento de conversiones con `value` y `transaction_id` = `event_id` | `generate_lead` | Integrado |
| Meta Pixel base | HTML personalizado (`fbq('init')`, `PageView`) | Todas las páginas | `ad_storage` |
| Meta Pixel Lead | HTML personalizado (`fbq('track','Lead',{value,currency},{eventID})`) | `generate_lead` | `ad_storage` |
| Meta Pixel Contact | HTML personalizado (`fbq('track','Contact')`) | `click_whatsapp` | `ad_storage` |

En GA4 se marca `generate_lead` como **evento clave** y se registran `tipo_inmueble` y `origen` como dimensiones personalizadas para comparar casas contra negocios y calculadora contra formulario directo.

## Cómo activarlo

- **Landing en Astro:** variable de entorno `PUBLIC_GTM_ID=GTM-XXXXXXX` al compilar.
- **WordPress:** *Apariencia → Personalizar → Brisa Solar → Medición y prospectos*.

## Depuración

Agrega `?depurar_medicion` a la URL y aparece un panel con cada evento que llega al `dataLayer`, en orden y con sus parámetros. Funciona sin GTM instalado, así que el etiquetado se puede revisar antes de tener acceso al contenedor del cliente. Con GTM ya instalado, el modo *Vista previa* de Tag Assistant muestra qué etiquetas disparó cada evento.

Las pruebas automáticas (`site/pruebas/landing.spec.ts`) verifican que `calculadora_uso`, `formulario_error`, `generate_lead`, `click_whatsapp` y las dos llamadas de consentimiento lleguen al `dataLayer` con sus parámetros.
