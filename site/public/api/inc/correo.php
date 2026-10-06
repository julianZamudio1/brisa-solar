<?php

declare(strict_types=1);

/**
 * Aviso interno por correo. Usa mail() del hosting; en producción conviene SMTP
 * autenticado (PHPMailer) para que no caiga en spam.
 *
 * @param array<string, mixed> $datos
 */
function notificar_prospecto(array $config, array $datos, string $folio): bool
{
    if (empty($config['correo_destino'])) {
        return false;
    }

    $tipo = $datos['tipo_inmueble'] === 'negocio' ? 'Negocio' : 'Casa';
    $recibo = number_format((float) $datos['recibo_bimestral'], 0, '.', ',');
    $lineas = [
        "Folio: $folio",
        "Nombre: {$datos['nombre']}",
        "Teléfono: {$datos['telefono']}",
        'Correo: ' . ($datos['correo'] ?: 'sin correo'),
        "Código postal: {$datos['codigo_postal']}",
        "Inmueble: $tipo",
        "Recibo bimestral: \$$recibo",
        "Origen: {$datos['origen']}",
    ];
    foreach ($datos['campana'] as $clave => $valor) {
        $lineas[] = "$clave: $valor";
    }
    if ($datos['mensaje']) {
        $lineas[] = '';
        $lineas[] = "Comentarios: {$datos['mensaje']}";
    }

    $cabeceras = [
        'From: ' . $config['correo_remitente'],
        'Content-Type: text/plain; charset=UTF-8',
    ];
    // El correo del prospecto pasó por FILTER_VALIDATE_EMAIL, así que no trae saltos de línea.
    if ($datos['correo']) {
        $cabeceras[] = 'Reply-To: ' . $datos['correo'];
    }

    $asunto = '=?UTF-8?B?' . base64_encode("Nuevo prospecto $folio ($tipo, \$$recibo)") . '?=';
    return mail($config['correo_destino'], $asunto, implode("\r\n", $lineas), implode("\r\n", $cabeceras));
}
