/**
 * Capa de medición. La página solo empuja eventos al dataLayer; GTM decide qué
 * etiqueta dispara cada uno (GA4, Pixel de Meta, Google Ads). Así marketing puede
 * cambiar etiquetas sin tocar el código. Plan completo en docs/medicion.md.
 */

type Parametros = Record<string, string | number | boolean | undefined>;

declare global {
  interface Window {
    dataLayer: Record<string, unknown>[];
  }
}

const CLAVES_CAMPANA = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid'];

window.dataLayer = window.dataLayer || [];

const depurar = new URLSearchParams(location.search).has('depurar_medicion');

export function medir(evento: string, parametros: Parametros = {}) {
  const dato = { event: evento, ...parametros };
  window.dataLayer.push(dato);
  if (depurar) mostrarEnPanel(dato);
}

/** Id único para deduplicar el mismo evento entre el Pixel y la API de Conversiones de Meta. */
export function idEvento() {
  return crypto.randomUUID?.() ?? `${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

/** Guarda los UTM de la primera visita de la sesión para adjuntarlos al prospecto. */
export function guardarCampana() {
  const params = new URLSearchParams(location.search);
  const encontrados = CLAVES_CAMPANA.filter((c) => params.has(c));
  if (!encontrados.length) return;
  try {
    if (sessionStorage.getItem('campana')) return;
    const campana = Object.fromEntries(encontrados.map((c) => [c, params.get(c) ?? '']));
    sessionStorage.setItem('campana', JSON.stringify(campana));
  } catch {
    // Almacenamiento bloqueado: se pierde la atribución, la página sigue funcionando.
  }
}

export function leerCampana(): Record<string, string> {
  try {
    return JSON.parse(sessionStorage.getItem('campana') ?? '{}');
  } catch {
    return {};
  }
}

/**
 * Clics declarativos: cualquier elemento con data-evento se mide sin JS extra.
 * <a data-evento="click_whatsapp" data-ubicacion="hero">
 */
export function escucharClics() {
  document.addEventListener('click', (e) => {
    const el = (e.target as HTMLElement).closest<HTMLElement>('[data-evento]');
    if (!el) return;
    medir(el.dataset.evento!, {
      ubicacion: el.dataset.ubicacion,
      texto: el.textContent?.trim().slice(0, 60),
    });
  });
}

/** Profundidad de scroll al 50% y 90% con IntersectionObserver (sin escuchar scroll). */
export function medirProfundidad() {
  for (const porcentaje of [50, 90]) {
    const marca = document.createElement('div');
    marca.style.cssText = `position:absolute;top:${porcentaje}%;height:1px;width:1px;pointer-events:none`;
    marca.setAttribute('aria-hidden', 'true');
    document.body.style.position ||= 'relative';
    document.body.append(marca);
    const obs = new IntersectionObserver((entradas) => {
      if (entradas.some((x) => x.isIntersecting)) {
        medir('profundidad_scroll', { porcentaje });
        obs.disconnect();
      }
    });
    obs.observe(marca);
  }
}

let panel: HTMLOListElement | null = null;

function mostrarEnPanel(dato: Record<string, unknown>) {
  if (!panel) {
    panel = document.createElement('ol');
    panel.className = 'panel-medicion';
    panel.setAttribute('aria-label', 'Eventos enviados al dataLayer');
    document.body.append(panel);
  }
  const item = document.createElement('li');
  const { event, ...resto } = dato;
  item.innerHTML = `<strong></strong><code></code>`;
  item.querySelector('strong')!.textContent = String(event);
  item.querySelector('code')!.textContent = JSON.stringify(resto);
  panel.prepend(item);
}
