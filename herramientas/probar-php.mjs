// Pruebas del backend PHP con PHP 8.3 en WebAssembly (no requiere PHP instalado).
// Uso: node --test probar-php.mjs
import { test, after } from 'node:test';
import assert from 'node:assert/strict';
import path from 'node:path';
import { loadNodeRuntime, createNodeFsMountHandler } from '@php-wasm/node';
import { PHP } from '@php-wasm/universal';

const raiz = path.resolve(import.meta.dirname, '..');
const php = new PHP(await loadNodeRuntime('8.3', { emscriptenOptions: { processId: 1 } }));
await php.mount('/repo', createNodeFsMountHandler(raiz));
after(() => php.exit());

const ENDPOINT = '/repo/site/public/api/cotizacion.php';
const env = { BRISA_CONFIG: '/repo/pruebas/php/config-pruebas.php' };

function enviar(campos, { accept = 'application/json', origin, ip = '10.0.0.1' } = {}) {
  const headers = { 'content-type': 'application/x-www-form-urlencoded', accept, host: 'brisasolar.mx' };
  if (origin) headers.origin = origin;
  return php.run({
    scriptPath: ENDPOINT,
    method: 'POST',
    body: new TextEncoder().encode(new URLSearchParams(campos).toString()),
    headers,
    env,
    $_SERVER: { REMOTE_ADDR: ip, HTTP_HOST: 'brisasolar.mx', SCRIPT_NAME: '/api/cotizacion.php' },
  });
}

const valido = {
  nombre: 'Rodrigo Saldaña',
  telefono: '442 123 4567',
  codigo_postal: '76230',
  tipo_inmueble: 'casa',
  recibo_bimestral: '3180',
  acepta_privacidad: '1',
  inicio: '0',
};

test('reglas de validación', async () => {
  const r = await php.run({ scriptPath: '/repo/pruebas/php/validacion_test.php' });
  assert.equal(r.exitCode, 0, r.text);
  assert.match(r.text, /0 fallas/);
});

test('un envío válido se guarda y regresa folio', async () => {
  const r = await enviar(valido, { ip: '10.0.0.2' });
  assert.equal(r.httpStatusCode, 201, r.text + r.errors);
  const json = JSON.parse(r.text);
  assert.equal(json.ok, true);
  assert.match(json.folio, /^BS-\d{6}-[A-Z2-9]{4}$/);
});

test('un envío con errores responde 422 con los campos', async () => {
  const r = await enviar({ ...valido, telefono: '123', codigo_postal: '' }, { ip: '10.0.0.3' });
  assert.equal(r.httpStatusCode, 422);
  const json = JSON.parse(r.text);
  assert.deepEqual(Object.keys(json.campos).sort(), ['codigo_postal', 'telefono']);
});

test('sin JavaScript redirige a la página de gracias', async () => {
  const r = await enviar({ ...valido, inicio: '' }, { accept: 'text/html', ip: '10.0.0.4' });
  assert.equal(r.httpStatusCode, 303);
  assert.match(r.headers.location[0], /^\/gracias\/\?folio=BS-/);
});

test('rechaza peticiones desde otro dominio', async () => {
  const r = await enviar(valido, { origin: 'https://sitio-ajeno.example', ip: '10.0.0.5' });
  assert.equal(r.httpStatusCode, 403);
});

test('al bot le contesta éxito falso sin guardar', async () => {
  const r = await enviar({ ...valido, sitio_web: 'http://spam.example' }, { ip: '10.0.0.6' });
  assert.equal(r.httpStatusCode, 200);
  assert.match(JSON.parse(r.text).folio, /-0000$/);
});

test('limita a 5 envíos por IP cada 10 minutos', async () => {
  const codigos = [];
  for (let i = 0; i < 6; i++) codigos.push((await enviar(valido, { ip: '10.0.0.7' })).httpStatusCode);
  assert.deepEqual(codigos, [201, 201, 201, 201, 201, 429]);
});

test('GET no está permitido', async () => {
  const r = await php.run({ scriptPath: ENDPOINT, method: 'GET', env });
  assert.equal(r.httpStatusCode, 405);
});
