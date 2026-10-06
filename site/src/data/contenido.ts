// Contenido de demostración. Astro lo usa directo y el tema de WordPress lo importa
// como entradas (testimonios y proyectos) con scripts/construir-tema.mjs.

export const testimonios = [
  {
    texto: 'Pagábamos más de seis mil pesos al bimestre. Hoy el recibo llega en 92 pesos y el trámite con CFE lo hicieron ellos.',
    nombre: 'Mariana Echeverría',
    detalle: 'Residencia en San Miguel de Allende',
    destacado: true,
  },
  {
    texto: 'Nos explicaron cuántos paneles necesitábamos y por qué. La cotización final fue igual a la primera.',
    nombre: 'Rodrigo Saldaña',
    detalle: 'Casa en Juriquilla',
    destacado: false,
  },
  {
    texto: 'La nave tenía tarifa GDMTH y ahora generamos casi todo de día. El sistema se pagó en menos de cuatro años.',
    nombre: 'Ing. Alejandra Pozos',
    detalle: 'Gerente de planta, El Marqués',
    destacado: false,
  },
];

export const proyectos = [
  { foto: 'proyecto-casa-ladrillo', alt: 'Casa de ladrillo con paneles solares en el techo', lugar: 'Casa en Juriquilla, Qro.', kwp: 4.4, paneles: 8, antes: 3180, despues: 74 },
  { foto: 'proyecto-nave-industrial', alt: 'Vista aérea de una nave industrial con paneles solares en toda la azotea', lugar: 'Nave industrial en El Marqués, Qro.', kwp: 186, paneles: 338, antes: 214500, despues: 9800 },
  { foto: 'proyecto-casa-dos-aguas', alt: 'Casa de techo a dos aguas con paneles solares', lugar: 'Casa en Celaya, Gto.', kwp: 3.3, paneles: 6, antes: 2240, despues: 60 },
  { foto: 'proyecto-bodega', alt: 'Vista aérea de una bodega con paneles solares rodeada de terreno', lugar: 'Bodega agrícola en Tequisquiapan, Qro.', kwp: 52.8, paneles: 96, antes: 61200, despues: 2350 },
  { foto: 'proyecto-residencial', alt: 'Residencia de dos pisos con paneles solares negros', lugar: 'Residencia en San Miguel de Allende, Gto.', kwp: 7.7, paneles: 14, antes: 6890, despues: 92 },
];
