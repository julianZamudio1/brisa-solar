import { medir } from './medicion';

// La sección puede no existir (por ejemplo en las páginas internas del tema de WordPress).
if (document.querySelector('[data-carrusel]')) iniciarCarrusel();

function iniciarCarrusel() {
  const carrusel = document.querySelector<HTMLElement>('[data-carrusel]')!;
  const botones = document.querySelectorAll<HTMLButtonElement>('[data-carrusel-controles] button');
  const tarjetas = carrusel.querySelectorAll<HTMLElement>('.tarjeta');
  const [anterior, siguiente] = botones;
  let interactuo = false;

  botones.forEach((b) =>
    b.addEventListener('click', () => {
      const paso = tarjetas[0].getBoundingClientRect().width + 24;
      carrusel.scrollBy({ left: paso * Number(b.dataset.dir), behavior: 'smooth' });
      if (!interactuo) medir('carrusel_interaccion', { carrusel: 'proyectos' });
      interactuo = true;
    }),
  );

  // Desactiva las flechas en los extremos sin escuchar el evento scroll.
  const obs = new IntersectionObserver(
    (entradas) => {
      for (const e of entradas) {
        if (e.target === tarjetas[0]) anterior.disabled = e.isIntersecting;
        if (e.target === tarjetas[tarjetas.length - 1]) siguiente.disabled = e.isIntersecting;
      }
    },
    { root: carrusel, threshold: 0.9 },
  );
  obs.observe(tarjetas[0]);
  obs.observe(tarjetas[tarjetas.length - 1]);
}
