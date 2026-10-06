<?php
// Pruebas unitarias de las reglas del formulario. Se ejecutan con: npm run test:php (en herramientas/)

declare(strict_types=1);

require __DIR__ . '/../../site/public/api/inc/validacion.php';

$fallas = 0;
$total = 0;

function afirmar(bool $condicion, string $descripcion): void
{
    global $fallas, $total;
    $total++;
    if (!$condicion) {
        $fallas++;
        echo "FALLA: $descripcion\n";
    }
}

$valido = [
    'nombre' => '  Mariana   Echeverría ',
    'telefono' => '+52 (442) 123-4567',
    'correo' => 'mariana@example.com',
    'codigo_postal' => '76000',
    'tipo_inmueble' => 'casa',
    'recibo_bimestral' => '2500',
    'acepta_privacidad' => '1',
    'utm_source' => 'google',
    'utm_campaign' => 'paneles-qro',
    'id_evento' => '3f2b8c1e-9d4a-4e1b-8f00-1a2b3c4d5e6f',
];

[$datos, $errores] = validar_prospecto($valido);
afirmar($errores === [], 'un prospecto válido no tiene errores');
afirmar($datos['nombre'] === 'Mariana Echeverría', 'normaliza espacios del nombre');
afirmar($datos['telefono'] === '4421234567', 'quita lada 52 y símbolos del teléfono');
afirmar($datos['recibo_bimestral'] === 2500, 'convierte el recibo a entero');
afirmar($datos['campana'] === ['utm_source' => 'google', 'utm_campaign' => 'paneles-qro'], 'conserva solo UTM con valor');
afirmar($datos['origen'] === 'formulario', 'origen por defecto');

[, $errores] = validar_prospecto([]);
afirmar(array_keys($errores) === ['nombre', 'telefono', 'codigo_postal', 'tipo_inmueble', 'recibo_bimestral', 'acepta_privacidad'], 'reporta todos los campos obligatorios');

[, $errores] = validar_prospecto(['telefono' => '442 123 45'] + $valido);
afirmar(isset($errores['telefono']), 'rechaza teléfonos de menos de 10 dígitos');

[, $errores] = validar_prospecto(['correo' => 'mariana@'] + $valido);
afirmar(isset($errores['correo']), 'rechaza correos incompletos');

[, $errores] = validar_prospecto(['correo' => ''] + $valido);
afirmar(!isset($errores['correo']), 'el correo es opcional');

[, $errores] = validar_prospecto(['tipo_inmueble' => 'oficina'] + $valido);
afirmar(isset($errores['tipo_inmueble']), 'solo acepta casa o negocio');

[, $errores] = validar_prospecto(['recibo_bimestral' => '50'] + $valido);
afirmar(isset($errores['recibo_bimestral']), 'rechaza recibos menores a $100');

[$datos] = validar_prospecto(['nombre' => "Juan\r\nBcc: spam@example.com"] + $valido);
afirmar(!str_contains($datos['nombre'], "\n"), 'elimina saltos de línea (inyección de cabeceras)');

[$datos] = validar_prospecto(['id_evento' => '<script>'] + $valido);
afirmar($datos['id_evento'] === '', 'descarta id de evento con formato inválido');

afirmar(parece_bot(['sitio_web' => 'http://spam.example'], 10_000), 'detecta el campo trampa');
afirmar(parece_bot(['inicio' => '9000'], 10_000), 'detecta envíos en menos de 3 segundos');
afirmar(!parece_bot(['inicio' => '1000'], 10_000), 'acepta envíos humanos');
afirmar(!parece_bot([], 10_000), 'no castiga a quien no tiene JavaScript');

echo "$total pruebas, $fallas fallas\n";
exit($fallas === 0 ? 0 : 1);
