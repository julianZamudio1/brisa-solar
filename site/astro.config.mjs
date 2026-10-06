// @ts-check
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

// SITE y BASE permiten publicar el mismo código en un dominio propio
// (https://brisasolar.mx/) o en una subcarpeta como GitHub Pages (/brisa-solar/).
const site = process.env.SITE ?? 'https://brisasolar.mx';
const base = process.env.BASE ?? '/';

export default defineConfig({
  site,
  base,
  trailingSlash: 'always',
  devToolbar: { enabled: false },
  integrations: [
    sitemap({
      filter: (page) => !page.includes('/gracias/'),
    }),
  ],
  build: {
    // El CSS crítico de una landing es pequeño: incrustarlo evita una petición que bloquea el render.
    inlineStylesheets: 'always',
  },
  image: {
    layout: 'constrained',
  },
  vite: {
    css: {
      preprocessorOptions: {
        scss: {
          // Bootstrap 5.3 todavía usa @import y funciones globales de Sass.
          quietDeps: true,
          silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
        },
      },
    },
  },
});
