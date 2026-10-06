/**
 * Datos del negocio en un solo lugar. Brisa Solar es una empresa ficticia:
 * teléfono, dirección y testimonios son de demostración.
 */
export const sitio = {
  nombre: 'Brisa Solar',
  descripcion:
    'Instalación de paneles solares para casas y negocios en Querétaro y el Bajío. Cotización gratis, trámite con CFE incluido y financiamiento.',
  telefono: '+524421580937',
  telefonoVisible: '442\u00a0158\u00a00937', // espacios no separables: el número no se parte en dos líneas
  whatsapp: '524421580937',
  mensajeWhatsapp: 'Hola, quiero cotizar paneles solares para mi',
  correo: 'hola@brisasolar.mx',
  horario: 'Lunes a viernes de 9:00 a 18:00, sábados de 9:00 a 14:00',
  direccion: {
    calle: 'Av. Constituyentes 117 Ote., Local 4',
    colonia: 'Col. Centro',
    ciudad: 'Santiago de Querétaro',
    estado: 'Querétaro',
    cp: '76000',
  },
  geo: { lat: 20.5888, lng: -100.3899 },
  redes: {
    facebook: 'https://www.facebook.com/',
    instagram: 'https://www.instagram.com/',
  },
} as const;

/** Mismo código, dos destinos: hosting con PHP o una demo estática sin backend. */
export const config = {
  gtmId: import.meta.env.PUBLIC_GTM_ID ?? '',
  endpointFormulario: import.meta.env.PUBLIC_ENDPOINT_FORMULARIO ?? 'api/cotizacion.php',
  modoDemo: import.meta.env.PUBLIC_MODO_DEMO === 'true',
};

export function enlaceWhatsapp(complemento = 'casa') {
  const texto = `${sitio.mensajeWhatsapp} ${complemento}.`;
  return `https://wa.me/${sitio.whatsapp}?text=${encodeURIComponent(texto)}`;
}

/** Une la base configurada (por ejemplo /brisa-solar/) con una ruta interna. */
export function ruta(path = '') {
  const base = import.meta.env.BASE_URL.replace(/\/?$/, '/');
  return base + path.replace(/^\//, '');
}
