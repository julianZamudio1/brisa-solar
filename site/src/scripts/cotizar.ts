import { iniciarFormulario } from './formulario';

const formulario = document.querySelector<HTMLFormElement>('[data-formulario]');
if (formulario) iniciarFormulario(formulario);

// El mapa de OpenStreetMap se carga hasta que el visitante lo pide.
document.querySelector('[data-cargar-mapa]')?.addEventListener('click', () => {
  const caja = document.querySelector<HTMLElement>('[data-mapa]')!;
  const iframe = document.createElement('iframe');
  iframe.src = caja.dataset.mapa!;
  iframe.title = 'Mapa de la ubicación de la oficina';
  iframe.loading = 'lazy';
  caja.replaceChildren(iframe);
});
