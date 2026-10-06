# Despliegue: hosting, dominio y publicación

El mismo código se publica de tres formas:

| Destino | Formulario | Para qué |
|---|---|---|
| Hosting compartido con PHP (cPanel, Hostinger, Neubox…) | `api/cotizacion.php` guarda en SQLite y avisa por correo | Producción para un cliente |
| WordPress | El tema guarda los prospectos en el administrador | Cliente que quiere editar contenido |
| GitHub Pages | Modo demostración (no envía datos) | Portafolio |

## 1. Hosting compartido con PHP

### Compilar

```bash
cd site
npm ci
SITE=https://tu-dominio.mx PUBLIC_GTM_ID=GTM-XXXXXXX npm run build
```

`SITE` define la URL canónica, el sitemap y las etiquetas Open Graph. Todo queda en `site/dist/`, incluidos `api/` y `.htaccess`.

### Subir

1. En cPanel, abre *Administrador de archivos* y entra a `public_html/` (o usa FTP/SFTP con FileZilla).
2. Sube el **contenido** de `site/dist/`, no la carpeta: `index.html` debe quedar directamente en `public_html/`.
3. Activa *Mostrar archivos ocultos* para confirmar que `.htaccess` y `api/.htaccess` se subieron.

### Configurar el formulario

1. Copia `api/config.example.php` como `brisa-config.php` **un nivel arriba** de `public_html/` (por ejemplo `/home/usuario/brisa-config.php`). Ahí no se puede descargar desde el navegador.
2. Edita `correo_destino`, `correo_remitente` (de tu dominio, para que no caiga en spam) y `sal_ip` (una cadena larga y aleatoria).
3. Cambia `carpeta_datos` a una ruta fuera de `public_html/`, por ejemplo `/home/usuario/datos-brisa`.
4. En *Seleccionar versión de PHP*, elige PHP 8.1 o superior con las extensiones `pdo_sqlite` y `mbstring`.

Si el hosting no permite archivos fuera de `public_html/`, usa `api/config.php`: el `.htaccess` de `api/` bloquea su descarga, y la carpeta `api/inc/` (código y base de datos) responde 403.

### Probar

```bash
curl -i -X POST https://tu-dominio.mx/api/cotizacion.php \
  -H "Accept: application/json" \
  -d "nombre=Prueba&telefono=4421234567&codigo_postal=76000&tipo_inmueble=casa&recibo_bimestral=2500&acepta_privacidad=1"
```

Debe responder `201` con `{"ok":true,"folio":"BS-…"}`. Después, revisa que llegó el correo y que `https://tu-dominio.mx/api/inc/validacion.php` responde 403.

Si el correo no llega, el hosting probablemente bloquea `mail()`. Configura SMTP autenticado (PHPMailer o el SMTP del proveedor de correo) y agrega registros SPF y DKIM al dominio.

## 2. Dominio y DNS

En el panel del registrador (GoDaddy, Namecheap, Akky, Cloudflare…):

| Tipo | Nombre | Valor | Para |
|---|---|---|---|
| A | `@` | IP del hosting (aparece en cPanel) | Dominio raíz |
| CNAME | `www` | `tu-dominio.mx` | Versión con www |
| TXT | `@` | `v=spf1 include:_spf.tu-hosting.com ~all` | Que el correo del formulario no caiga en spam |
| MX | `@` | Servidor de correo del proveedor | Recibir correo en el dominio |

Si el dominio usa los *nameservers* del hosting, cPanel crea estos registros solo. Los cambios de DNS tardan de minutos a 48 horas en propagarse; se revisan con `nslookup tu-dominio.mx` o en dnschecker.org.

### HTTPS

1. En cPanel, *SSL/TLS Status → Run AutoSSL* emite un certificado gratuito de Let's Encrypt (o Sectigo, según el hosting).
2. Cuando el certificado esté activo, el `.htaccess` redirige todo a `https://` sin `www` con un 301. Una sola URL canónica evita contenido duplicado en Google.
3. El `.htaccess` también envía HSTS, cabeceras de seguridad, compresión y caché de un año para `/_astro/` (archivos con hash en el nombre).

### Después de publicar

- Da de alta el dominio en **Google Search Console** (verificación por registro TXT) y envía `https://tu-dominio.mx/sitemap-index.xml`.
- Crea o reclama el **Perfil de Negocio de Google** con la misma dirección y teléfono que el JSON-LD de la página.
- Revisa la página con PageSpeed Insights y con la [prueba de resultados enriquecidos](https://search.google.com/test/rich-results) (debe detectar `LocalBusiness` y `FAQPage`).

## 3. WordPress

1. Comprime la carpeta `wordpress/brisa-solar/` como `brisa-solar.zip`.
2. *Apariencia → Temas → Añadir nuevo → Subir tema*, sube el zip y actívalo.
3. Para cargar el contenido de ejemplo, ejecuta con WP-CLI: `wp eval 'brisa_importar_demo();'`. Si no, crea a mano una página *Inicio* como portada y una página con slug `gracias`.
4. Configura teléfono, WhatsApp, GTM y el correo de ventas en *Apariencia → Personalizar → Brisa Solar*.
5. *Ajustes → Enlaces permanentes → Nombre de la entrada*.

Para el correo de prospectos en producción conviene un plugin SMTP (WP Mail SMTP) en lugar del `mail()` del hosting. Detalles del tema en [wordpress.md](wordpress.md).

## 4. GitHub Pages (demo del portafolio)

El flujo `.github/workflows/demo.yml` compila con `BASE=/<repositorio>/` y `PUBLIC_MODO_DEMO=true` en cada push a `main`. Para activarlo: *Settings → Pages → Source: GitHub Actions*.

En modo demostración el formulario valida igual, simula el envío y redirige a la página de gracias con un folio `DEMO-1234`. Bajo el botón aparece un aviso de que no se envían datos.

Para usar un dominio propio con GitHub Pages: registro `CNAME www → usuario.github.io` y cuatro registros `A @` a las IP de GitHub Pages; luego compila con `BASE=/` y `SITE=https://tu-dominio.mx`.
