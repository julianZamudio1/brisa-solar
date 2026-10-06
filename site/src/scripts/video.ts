import { medir } from './medicion';

document.querySelectorAll<HTMLElement>('[data-video]').forEach((caja) => {
  caja.querySelector('button')?.addEventListener('click', () => {
    const iframe = document.createElement('iframe');
    iframe.src = caja.dataset.video!;
    iframe.title = caja.dataset.titulo!;
    iframe.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen';
    iframe.allowFullscreen = true;
    caja.replaceChildren(iframe);
    medir('video_reproducido', { titulo: caja.dataset.titulo });
  });
});
