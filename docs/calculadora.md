# Calculadora de ahorro

La lógica está en `site/src/lib/calculadora.ts`, en funciones puras con 12 pruebas unitarias (`npm test`). La misma función calcula los valores iniciales en el build (para que la sección se vea completa sin JavaScript) y responde en el navegador cuando el visitante mueve la barra.

Es una **estimación para generar prospectos**, no una cotización. La página lo dice junto al botón y explica sus supuestos en *Cómo calculamos*.

## Pasos

1. **Recibo → consumo (kWh bimestrales)**
   - *Casa:* se invierte la tarifa 1 de CFE por bloques (básico 150 kWh, intermedio 130 kWh, excedente). Si el recibo es mayor de lo que pagaría un usuario DAC en el límite de 500 kWh bimestrales, se usa la tarifa DAC (cargo fijo + precio por kWh).
   - *Negocio:* tarifa PDBT simplificada (cargo fijo + precio por kWh).
2. **Consumo → potencia:** `kWp = (kWh bimestrales / 61 días) / (horas solares pico × 0.78)`.
3. **Potencia → paneles:** se redondea hacia arriba a paneles de 550 W.
4. **Recibo nuevo:** con medición neta se paga el mínimo de CFE si el sistema cubre el consumo.
5. **Inversión:** precio por kWp instalado según el tamaño (sistemas chicos cuestan más por kWp), con un rango de ±10%.
6. **Retorno y ahorro a 25 años:** con un alza de tarifa de 4% anual y una pérdida de producción de 0.5% anual.
7. **CO₂ evitado:** generación anual × factor de emisión del Sistema Eléctrico Nacional (0.438 kg CO₂/kWh).

## Constantes

| Constante | Valor | Origen |
|---|---|---|
| Tarifa 1, bloque básico | $1.30/kWh | Tarifa 1 de CFE con IVA, aproximada |
| Tarifa 1, intermedio | $1.58/kWh | Ídem |
| Tarifa 1, excedente | $4.62/kWh | Ídem |
| DAC | $300 fijo + $6.40/kWh | Ídem |
| PDBT | $240 fijo + $5.50/kWh | Ídem |
| Horas solares pico | Norte 6.0, Centro 5.6, Sur 5.1 | Promedios anuales de irradiación en México |
| Rendimiento del sistema | 78% | Pérdidas típicas por temperatura, cableado e inversor |
| Costo por kWp | $22,000 (≤3 kWp), $19,500 (≤10), $17,000 (>10) | Rango de mercado residencial y comercial, ilustrativo |

Las tarifas de CFE cambian cada mes y varían por región. Las constantes están en un solo objeto (`TARIFA`) para actualizarlas en un lugar; la explicación en la página se arma con esos mismos valores.

## Detalles

- Si el recibo es menor a $400, la calculadora avisa que el ahorro suele ser pequeño. Un prospecto que no va a comprar le cuesta tiempo al equipo de ventas.
- Si detecta tarifa DAC, lo resalta: es el caso con el retorno más rápido y el mejor argumento de venta.
- *Quiero esta cotización* copia el recibo y el tipo de inmueble al formulario y marca el prospecto con `origen = calculadora`, para comparar en GA4 qué canal convierte mejor.
