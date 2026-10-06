import { escucharClics, guardarCampana, medirProfundidad } from './medicion';

guardarCampana();
escucharClics();
medirProfundidad();

// Aparición de secciones al entrar en pantalla.
const revelables = document.querySelectorAll<HTMLElement>('[data-revelar]');
if ('IntersectionObserver' in window) {
  const obs = new IntersectionObserver(
    (entradas) => {
      for (const entrada of entradas) {
        if (!entrada.isIntersecting) continue;
        entrada.target.classList.add('visible');
        obs.unobserve(entrada.target);
      }
    },
    { rootMargin: '0px 0px -10% 0px' },
  );
  revelables.forEach((el) => obs.observe(el));
} else {
  revelables.forEach((el) => el.classList.add('visible'));
}

// Menú móvil.
const botonMenu = document.querySelector<HTMLButtonElement>('[data-menu-boton]');
const menu = document.getElementById('menu-principal');
if (botonMenu && menu) {
  const cerrar = () => {
    botonMenu.setAttribute('aria-expanded', 'false');
    menu.classList.remove('abierto');
  };
  botonMenu.addEventListener('click', () => {
    const abierto = botonMenu.getAttribute('aria-expanded') === 'true';
    botonMenu.setAttribute('aria-expanded', String(!abierto));
    menu.classList.toggle('abierto', !abierto);
  });
  menu.addEventListener('click', (e) => {
    if ((e.target as HTMLElement).closest('a')) cerrar();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') cerrar();
  });
}

// Cambio manual de tema; se recuerda solo en este navegador.
document.querySelector('[data-tema-boton]')?.addEventListener('click', () => {
  const html = document.documentElement;
  const nuevo = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-bs-theme', nuevo);
  try {
    localStorage.setItem('tema', nuevo);
  } catch {
    // Sin almacenamiento el cambio dura hasta recargar.
  }
});
