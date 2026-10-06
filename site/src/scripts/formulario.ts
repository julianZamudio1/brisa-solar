import { idEvento, leerCampana, medir } from './medicion';

const MENSAJES: Record<string, Partial<Record<keyof ValidityState, string>>> = {
  nombre: { valueMissing: 'Escribe tu nombre.', tooShort: 'Escribe tu nombre completo.' },
  telefono: {
    valueMissing: 'Necesitamos un teléfono para confirmar la visita.',
    patternMismatch: 'Escribe un teléfono de 10 dígitos.',
  },
  correo: { typeMismatch: 'Revisa el correo, parece incompleto.' },
  codigo_postal: {
    valueMissing: 'Escribe tu código postal.',
    patternMismatch: 'El código postal tiene 5 dígitos.',
  },
  recibo_bimestral: {
    valueMissing: 'Escribe el monto aproximado de tu recibo.',
    rangeUnderflow: 'El monto mínimo es de $100.',
    rangeOverflow: 'Revisa el monto, parece demasiado alto.',
    badInput: 'Escribe solo números.',
  },
  acepta_privacidad: { valueMissing: 'Debes aceptar el aviso de privacidad para continuar.' },
};

type Campo = HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;

function soloDigitos(valor: string) {
  return valor.replace(/\D/g, '');
}

/** Valida con la API nativa del navegador y agrega reglas propias (10 dígitos reales). */
function validarCampo(campo: Campo): string {
  if (campo.name === 'telefono' && campo.value) {
    const digitos = soloDigitos(campo.value).replace(/^52/, '');
    campo.setCustomValidity(digitos.length === 10 ? '' : 'Escribe un teléfono de 10 dígitos.');
  }
  if (campo.validity.valid) return '';
  if (campo.validity.customError) return campo.validationMessage;
  const mensajes = MENSAJES[campo.name] ?? {};
  for (const clave of Object.keys(mensajes) as (keyof ValidityState)[]) {
    if (campo.validity[clave]) return mensajes[clave]!;
  }
  return campo.validationMessage;
}

function pintarError(campo: Campo, mensaje: string) {
  const error = document.getElementById(`${campo.id}-error`);
  campo.classList.toggle('is-invalid', Boolean(mensaje));
  campo.setAttribute('aria-invalid', String(Boolean(mensaje)));
  if (error) error.textContent = mensaje;
}

export function iniciarFormulario(form: HTMLFormElement) {
  const campos = [...form.querySelectorAll<Campo>('input:not([type=hidden]), select, textarea')].filter(
    (c) => c.name !== 'sitio_web',
  );
  const boton = form.querySelector<HTMLButtonElement>('button[type=submit]')!;
  const alerta = form.querySelector<HTMLElement>('[data-error-envio]')!;
  const tocados = new Set<string>();
  let iniciado = false;

  form.querySelector<HTMLInputElement>('[data-inicio]')!.value = String(Date.now());

  // Valida al salir del campo y, si ya mostró error, mientras corrige.
  for (const campo of campos) {
    campo.addEventListener('blur', () => {
      tocados.add(campo.name);
      pintarError(campo, validarCampo(campo));
    });
    campo.addEventListener('input', () => {
      if (tocados.has(campo.name)) pintarError(campo, validarCampo(campo));
    });
  }

  form.addEventListener('focusin', () => {
    if (iniciado) return;
    iniciado = true;
    medir('formulario_inicio', { formulario: 'cotizacion' });
  });

  // La calculadora pasa su resultado al formulario.
  document.addEventListener('calculo:cotizar', (e) => {
    const { reciboBimestral, tipo } = (e as CustomEvent).detail;
    (form.elements.namedItem('recibo_bimestral') as HTMLInputElement).value = String(reciboBimestral);
    (form.elements.namedItem('tipo_inmueble') as HTMLSelectElement).value = tipo;
    (form.elements.namedItem('origen') as HTMLInputElement).value = 'calculadora';
    setTimeout(() => form.querySelector<HTMLInputElement>('#f-nombre')?.focus({ preventScroll: true }), 400);
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    alerta.classList.add('d-none');

    const errores = campos.map((c) => [c, validarCampo(c)] as const).filter(([, m]) => m);
    campos.forEach((c) => pintarError(c, validarCampo(c)));
    if (errores.length) {
      errores[0][0].focus();
      medir('formulario_error', { campos: errores.map(([c]) => c.name).join(',') });
      return;
    }

    const id = idEvento();
    (form.elements.namedItem('id_evento') as HTMLInputElement).value = id;
    const datos = new FormData(form);
    for (const [clave, valor] of Object.entries(leerCampana())) datos.set(clave, valor);

    boton.disabled = true;
    boton.setAttribute('aria-busy', 'true');

    try {
      // Se lee el atributo: en WordPress un campo name="action" tapa la propiedad form.action.
      const url = new URL(form.getAttribute('action') ?? '', location.href).href;
      const folio = form.dataset.modoDemo ? await simularEnvio() : await enviar(url, datos);
      const recibo = Number(datos.get('recibo_bimestral'));
      medir('generate_lead', {
        formulario: 'cotizacion',
        tipo_inmueble: String(datos.get('tipo_inmueble')),
        recibo_bimestral: recibo,
        origen: String(datos.get('origen') || 'formulario'),
        // Valor del prospecto para pujas en Ads: el ahorro anual estimado es una buena aproximación.
        value: Math.round(recibo * 6 * 0.9),
        currency: 'MXN',
        event_id: id,
      });
      const destino = new URL(form.dataset.gracias!, location.href);
      destino.searchParams.set('folio', folio);
      // Da tiempo a que GTM despache las etiquetas antes de cambiar de página.
      setTimeout(() => location.assign(destino), 300);
    } catch (error) {
      // Mensaje del servidor si lo hay (p. ej. "La página caducó. Recárgala…"); si no, el genérico.
      const detalle = alerta.querySelector<HTMLElement>('[data-error-detalle]');
      if (detalle) detalle.textContent = error instanceof ErrorServidor ? `${error.message} ` : '';
      alerta.classList.remove('d-none');
      boton.disabled = false;
      boton.removeAttribute('aria-busy');
      medir('formulario_fallo_envio', { formulario: 'cotizacion' });
    }
  });
}

class ErrorServidor extends Error {}

async function enviar(url: string, datos: FormData): Promise<string> {
  const respuesta = await fetch(url, {
    method: 'POST',
    body: datos,
    headers: { Accept: 'application/json' },
  });
  const json = await respuesta.json().catch(() => ({}));
  if (!respuesta.ok || !json.ok) {
    throw json.error ? new ErrorServidor(json.error) : new Error(`HTTP ${respuesta.status}`);
  }
  return json.folio as string;
}

async function simularEnvio(): Promise<string> {
  await new Promise((r) => setTimeout(r, 700));
  return `DEMO-${Math.floor(1000 + Math.random() * 9000)}`;
}

