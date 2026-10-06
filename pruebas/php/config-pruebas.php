<?php
// Configuración para las pruebas: la base vive en el /tmp en memoria de PHP-WASM.
return [
    'carpeta_datos' => sys_get_temp_dir() . '/brisa-pruebas',
    'correo_destino' => '',
    'sal_ip' => 'sal-de-pruebas',
    'origenes' => [],
];
