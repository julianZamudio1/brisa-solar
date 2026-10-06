// Prueba de humo del tema en WordPress Playground (npm run wordpress debe estar corriendo).
// Llena el formulario como visitante, verifica la página de gracias y que el prospecto
// quedó guardado en el administrador. Uso: node probar-wordpress.mjs
import { chromium } from '@playwright/test';
import assert from 'node:assert/strict';

const URL_WP = process.env.URL_WP ?? 'http://127.0.0.1:9400/';
const navegador = await chromium.launch();

// Visitante anónimo: contexto sin la sesión de administrador.
const visitante = await navegador.newPage();
await visitante.addInitScript(() => localStorage.setItem('consentimiento', '{"analitica":true,"publicidad":true}'));
await visitante.goto(URL_WP, { waitUntil: 'networkidle' });
await visitante.getByLabel('Nombre completo').fill('Prueba Playwright');
await visitante.getByLabel('Teléfono o WhatsApp').fill('442 555 0101');
await visitante.getByLabel('Código postal').fill('76000');
await visitante.getByLabel('Recibo bimestral aproximado').fill('4100');
await visitante.getByLabel('Acepto el aviso de privacidad').check();
await visitante.waitForTimeout(3100); // la trampa anti-bots rechaza envíos de menos de 3 s
await visitante.getByRole('button', { name: 'Agendar visita gratis' }).click();
await visitante.waitForURL(/\/gracias\/\?folio=BS-\d{6}-[A-Z0-9]{4}/, { timeout: 30000 });
const folio = new URL(visitante.url()).searchParams.get('folio');
assert.equal(await visitante.locator('[data-folio]').textContent(), folio);
console.log('✔ formulario enviado, folio', folio);

// Administrador: el prospecto aparece en la lista (el blueprint inicia sesión automáticamente).
const admin = await navegador.newPage();
await admin.goto(new URL('wp-admin/edit.php?post_type=brisa_prospecto&post_status=private', URL_WP).href, { waitUntil: 'networkidle' });
const fila = admin.locator('#the-list tr', { hasText: folio });
assert.equal(await fila.count(), 1, 'el prospecto no aparece en el administrador');
assert.match(await fila.textContent(), /4425550101/);
assert.match(await fila.textContent(), /\$4,100/);
console.log('✔ prospecto visible en Prospectos con teléfono y recibo');

await navegador.close();
