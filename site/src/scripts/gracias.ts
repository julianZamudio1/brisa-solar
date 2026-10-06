const folio = new URLSearchParams(location.search).get('folio');
const destino = document.querySelector('[data-folio]');
if (destino && folio && /^[A-Z0-9-]{4,20}$/.test(folio)) destino.textContent = folio;
