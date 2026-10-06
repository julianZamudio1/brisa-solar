import { defineConfig, devices } from '@playwright/test';

const PUERTO = 4322;

export default defineConfig({
  testDir: './pruebas',
  fullyParallel: true,
  // Cinco motores de navegador en paralelo saturan una laptop; en CI cada máquina es nueva.
  workers: process.env.CI ? undefined : 2,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : 'list',
  use: {
    baseURL: `http://localhost:${PUERTO}/`,
    locale: 'es-MX',
    trace: 'retain-on-failure',
  },
  // Compatibilidad entre navegadores: los tres motores y dos teléfonos.
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    { name: 'firefox', use: { ...devices['Desktop Firefox'] } },
    { name: 'webkit', use: { ...devices['Desktop Safari'] } },
    { name: 'android', use: { ...devices['Pixel 7'] } },
    { name: 'iphone', use: { ...devices['iPhone 14'] } },
  ],
  webServer: {
    command: `npm run build && npx astro preview --port ${PUERTO} --force`,
    url: `http://localhost:${PUERTO}/`,
    reuseExistingServer: !process.env.CI,
    timeout: 180_000,
  },
});
