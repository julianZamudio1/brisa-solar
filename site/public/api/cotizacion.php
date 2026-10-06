<?php
/**
 * Recibe el formulario de cotización.
 * - Con fetch (Accept: application/json) responde JSON.
 * - Sin JavaScript hace un POST normal y redirige a /gracias/ o regresa con error.
 */

declare(strict_types=1);

require __DIR__ . '/inc/validacion.php';
require __DIR__ . '/inc/almacen.php';
require __DIR__ . '/inc/correo.php';

// La configuración real vive fuera de la carpeta pública si el hosting lo permite.
$rutasConfig = array_filter([getenv('BRISA_CONFIG') ?: null, dirname(__DIR__, 2) . '/brisa-config.php', __DIR__ . '/config.php']);
$config = ['carpeta_datos' => __DIR__ . '/inc/datos', 'correo_destino' => '', 'correo_remitente' => 'no-responder@localhost', 'sal_ip' => 'cambia-esta-sal', 'origenes' => []];
foreach ($rutasConfig as $ruta) {
    if (is_file($ruta)) {
        $config = array_merge($config, require $ruta);
        break;
    }
}

$quiereJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

/** @param array<string, mixed> $cuerpo */
function responder(int $estado, array $cuerpo, bool $json, string $redireccion): never
{
    if ($json) {
        http_response_code($estado);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: ' . $redireccion, true, 303);
    }
    exit;
}

$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/api/cotizacion.php', 2), '/') . '/';
$paginaError = $base . '#cotizar';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    responder(405, ['ok' => false, 'error' => 'Método no permitido'], true, $paginaError);
}

// Solo se aceptan envíos desde el propio sitio (protección básica contra CSRF y abuso desde otros dominios).
$origen = $_SERVER['HTTP_ORIGIN'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';
$permitidos = array_merge(["https://$host", "http://$host"], $config['origenes']);
if ($origen !== '' && !in_array($origen, $permitidos, true)) {
    responder(403, ['ok' => false, 'error' => 'Origen no permitido'], $quiereJson, $paginaError);
}

$entrada = $_POST;
$ahora = new DateTimeImmutable('now', new DateTimeZone('America/Mexico_City'));

// A un bot se le responde como si todo hubiera salido bien, para no darle pistas.
if (parece_bot($entrada, (int) floor(microtime(true) * 1000))) {
    responder(200, ['ok' => true, 'folio' => 'BS-' . $ahora->format('ymd') . '-0000'], $quiereJson, $base . 'gracias/');
}

[$datos, $errores] = validar_prospecto($entrada);
if ($errores) {
    responder(422, ['ok' => false, 'error' => 'Revisa los campos marcados.', 'campos' => $errores], $quiereJson, $paginaError);
}

try {
    $pdo = abrir_base($config['carpeta_datos']);
    $ipHash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . $config['sal_ip']);
    if (!registrar_intento($pdo, $ipHash, $ahora->getTimestamp())) {
        responder(429, ['ok' => false, 'error' => 'Demasiados envíos. Intenta en unos minutos.'], $quiereJson, $paginaError);
    }
    $folio = guardar_prospecto($pdo, $datos, $ipHash, $ahora);
} catch (Throwable $e) {
    error_log('[cotizacion] ' . $e->getMessage());
    responder(500, ['ok' => false, 'error' => 'No pudimos guardar tu solicitud.'], $quiereJson, $paginaError);
}

notificar_prospecto($config, $datos, $folio);

responder(201, ['ok' => true, 'folio' => $folio], $quiereJson, $base . 'gracias/?folio=' . rawurlencode($folio));
