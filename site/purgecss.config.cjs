// Clases que existen solo en tiempo de ejecución (las agrega JS) o en el HTML de WordPress.
module.exports = {
  safelist: {
    standard: ['js', 'visible', 'abierto', 'is-invalid', 'is-valid', 'd-none', 'panel-medicion', 'was-validated', /^wp-/, /^admin-bar/, 'screen-reader-text', 'alignwide', 'alignfull'],
    deep: [/data-bs-theme/],
    greedy: [/^tema-/, /aria-busy/, /details-content/],
  },
  variables: false,
  keyframes: true,
  fontFace: false,
};
