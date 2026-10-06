// Compila los recursos compartidos hacia el tema de WordPress (../wordpress/brisa-solar).
// Una sola fuente de verdad: el SCSS, el TypeScript y los datos de este proyecto de Astro.
// Uso: npm run tema
import { mkdir, readFile, writeFile, copyFile, rm, readdir } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import * as sass from 'sass';
import * as esbuild from 'esbuild';
import sharp from 'sharp';
import { PurgeCSS } from 'purgecss';
import { createRequire } from 'node:module';

const opcionesPurga = createRequire(import.meta.url)('../purgecss.config.cjs');

const raiz = path.resolve(import.meta.dirname, '..');
const tema = path.resolve(raiz, '../wordpress/brisa-solar');
const assets = path.join(tema, 'assets');
const nm = path.join(raiz, 'node_modules');

async function limpiar(carpeta) {
  await rm(carpeta, { recursive: true, force: true });
  await mkdir(carpeta, { recursive: true });
}

/** Convierte un valor de JS a un literal de PHP. */
function php(valor, sangria = '') {
  if (valor === null || valor === undefined) return 'null';
  if (typeof valor === 'boolean') return valor ? 'true' : 'false';
  if (typeof valor === 'number') return Number.isFinite(valor) ? String(valor) : 'INF';
  if (typeof valor === 'string') return `'${valor.replace(/\\/g, '\\\\').replace(/'/g, "\\'")}'`;
  const s = sangria + '    ';
  if (Array.isArray(valor)) return `[\n${valor.map((v) => s + php(v, s)).join(',\n')},\n${sangria}]`;
  return `[\n${Object.entries(valor).map(([k, v]) => `${s}${php(k)} => ${php(v, s)}`).join(',\n')},\n${sangria}]`;
}

const encabezadoPhp = (origen) =>
  `<?php\n// GENERADO por site/scripts/construir-tema.mjs a partir de ${origen}. No editar a mano.\n\nreturn `;

// 1. CSS: el mismo main.scss de la landing.
await mkdir(path.join(assets, 'css'), { recursive: true });
const css = sass.compile(path.join(raiz, 'src/styles/main.scss'), {
  loadPaths: [nm],
  style: 'compressed',
  quietDeps: true,
  silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
});
// Se escribe primero completo; al final se purga contra las plantillas PHP y el JS del tema.
await writeFile(path.join(assets, 'css/brisa.css'), css.css);

// 2. Fuentes: solo el subconjunto latino, autoalojadas.
await limpiar(path.join(assets, 'fuentes'));
const fuentes = [
  ['Figtree Variable', '@fontsource-variable/figtree/files/figtree-latin-wght-normal.woff2', 'figtree.woff2', '300 900'],
  ['Bricolage Grotesque Variable', '@fontsource-variable/bricolage-grotesque/files/bricolage-grotesque-latin-wght-normal.woff2', 'bricolage-grotesque.woff2', '200 800'],
];
let fuentesCss = '';
for (const [familia, origen, destino, pesos] of fuentes) {
  await copyFile(path.join(nm, origen), path.join(assets, 'fuentes', destino));
  fuentesCss += `@font-face{font-family:'${familia}';font-style:normal;font-display:swap;font-weight:${pesos};src:url(../fuentes/${destino}) format('woff2');unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD}\n`;
}
await writeFile(path.join(assets, 'css/fuentes.css'), fuentesCss);

// 3. JS: un solo archivo con los mismos módulos que usan los componentes de Astro.
await esbuild.build({
  entryPoints: [path.join(raiz, 'src/scripts/tema-wordpress.ts')],
  bundle: true,
  minify: true,
  format: 'esm',
  target: 'es2020',
  outfile: path.join(assets, 'js/brisa.js'),
  legalComments: 'none',
});

// 4. Íconos de Phosphor usados en el proyecto.
await limpiar(path.join(assets, 'iconos'));
const fuentesAstro = await Promise.all(
  ['components', 'pages'].flatMap((d) => [d]).map(async (d) => {
    const dir = path.join(raiz, 'src', d);
    const archivos = await readdir(dir);
    return Promise.all(archivos.map((a) => readFile(path.join(dir, a), 'utf8')));
  }),
);
const usados = new Set();
for (const texto of fuentesAstro.flat()) {
  for (const m of texto.matchAll(/(?:nombre="|icono: ')([a-z-]+)/g)) usados.add(m[1]);
}
for (const nombre of usados) {
  for (const estilo of ['regular', 'duotone', 'fill']) {
    const archivo = estilo === 'regular' ? `${nombre}.svg` : `${nombre}-${estilo}.svg`;
    const origen = path.join(nm, '@phosphor-icons/core/assets', estilo, archivo);
    if (existsSync(origen)) {
      await mkdir(path.join(assets, 'iconos', estilo), { recursive: true });
      await copyFile(origen, path.join(assets, 'iconos', estilo, archivo));
    }
  }
}

// 5. Fotos del tema (hero, beneficios, póster del video) y fotos del contenido de demostración.
await limpiar(path.join(assets, 'img'));
const fotos = path.join(raiz, 'src/assets/fotos');
for (const [nombre, anchos] of [
  ['hero-instalador', [720, 1280]],
  ['panel-detalle', [800, 1100]],
  ['garantia-casco', [640]],
  ['proceso-cableado', [960, 1280]],
]) {
  for (const ancho of anchos) {
    await sharp(path.join(fotos, `${nombre}.jpg`)).resize({ width: ancho }).webp({ quality: 74 }).toFile(path.join(assets, 'img', `${nombre}-${ancho}.webp`));
  }
}
await limpiar(path.join(tema, 'demo/fotos'));
for (const archivo of (await readdir(fotos)).filter((a) => a.startsWith('proyecto-'))) {
  await sharp(path.join(fotos, archivo)).resize({ width: 1400 }).jpeg({ quality: 78, mozjpeg: true }).toFile(path.join(tema, 'demo/fotos', archivo));
}

// 6. Datos: valores del negocio, contenido de demostración y el cálculo inicial de la calculadora.
const temporal = path.join(raiz, 'node_modules/.cache/construir-tema');
await mkdir(temporal, { recursive: true });
await esbuild.build({
  stdin: {
    contents: `export { sitio } from './src/data/sitio';
      export { preguntas } from './src/data/preguntas';
      export { testimonios, proyectos } from './src/data/contenido';
      export { calcular, HSP, SUPUESTOS, TARIFA } from './src/lib/calculadora';`,
    resolveDir: raiz,
    loader: 'ts',
  },
  bundle: true,
  format: 'esm',
  platform: 'node',
  outfile: path.join(temporal, 'datos.mjs'),
  define: { 'import.meta.env': '{}' },
});
const datos = await import(pathToFileURL(path.join(temporal, 'datos.mjs')).href + `?v=${Date.now()}`);
const inc = path.join(tema, 'inc/generado');
await limpiar(inc);
await writeFile(path.join(inc, 'sitio.php'), encabezadoPhp('src/data/sitio.ts') + php(datos.sitio) + ';\n');
await writeFile(
  path.join(inc, 'demo.php'),
  encabezadoPhp('src/data/preguntas.ts y src/data/contenido.ts') +
    php({ preguntas: datos.preguntas, testimonios: datos.testimonios, proyectos: datos.proyectos }) +
    ';\n',
);
await writeFile(
  path.join(inc, 'calculadora.php'),
  encabezadoPhp('src/lib/calculadora.ts') +
    php({
      inicial: datos.calcular({ reciboBimestral: 2500, tipo: 'casa', region: 'centro' }),
      hsp: datos.HSP,
      supuestos: datos.SUPUESTOS,
      tarifa: datos.TARIFA,
    }) +
    ';\n',
);

// 7. Reglas de validación del formulario: el mismo archivo que usa el endpoint PHP de la landing.
const validacion = await readFile(path.join(raiz, 'public/api/inc/validacion.php'), 'utf8');
await writeFile(
  path.join(inc, 'validacion.php'),
  validacion.replace('<?php', '<?php\n// GENERADO: copia de site/public/api/inc/validacion.php. No editar a mano.'),
);

// 8. PurgeCSS contra las plantillas del tema (mismas reglas de exclusión que la landing).
const [purgado] = await new PurgeCSS().purge({
  ...opcionesPurga,
  // PurgeCSS usa globs con diagonales normales también en Windows.
  content: [path.join(tema, '**/*.php'), path.join(assets, 'js/brisa.js')].map((p) => p.split(path.sep).join('/')),
  css: [{ raw: css.css }],
});
await writeFile(path.join(assets, 'css/brisa.css'), purgado.css);

console.log(`Tema actualizado en ${path.relative(process.cwd(), tema)} (${usados.size} íconos, CSS ${(purgado.css.length / 1024).toFixed(0)} KB)`);
