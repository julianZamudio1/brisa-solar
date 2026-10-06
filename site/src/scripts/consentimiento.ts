import { medir } from './medicion';

// La sección puede no existir (por ejemplo en las páginas internas del tema de WordPress).
if (document.querySelector('[data-consentimiento]')) iniciarConsentimiento();

function iniciarConsentimiento() {
  type Preferencias = { analitica: boolean; publicidad: boolean };
  // gtag necesita el objeto arguments (no un arreglo) para que GTM reconozca el comando.
  function gtag(..._args: unknown[]) {
    // eslint-disable-next-line prefer-rest-params
    window.dataLayer.push(arguments as unknown as Record<string, unknown>);
  }

  const caja = document.querySelector<HTMLElement>('[data-consentimiento]')!;
  const detalle = caja.querySelector<HTMLElement>('[data-consentimiento-detalle]')!;

  let guardado: Preferencias | null = null;
  try {
    guardado = JSON.parse(localStorage.getItem('consentimiento') ?? 'null');
  } catch {
    guardado = null;
  }
  if (!guardado) caja.hidden = false;

  function aplicar(pref: Preferencias) {
    gtag('consent', 'update', {
      analytics_storage: pref.analitica ? 'granted' : 'denied',
      ad_storage: pref.publicidad ? 'granted' : 'denied',
      ad_user_data: pref.publicidad ? 'granted' : 'denied',
      ad_personalization: pref.publicidad ? 'granted' : 'denied',
    });
    try {
      localStorage.setItem('consentimiento', JSON.stringify(pref));
    } catch {
      // Sin almacenamiento se volverá a preguntar en la siguiente visita.
    }
    medir('consentimiento_actualizado', pref);
    caja.hidden = true;
  }

  caja.addEventListener('click', (e) => {
    const accion = (e.target as HTMLElement).closest<HTMLElement>('[data-accion]')?.dataset.accion;
    if (accion === 'aceptar') aplicar({ analitica: true, publicidad: true });
    if (accion === 'rechazar') {
      if (!detalle.hidden) {
        const marcada = (nombre: string) => detalle.querySelector<HTMLInputElement>(`[name="${nombre}"]`)!.checked;
        aplicar({ analitica: marcada('analitica'), publicidad: marcada('publicidad') });
      } else {
        aplicar({ analitica: false, publicidad: false });
      }
    }
    if (accion === 'configurar') {
      detalle.hidden = false;
      const boton = e.target as HTMLButtonElement;
      boton.hidden = true;
      caja.querySelector<HTMLElement>('[data-accion="rechazar"]')!.textContent = 'Guardar selección';
    }
  });

  // Enlace del pie de página para cambiar la decisión después.
  document.querySelectorAll('[data-abrir-consentimiento]').forEach((el) =>
    el.addEventListener('click', (e) => {
      e.preventDefault();
      caja.hidden = false;
    }),
  );
}
