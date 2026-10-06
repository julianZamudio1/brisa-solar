// Capturas para el README. Requiere el sitio corriendo (npm run preview o npm run dev).
// Uso: node scripts/capturas.mjs [url] [carpeta]
import { chromium } from '@playwright/test';
import sharp from 'sharp';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';

const url = process.argv[2] ?? 'http://localhost:4321/';
const salida = path.resolve(process.argv[3] ?? '../docs/capturas');
await mkdir(salida, { recursive: true });
const navegador = await chromium.launch();

async function pagina({ ancho = 1440, alto = 900, tema = 'light', escala = 1, movil = false } = {}) {
  const p = await navegador.newPage({ viewport: { width: ancho, height: alto }, colorScheme: tema, deviceScaleFactor: escala, isMobile: movil, hasTouch: movil });
  await p.addInitScript(() => localStorage.setItem('consentimiento', '{"analitica":false,"publicidad":false}'));
  await p.route(/youtube|openstreetmap|googletagmanager/, (r) => r.abort());
  await p.goto(url, { waitUntil: 'networkidle' });
  // Recorre la página para que todas las secciones terminen su animación de entrada.
  await p.evaluate(async () => {
    for (let y = 0; y < document.body.scrollHeight; y += 400) {
      window.scrollTo({ top: y, behavior: 'instant' });
      await new Promise((r) => setTimeout(r, 50));
    }
    window.scrollTo({ top: 0, behavior: 'instant' });
  });
  await p.waitForTimeout(900);
  return p;
}

async function guardar(buffer, nombre, ancho) {
  await sharp(buffer).resize({ width: ancho, withoutEnlargement: true }).webp({ quality: 82 }).toFile(path.join(salida, `${nombre}.webp`));
  console.log('✔', nombre);
}

// 1. Primera pantalla en escritorio, claro y oscuro.
for (const tema of ['light', 'dark']) {
  const p = await pagina({ tema });
  await guardar(await p.screenshot(), tema === 'light' ? '01-inicio' : '02-inicio-oscuro', 1440);
  await p.close();
}

// 2. Calculadora con un recibo en tarifa DAC.
{
  const p = await pagina();
  // En capturas de una sola sección el encabezado fijo quedaría encima del contenido.
  await p.addStyleTag({ content: '.encabezado, .whatsapp-flotante { position: static !important; }' });
  await p.locator('#calc-recibo-numero').fill('5200');
  await p.waitForTimeout(300);
  await guardar(await p.locator('#calculadora').screenshot(), '03-calculadora', 1440);

  // 3. Formulario con errores de validación.
  await p.getByRole('button', { name: 'Agendar visita gratis' }).click();
  await p.getByLabel('Teléfono o WhatsApp').fill('442 12');
  await p.getByLabel('Teléfono o WhatsApp').blur();
  await p.getByLabel('Nombre completo').fill('Mariana Echeverría');
  await p.getByLabel('Nombre completo').blur();
  await guardar(await p.locator('#cotizar').screenshot(), '04-formulario', 1440);

  // 4. Beneficios y proyectos.
  await guardar(await p.locator('#proyectos').screenshot(), '05-proyectos', 1440);
  await p.close();
}

// 5. Móvil: inicio y calculadora.
{
  const p = await pagina({ ancho: 390, alto: 844, escala: 2, movil: true });
  await guardar(await p.screenshot(), '06-movil', 780);
  await p.locator('#calculadora').scrollIntoViewIfNeeded();
  await p.evaluate(() => window.scrollBy({ top: 330, behavior: 'instant' }));
  await p.waitForTimeout(400);
  await guardar(await p.screenshot(), '07-movil-calculadora', 780);
  await p.close();
}

// 6. Panel de depuración de la medición.
{
  const p = await navegador.newPage({ viewport: { width: 1440, height: 900 } });
  await p.route(/youtube|openstreetmap|googletagmanager/, (r) => r.abort());
  await p.goto(url + '?depurar_medicion', { waitUntil: 'networkidle' });
  await p.getByRole('button', { name: 'Aceptar todas' }).click();
  await p.locator('#calc-recibo-numero').fill('3800');
  await p.waitForTimeout(1000);
  await p.locator('.hero a[data-evento="click_whatsapp"]').evaluate((a) => a.addEventListener('click', (e) => e.preventDefault()));
  await p.locator('.hero a[data-evento="click_whatsapp"]').click();
  await p.locator('#calculadora').scrollIntoViewIfNeeded();
  await p.waitForTimeout(500);
  await guardar(await p.screenshot(), '08-depurar-medicion', 1440);
  await p.close();
}

await navegador.close();
