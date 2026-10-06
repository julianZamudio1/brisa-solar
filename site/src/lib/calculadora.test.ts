import { describe, expect, it } from 'vitest';
import { calcular, consumoDesdeTarifa1, costoTarifa1, estimarConsumo } from './calculadora';

describe('tarifa 1', () => {
  it('el inverso devuelve el consumo original en cada bloque', () => {
    for (const kwh of [80, 150, 200, 280, 420, 650]) {
      expect(consumoDesdeTarifa1(costoTarifa1(kwh))).toBeCloseTo(kwh, 6);
    }
  });

  it('cobra el bloque básico a su precio', () => {
    expect(costoTarifa1(100)).toBeCloseTo(130, 6);
  });
});

describe('estimarConsumo', () => {
  it('un recibo alto se trata como DAC', () => {
    expect(estimarConsumo(5000, 'casa')).toEqual({ kwh: (5000 - 300) / 6.4, dac: true });
  });

  it('un recibo medio se queda en tarifa 1', () => {
    expect(estimarConsumo(2000, 'casa').dac).toBe(false);
  });

  it('un negocio descuenta el cargo fijo', () => {
    expect(estimarConsumo(5740, 'negocio').kwh).toBeCloseTo(1000, 6);
  });
});

describe('calcular', () => {
  const casaTipica = calcular({ reciboBimestral: 2500, tipo: 'casa', region: 'centro' });

  it('dimensiona un sistema que cubre el consumo', () => {
    expect(casaTipica.cobertura).toBe(1);
    expect(casaTipica.paneles).toBeGreaterThan(0);
    expect(casaTipica.potenciaKwp).toBeCloseTo(casaTipica.paneles * 0.55, 6);
  });

  it('el recibo nuevo es el mínimo y el ahorro es la diferencia', () => {
    expect(casaTipica.reciboNuevo).toBe(60);
    expect(casaTipica.ahorroBimestral).toBe(2440);
    expect(casaTipica.ahorroAnual).toBe(2440 * 6);
  });

  it('el rango de inversión rodea a la estimada', () => {
    const { minima, estimada, maxima } = casaTipica.inversion;
    expect(minima).toBeLessThan(estimada);
    expect(maxima).toBeGreaterThan(estimada);
  });

  it('el retorno es inversión entre ahorro anual', () => {
    const esperado = casaTipica.inversion.estimada / casaTipica.ahorroAnual;
    expect(casaTipica.retornoAnios).toBeCloseTo(esperado, 1);
  });

  it('más sol significa menos paneles', () => {
    const norte = calcular({ reciboBimestral: 4000, tipo: 'casa', region: 'norte' });
    const sur = calcular({ reciboBimestral: 4000, tipo: 'casa', region: 'sur' });
    expect(norte.paneles).toBeLessThanOrEqual(sur.paneles);
  });

  it('marca como no viable un recibo muy bajo', () => {
    expect(calcular({ reciboBimestral: 250, tipo: 'casa', region: 'centro' }).viable).toBe(false);
  });

  it('no produce ahorro negativo ni NaN con recibo cero', () => {
    const r = calcular({ reciboBimestral: 0, tipo: 'casa', region: 'centro' });
    expect(r.ahorroBimestral).toBe(0);
    expect(Number.isNaN(r.potenciaKwp)).toBe(false);
  });
});
