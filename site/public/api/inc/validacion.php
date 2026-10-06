<?php
/**
 * Reglas del formulario de cotización. Son las mismas que valida el navegador,
 * pero el servidor nunca confía en el cliente.
 */

declare(strict_types=1);

const TIPOS_INMUEBLE = ['casa', 'negocio'];
const CAMPOS_CAMPANA = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid'];

function limpiar_texto(mixed $valor, int $maximo): string
{
    $texto = is_string($valor) ? $valor : '';
    // Quita caracteres de control (incluye saltos de línea que permitirían inyectar cabeceras de correo).
    $texto = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $texto) ?? '';
    $texto = trim(preg_replace('/\s+/u', ' ', $texto) ?? '');
    return mb_substr($texto, 0, $maximo);
}

/** Normaliza un teléfono mexicano a 10 dígitos; devuelve null si no es válido. */
function normalizar_telefono(string $telefono): ?string
{
    $digitos = preg_replace('/\D+/', '', $telefono) ?? '';
    if (strlen($digitos) === 12 && str_starts_with($digitos, '52')) {
        $digitos = substr($digitos, 2);
    }
    return strlen($digitos) === 10 ? $digitos : null;
}

/**
 * @param array<string, mixed> $entrada
 * @return array{0: array<string, mixed>, 1: array<string, string>} [datos limpios, errores por campo]
 */
function validar_prospecto(array $entrada): array
{
    $errores = [];

    $nombre = limpiar_texto($entrada['nombre'] ?? '', 80);
    if (mb_strlen($nombre) < 3) {
        $errores['nombre'] = 'Escribe tu nombre completo.';
    }

    $telefono = normalizar_telefono(limpiar_texto($entrada['telefono'] ?? '', 20));
    if ($telefono === null) {
        $errores['telefono'] = 'Escribe un teléfono de 10 dígitos.';
    }

    $correo = limpiar_texto($entrada['correo'] ?? '', 120);
    if ($correo !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
        $errores['correo'] = 'Revisa el correo, parece incompleto.';
    }

    $cp = limpiar_texto($entrada['codigo_postal'] ?? '', 5);
    if (!preg_match('/^\d{5}$/', $cp)) {
        $errores['codigo_postal'] = 'El código postal tiene 5 dígitos.';
    }

    $tipo = limpiar_texto($entrada['tipo_inmueble'] ?? '', 10);
    if (!in_array($tipo, TIPOS_INMUEBLE, true)) {
        $errores['tipo_inmueble'] = 'Elige casa o negocio.';
    }

    $recibo = filter_var($entrada['recibo_bimestral'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 100, 'max_range' => 2000000],
    ]);
    if ($recibo === false) {
        $errores['recibo_bimestral'] = 'Escribe un monto entre $100 y $2,000,000.';
    }

    if (($entrada['acepta_privacidad'] ?? '') !== '1') {
        $errores['acepta_privacidad'] = 'Debes aceptar el aviso de privacidad.';
    }

    $campana = [];
    foreach (CAMPOS_CAMPANA as $campo) {
        $valor = limpiar_texto($entrada[$campo] ?? '', 150);
        if ($valor !== '') {
            $campana[$campo] = $valor;
        }
    }

    $datos = [
        'nombre' => $nombre,
        'telefono' => $telefono,
        'correo' => $correo,
        'codigo_postal' => $cp,
        'tipo_inmueble' => $tipo,
        'recibo_bimestral' => $recibo === false ? null : $recibo,
        'mensaje' => limpiar_texto($entrada['mensaje'] ?? '', 600),
        'origen' => limpiar_texto($entrada['origen'] ?? 'formulario', 30) ?: 'formulario',
        'id_evento' => preg_match('/^[a-f0-9-]{8,64}$/i', (string) ($entrada['id_evento'] ?? '')) ? $entrada['id_evento'] : '',
        'campana' => $campana,
    ];

    return [$datos, $errores];
}

/**
 * Señales de bot: campo trampa lleno o envío en menos de 3 segundos desde que cargó la página.
 * @param array<string, mixed> $entrada
 */
function parece_bot(array $entrada, int $ahoraMs): bool
{
    if (trim((string) ($entrada['sitio_web'] ?? '')) !== '') {
        return true;
    }
    $inicio = filter_var($entrada['inicio'] ?? null, FILTER_VALIDATE_INT);
    if ($inicio === false) {
        return false; // Sin JS no hay marca de tiempo; no se castiga.
    }
    return ($ahoraMs - $inicio) < 3000;
}
