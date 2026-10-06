import { medir } from './medicion';
document.querySelectorAll<HTMLDetailsElement>('.acordeon details').forEach((d) =>
  d.addEventListener('toggle', () => {
    if (d.open) medir('pregunta_abierta', { pregunta: d.querySelector('summary')?.textContent?.trim() });
  }),
);
