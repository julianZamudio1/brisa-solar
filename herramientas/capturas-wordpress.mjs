// Capturas del tema en WordPress para el README (npm run wordpress debe estar corriendo
// y probar-wordpress.mjs ya debe haber creado al menos un prospecto).
import { chromium } from '@playwright/test';
import sharp from 'sharp';
import path from 'node:path';

const URL_WP = process.env.URL_WP ?? 'http://127.0.0.1:9400/';
const salida = path.resolve(import.meta.dirname, '../docs/capturas');
const navegador = await chromium.launch();
const p = await navegador.newPage({ viewport: { width: 1440, height: 900 } });

await p.goto(new URL('wp-admin/edit.php?post_type=brisa_prospecto&post_status=private', URL_WP).href, { waitUntil: 'networkidle' });
await sharp(await p.screenshot()).resize({ width: 1440 }).webp({ quality: 82 }).toFile(path.join(salida, '09-wordpress-prospectos.webp'));

await p.locator('#the-list tr a.row-title').first().click();
await p.waitForLoadState('networkidle');
// El editor de bloques muestra un aviso de bienvenida la primera vez.
await p.keyboard.press('Escape');
await p.waitForTimeout(500);
await sharp(await p.screenshot()).resize({ width: 1440 }).webp({ quality: 82 }).toFile(path.join(salida, '10-wordpress-prospecto.webp'));

await p.goto(new URL('wp-admin/customize.php', URL_WP).href, { waitUntil: 'networkidle' });
await p.getByRole('heading', { name: /Brisa Solar/ }).first().click().catch(() => {});
await p.locator('#accordion-panel-brisa').click();
await p.locator('#accordion-section-brisa_negocio').click();
await p.waitForTimeout(1500);
await sharp(await p.screenshot()).resize({ width: 1440 }).webp({ quality: 82 }).toFile(path.join(salida, '11-wordpress-personalizar.webp'));

console.log('✔ capturas de WordPress');
await navegador.close();
