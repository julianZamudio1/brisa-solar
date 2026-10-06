/**
 * Estimación ilustrativa de un sistema fotovoltaico interconectado a partir del
 * recibo bimestral de CFE. No sustituye una visita técnica: las tarifas cambian
 * cada mes y dependen de la región. Las constantes y su origen están en
 * docs/calculadora.md.
 */

export type TipoInmueble = 'casa' | 'negocio';
export type Region = 'norte' | 'centro' | 'sur';

/** Horas solares pico promedio anual por región (kWh/m²/día). */
export const HSP: Record<Region, number> = {
  norte: 6.0,
  centro: 5.6,
  sur: 5.1,
};

/** Precios con IVA incluido, aproximados a la tarifa 1 y PDBT de CFE. */
export const TARIFA = {
  domestica: {
    // Bloques bimestrales de la tarifa 1 (kWh, $/kWh)
    basico: { kwh: 150, precio: 1.3 },
    intermedio: { kwh: 130, precio: 1.58 },
    excedente: { precio: 4.62 },
    // Límite de la tarifa DAC para la tarifa 1: 250 kWh/mes promedio.
    limiteDacBimestral: 500,
    dac: { cargoFijo: 300, precio: 6.4 },
    minimoBimestral: 60,
  },
  negocio: { cargoFijo: 240, precio: 5.5 },
} as const;

export const SUPUESTOS = {
  rendimiento: 0.78, // pérdidas por temperatura, cableado e inversor
  potenciaPanelW: 550,
  diasBimestre: 61,
  incrementoTarifaAnual: 0.04,
  degradacionAnual: 0.005,
  vidaUtilAnios: 25,
  factorEmisionKgPorKwh: 0.438, // factor de emisión del Sistema Eléctrico Nacional
  reciboMinimoRecomendado: 400,
} as const;

/** Costo instalado por kWp (MXN, IVA incluido) según tamaño del sistema. */
export function costoPorKwp(kwp: number): number {
  if (kwp <= 3) return 22000;
  if (kwp <= 10) return 19500;
  return 17000;
}

export interface EntradaCalculo {
  reciboBimestral: number;
  tipo: TipoInmueble;
  region: Region;
}

export interface ResultadoCalculo {
  viable: boolean;
  consumoBimestralKwh: number;
  tarifaDac: boolean;
  paneles: number;
  potenciaKwp: number;
  generacionBimestralKwh: number;
  cobertura: number;
  reciboNuevo: number;
  ahorroBimestral: number;
  ahorroAnual: number;
  inversion: { minima: number; maxima: number; estimada: number };
  retornoAnios: number;
  ahorro25Anios: number;
  co2EvitadoToneladasAnio: number;
}

const T = TARIFA.domestica;

/** Costo bimestral en tarifa 1 para un consumo dado. */
export function costoTarifa1(kwh: number): number {
  const basico = Math.min(kwh, T.basico.kwh);
  const intermedio = Math.min(Math.max(kwh - T.basico.kwh, 0), T.intermedio.kwh);
  const excedente = Math.max(kwh - T.basico.kwh - T.intermedio.kwh, 0);
  return basico * T.basico.precio + intermedio * T.intermedio.precio + excedente * T.excedente.precio;
}

/** Inverso de costoTarifa1: consumo que produce un recibo dado. */
export function consumoDesdeTarifa1(recibo: number): number {
  const topeBasico = T.basico.kwh * T.basico.precio;
  const topeIntermedio = topeBasico + T.intermedio.kwh * T.intermedio.precio;
  if (recibo <= topeBasico) return recibo / T.basico.precio;
  if (recibo <= topeIntermedio) return T.basico.kwh + (recibo - topeBasico) / T.intermedio.precio;
  return T.basico.kwh + T.intermedio.kwh + (recibo - topeIntermedio) / T.excedente.precio;
}

export function estimarConsumo(recibo: number, tipo: TipoInmueble): { kwh: number; dac: boolean } {
  if (tipo === 'negocio') {
    const n = TARIFA.negocio;
    return { kwh: Math.max(recibo - n.cargoFijo, 0) / n.precio, dac: false };
  }
  // La DAC se asigna por promedio anual, así que un bimestre alto en tarifa 1 es posible.
  // Solo un recibo mayor al que pagaría un usuario DAC en el límite indica tarifa DAC.
  const reciboDacEnLimite = T.dac.cargoFijo + T.limiteDacBimestral * T.dac.precio;
  if (recibo <= reciboDacEnLimite) return { kwh: consumoDesdeTarifa1(recibo), dac: false };
  return { kwh: (recibo - T.dac.cargoFijo) / T.dac.precio, dac: true };
}

function redondear(valor: number, multiplo: number): number {
  return Math.round(valor / multiplo) * multiplo;
}

export function calcular({ reciboBimestral, tipo, region }: EntradaCalculo): ResultadoCalculo {
  const S = SUPUESTOS;
  const recibo = Math.max(0, reciboBimestral);
  const { kwh: consumo, dac } = estimarConsumo(recibo, tipo);

  const kwhDia = consumo / S.diasBimestre;
  const kwpNecesarios = kwhDia / (HSP[region] * S.rendimiento);
  const paneles = Math.max(1, Math.ceil((kwpNecesarios * 1000) / S.potenciaPanelW));
  const potenciaKwp = (paneles * S.potenciaPanelW) / 1000;
  const generacion = potenciaKwp * HSP[region] * S.rendimiento * S.diasBimestre;
  const cobertura = consumo > 0 ? Math.min(1, generacion / consumo) : 0;

  // Con medición neta se paga el mínimo; si la cobertura no es total se paga lo que falte.
  const minimo = tipo === 'casa' ? T.minimoBimestral : TARIFA.negocio.cargoFijo;
  const faltante = consumo - Math.min(generacion, consumo);
  const reciboNuevo =
    tipo === 'casa'
      ? Math.max(minimo, costoTarifa1(faltante))
      : minimo + faltante * TARIFA.negocio.precio;
  const ahorroBimestral = Math.max(0, recibo - reciboNuevo);
  const ahorroAnual = ahorroBimestral * 6;

  const estimada = redondear(potenciaKwp * costoPorKwp(potenciaKwp), 500);
  const inversion = {
    estimada,
    minima: redondear(estimada * 0.9, 500),
    maxima: redondear(estimada * 1.1, 500),
  };

  let ahorro25Anios = 0;
  for (let anio = 0; anio < S.vidaUtilAnios; anio++) {
    ahorro25Anios +=
      ahorroAnual * (1 + S.incrementoTarifaAnual) ** anio * (1 - S.degradacionAnual) ** anio;
  }

  return {
    viable: recibo >= S.reciboMinimoRecomendado,
    consumoBimestralKwh: Math.round(consumo),
    tarifaDac: dac,
    paneles,
    potenciaKwp: Math.round(potenciaKwp * 100) / 100,
    generacionBimestralKwh: Math.round(generacion),
    cobertura,
    reciboNuevo: Math.round(reciboNuevo),
    ahorroBimestral: Math.round(ahorroBimestral),
    ahorroAnual: Math.round(ahorroAnual),
    inversion,
    retornoAnios: ahorroAnual > 0 ? Math.round((estimada / ahorroAnual) * 10) / 10 : Infinity,
    ahorro25Anios: redondear(ahorro25Anios, 1000),
    co2EvitadoToneladasAnio:
      Math.round(((generacion * 6 * S.factorEmisionKgPorKwh) / 1000) * 10) / 10,
  };
}

const formatoPesos = new Intl.NumberFormat('es-MX', {
  style: 'currency',
  currency: 'MXN',
  maximumFractionDigits: 0,
});

export const pesos = (n: number) => formatoPesos.format(n);
