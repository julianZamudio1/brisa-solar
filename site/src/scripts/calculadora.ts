import { calcular, pesos, type Region, type TipoInmueble } from '../lib/calculadora';
import { medir } from './medicion';

// La sección puede no existir (por ejemplo en las páginas internas del tema de WordPress).
if (document.querySelector('[data-calculadora]')) iniciarCalculadora();

function iniciarCalculadora() {
  const form = document.querySelector<HTMLFormElement>('[data-calculadora]')!;
  const rango = form.querySelector<HTMLInputElement>('#calc-recibo')!;
  const numero = form.querySelector<HTMLInputElement>('#calc-recibo-numero')!;
  const caja = document.querySelector<HTMLElement>('[data-resultado]')!;
  const fmt = new Intl.NumberFormat('es-MX', { maximumFractionDigits: 1 });
  const r = (clave: string) => caja.querySelector<HTMLElement>(`[data-r="${clave}"]`)!;

  let ultimo: ReturnType<typeof calcular> | null = null;
  let medido = false;
  let temporizador: number | undefined;

  function leer() {
    const datos = new FormData(form);
    return {
      reciboBimestral: Number(numero.value) || 0,
      tipo: datos.get('tipo') as TipoInmueble,
      region: datos.get('region') as Region,
    };
  }

  function pintar() {
    const entrada = leer();
    const res = calcular(entrada);
    ultimo = res;
    r('ahorroAnual').textContent = pesos(res.ahorroAnual);
    r('recibo').textContent = pesos(entrada.reciboBimestral);
    r('reciboNuevo').textContent = pesos(res.reciboNuevo);
    r('paneles').textContent = String(res.paneles);
    r('potenciaKwp').textContent = fmt.format(res.potenciaKwp);
    r('inversion').textContent = `${pesos(res.inversion.minima)} a ${pesos(res.inversion.maxima)}`;
    r('retornoAnios').textContent = Number.isFinite(res.retornoAnios) ? fmt.format(res.retornoAnios) : 'más de 25';
    r('ahorro25Anios').textContent = pesos(res.ahorro25Anios);
    r('co2').textContent = fmt.format(res.co2EvitadoToneladasAnio);
    caja.querySelector<HTMLElement>('[data-aviso-dac]')!.hidden = !res.tarifaDac;
    caja.querySelector<HTMLElement>('[data-aviso-bajo]')!.hidden = res.viable;
    rango.setAttribute('aria-valuetext', `${entrada.reciboBimestral.toLocaleString('es-MX')} pesos`);

    // Un solo evento por interacción, no uno por cada pixel que se mueve la barra.
    clearTimeout(temporizador);
    temporizador = window.setTimeout(() => {
      medir(medido ? 'calculadora_ajuste' : 'calculadora_uso', {
        recibo_bimestral: entrada.reciboBimestral,
        tipo_inmueble: entrada.tipo,
        region: entrada.region,
        paneles: res.paneles,
        ahorro_anual: res.ahorroAnual,
      });
      medido = true;
    }, 800);
  }

  rango.addEventListener('input', () => {
    numero.value = rango.value;
    pintar();
  });
  numero.addEventListener('input', () => {
    rango.value = String(Math.min(Number(numero.value) || 0, Number(rango.max)));
    pintar();
  });
  form.addEventListener('change', pintar);
  form.addEventListener('submit', (e) => e.preventDefault());

  // Lleva el cálculo al formulario de cotización.
  document.querySelector('[data-cotizar-calculo]')?.addEventListener('click', () => {
    const entrada = leer();
    document.dispatchEvent(new CustomEvent('calculo:cotizar', { detail: { ...entrada, resultado: ultimo } }));
    medir('click_cta', { ubicacion: 'calculadora', texto: 'Quiero esta cotización' });
  });
}
