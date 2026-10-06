import { expect, test, type Page } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const SIN_DECISION = { analitica: false, publicidad: false };

/** Eventos que la página empujó al dataLayer (sin los comandos de gtag). */
async function eventos(page: Page): Promise<Record<string, unknown>[]> {
  return page.evaluate(() =>
    window.dataLayer.filter((d) => typeof d === 'object' && d && 'event' in d && !String(d.event).startsWith('gtm.')),
  );
}

async function abrir(page: Page, { consentimiento = SIN_DECISION as object | null } = {}) {
  if (consentimiento) {
    await page.addInitScript((c) => localStorage.setItem('consentimiento', JSON.stringify(c)), consentimiento);
  }
  // El video y el mapa son de terceros: no se cargan en las pruebas.
  await page.route(/youtube|openstreetmap|googletagmanager/, (r) => r.abort());
  await page.goto('./');
}

async function llenarFormulario(page: Page) {
  await page.getByLabel('Nombre completo').fill('Rodrigo Saldaña');
  await page.getByLabel('Teléfono o WhatsApp').fill('442 123 4567');
  await page.getByLabel('Código postal').fill('76230');
  await page.getByLabel('Recibo bimestral aproximado').fill('3180');
  await page.getByLabel('Acepto el aviso de privacidad').check();
}

test.describe('Carga y SEO', () => {
  test('carga sin errores y con metadatos', async ({ page }) => {
    const errores: string[] = [];
    page.on('pageerror', (e) => errores.push(e.message));
    page.on('console', (m) => m.type() === 'error' && !m.text().includes('net::ERR_FAILED') && errores.push(m.text()));
    await abrir(page);

    await expect(page).toHaveTitle(/Paneles solares en Querétaro/);
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Paga hasta 95% menos de luz con energía solar');
    await expect(page.locator('meta[name="description"]')).toHaveAttribute('content', /paneles solares/i);
    await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', /^https?:\/\//);

    const esquemas = await page.locator('script[type="application/ld+json"]').allTextContents();
    const tipos = esquemas.map((e) => JSON.parse(e)['@type']);
    expect(tipos).toEqual(['HomeAndConstructionBusiness', 'FAQPage']);
    expect(errores).toEqual([]);
  });

  test('una sola h1 y encabezados en orden', async ({ page }) => {
    await abrir(page);
    await expect(page.locator('h1')).toHaveCount(1);
    const niveles = await page.locator('main h1, main h2, main h3').evaluateAll((els) => els.map((e) => Number(e.tagName[1])));
    for (let i = 1; i < niveles.length; i++) expect(niveles[i] - niveles[i - 1]).toBeLessThanOrEqual(1);
  });
});

test.describe('Calculadora', () => {
  test('recalcula al mover la barra y mide el uso', async ({ page }) => {
    await abrir(page);
    const ahorro = page.locator('[data-r="ahorroAnual"]');
    const inicial = await ahorro.textContent();

    await page.locator('#calc-recibo-numero').fill('6000');
    await expect(ahorro).not.toHaveText(inicial!);
    await expect(page.locator('[data-r="recibo"]')).toHaveText('$6,000');
    await expect(page.locator('[data-aviso-dac]')).toBeVisible();

    await expect.poll(async () => (await eventos(page)).map((e) => e.event)).toContain('calculadora_uso');
    const uso = (await eventos(page)).find((e) => e.event === 'calculadora_uso')!;
    expect(uso).toMatchObject({ recibo_bimestral: 6000, tipo_inmueble: 'casa', region: 'centro' });
  });

  test('avisa cuando el recibo es muy bajo', async ({ page }) => {
    await abrir(page);
    await page.locator('#calc-recibo-numero').fill('250');
    await expect(page.locator('[data-aviso-bajo]')).toBeVisible();
  });

  test('pasa el cálculo al formulario de cotización', async ({ page }) => {
    await abrir(page);
    await page.locator('#calc-recibo-numero').fill('4200');
    await page.getByText('Negocio', { exact: true }).click();
    await page.getByRole('link', { name: 'Quiero esta cotización' }).click();
    await expect(page.getByLabel('Recibo bimestral aproximado')).toHaveValue('4200');
    await expect(page.getByLabel('Tipo de inmueble')).toHaveValue('negocio');
    await expect(page.getByLabel('Nombre completo')).toBeFocused();
  });
});

test.describe('Formulario de cotización', () => {
  test('muestra errores claros y enfoca el primer campo', async ({ page }) => {
    await abrir(page);
    await page.getByRole('button', { name: 'Agendar visita gratis' }).click();
    await expect(page.getByText('Escribe tu nombre.')).toBeVisible();
    await expect(page.getByText('Necesitamos un teléfono para confirmar la visita.')).toBeVisible();
    await expect(page.getByText('Debes aceptar el aviso de privacidad para continuar.')).toBeVisible();
    await expect(page.getByLabel('Nombre completo')).toBeFocused();
    await expect(page.getByLabel('Nombre completo')).toHaveAttribute('aria-invalid', 'true');
    expect((await eventos(page)).map((e) => e.event)).toContain('formulario_error');
  });

  test('exige 10 dígitos en el teléfono', async ({ page }) => {
    await abrir(page);
    const telefono = page.getByLabel('Teléfono o WhatsApp');
    await telefono.fill('442 12');
    await telefono.blur();
    await expect(page.getByText('Escribe un teléfono de 10 dígitos.')).toBeVisible();
    await telefono.fill('+52 442 123 4567');
    await expect(page.getByText('Escribe un teléfono de 10 dígitos.')).toBeHidden();
  });

  test('envía, mide generate_lead y lleva a la página de gracias', async ({ page }) => {
    let cuerpo = '';
    await page.route('**/api/cotizacion.php', async (r) => {
      cuerpo = r.request().postData() ?? '';
      await r.fulfill({ status: 201, json: { ok: true, folio: 'BS-261006-7K2Q' } });
    });
    await abrir(page);
    await llenarFormulario(page);
    await page.getByRole('button', { name: 'Agendar visita gratis' }).click();

    await expect.poll(async () => (await eventos(page).catch(() => [])).find((e) => e.event === 'generate_lead')).toMatchObject({
      currency: 'MXN',
      recibo_bimestral: 3180,
      tipo_inmueble: 'casa',
    });
    await page.waitForURL(/\/gracias\/\?folio=BS-261006-7K2Q/);
    await expect(page.getByText('BS-261006-7K2Q')).toBeVisible();
    expect(cuerpo).toContain('Rodrigo Salda');
    expect(cuerpo).toContain('id_evento');
  });

  test('si el servidor falla ofrece WhatsApp y permite reintentar', async ({ page }) => {
    await page.route('**/api/cotizacion.php', (r) => r.fulfill({ status: 500, json: { ok: false } }));
    await abrir(page);
    await llenarFormulario(page);
    const boton = page.getByRole('button', { name: 'Agendar visita gratis' });
    await boton.click();
    await expect(page.getByRole('alert')).toContainText('No pudimos enviar tu solicitud');
    await expect(boton).toBeEnabled();
  });
});

test.describe('Consentimiento y medición', () => {
  test('pregunta antes de medir y recuerda la decisión', async ({ page }) => {
    await abrir(page, { consentimiento: null });
    const aviso = page.getByRole('region', { name: 'Cookies y medición' });
    await expect(aviso).toBeVisible();

    const porDefecto = await page.evaluate(() =>
      window.dataLayer.map((d) => Array.from(d as unknown as ArrayLike<unknown>)).find((a) => a[0] === 'consent' && a[1] === 'default'),
    );
    expect(porDefecto?.[2]).toMatchObject({ analytics_storage: 'denied', ad_storage: 'denied' });

    await aviso.getByRole('button', { name: 'Aceptar todas' }).click();
    await expect(aviso).toBeHidden();
    const actualizacion = await page.evaluate(() =>
      window.dataLayer.map((d) => Array.from(d as unknown as ArrayLike<unknown>)).filter((a) => a[0] === 'consent' && a[1] === 'update').pop(),
    );
    expect(actualizacion?.[2]).toMatchObject({ analytics_storage: 'granted', ad_user_data: 'granted' });

    await page.reload();
    await expect(aviso).toBeHidden();
  });

  test('los clics en WhatsApp se miden con su ubicación', async ({ page, context }) => {
    await abrir(page);
    const enlace = page.locator('.hero a[data-evento="click_whatsapp"]');
    await expect(enlace).toHaveAttribute('href', /^https:\/\/wa\.me\/52\d{10}\?text=/);
    await context.route('https://wa.me/**', (r) => r.abort());
    await enlace.click({ modifiers: ['ControlOrMeta'] }).catch(() => {});
    expect(await eventos(page)).toContainEqual(expect.objectContaining({ event: 'click_whatsapp', ubicacion: 'hero' }));
  });
});

test.describe('Interacción', () => {
  test('el video carga YouTube solo al dar clic', async ({ page }) => {
    await abrir(page);
    await expect(page.locator('iframe[src*="youtube"]')).toHaveCount(0);
    await page.getByRole('button', { name: /Reproducir video/ }).click();
    await expect(page.locator('iframe[src*="youtube-nocookie.com/embed/"]')).toHaveCount(1);
  });

  test('cambia entre tema claro y oscuro', async ({ page }) => {
    await abrir(page);
    const html = page.locator('html');
    const antes = await html.getAttribute('data-bs-theme');
    await page.getByRole('button', { name: 'Cambiar tema claro u oscuro' }).click();
    await expect(html).not.toHaveAttribute('data-bs-theme', antes!);
  });

  test('en móvil hay barra de acciones y menú desplegable', async ({ page, isMobile }) => {
    test.skip(!isMobile, 'solo en teléfonos');
    await abrir(page);
    await expect(page.getByRole('navigation', { name: 'Acciones rápidas' })).toBeVisible();
    const menu = page.getByRole('button', { name: 'Abrir menú' });
    await menu.click();
    await expect(menu).toHaveAttribute('aria-expanded', 'true');
    await page.getByRole('navigation', { name: 'Principal' }).getByRole('link', { name: 'Preguntas' }).click();
    await expect(menu).toHaveAttribute('aria-expanded', 'false');
  });

  test('sin desbordamiento horizontal', async ({ page }) => {
    await abrir(page);
    const desborde = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    expect(desborde).toBeLessThanOrEqual(0);
  });
});

test('accesibilidad: sin fallas graves de axe', async ({ page }, info) => {
  test.skip(info.project.name !== 'chromium', 'axe una vez basta');
  await abrir(page);
  // Recorre la página para que todas las secciones terminen su animación de entrada.
  await page.evaluate(async () => {
    for (let y = 0; y < document.body.scrollHeight; y += 500) {
      window.scrollTo({ top: y, behavior: 'instant' });
      await new Promise((r) => setTimeout(r, 50));
    }
  });
  await page.waitForTimeout(900);
  const resultado = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
  const graves = resultado.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical');
  expect(graves.map((v) => `${v.id}: ${v.nodes.map((n) => n.target.join(' ')).join(', ')}`)).toEqual([]);
});
