const purgecss = require('@fullhuman/postcss-purgecss').default ?? require('@fullhuman/postcss-purgecss');
const opciones = require('./purgecss.config.cjs');

// PurgeCSS solo en el build: quita las utilidades de Bootstrap que la landing no usa.
module.exports = {
  plugins:
    process.env.NODE_ENV === 'production'
      ? [purgecss({ ...opciones, content: ['./src/**/*.{astro,ts,md}'] })]
      : [],
};
