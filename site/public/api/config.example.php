<?php
// Copia este archivo como brisa-config.php UN NIVEL ARRIBA de public_html (fuera del alcance web).
// Si el hosting no lo permite, cópialo como api/config.php: el .htaccess bloquea su descarga.
return [
    // Carpeta para la base SQLite. Ideal: fuera de public_html, por ejemplo /home/usuario/datos-brisa
    'carpeta_datos' => __DIR__ . '/datos-brisa',
    'correo_destino' => 'ventas@tu-dominio.mx',
    'correo_remitente' => 'Formulario web <no-responder@tu-dominio.mx>',
    // Cadena aleatoria para que el hash de la IP no se pueda revertir.
    'sal_ip' => 'cambia-esto-por-una-cadena-larga-y-aleatoria',
    // Dominios extra desde los que se acepta el formulario (por ejemplo la versión con www).
    'origenes' => ['https://www.tu-dominio.mx'],
];
