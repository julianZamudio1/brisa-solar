<?php
/**
 * Guarda prospectos en SQLite (incluido en casi todo hosting compartido con PHP 8).
 * La carpeta de datos debe quedar fuera del alcance web: ver .htaccess y config.
 */

declare(strict_types=1);

function abrir_base(string $carpeta): PDO
{
    if (!is_dir($carpeta) && !mkdir($carpeta, 0750, true) && !is_dir($carpeta)) {
        throw new RuntimeException("No se pudo crear la carpeta de datos: $carpeta");
    }
    $pdo = new PDO('sqlite:' . $carpeta . '/prospectos.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS prospectos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            folio TEXT NOT NULL UNIQUE,
            creado TEXT NOT NULL,
            nombre TEXT NOT NULL,
            telefono TEXT NOT NULL,
            correo TEXT,
            codigo_postal TEXT NOT NULL,
            tipo_inmueble TEXT NOT NULL,
            recibo_bimestral INTEGER NOT NULL,
            mensaje TEXT,
            origen TEXT,
            id_evento TEXT,
            campana TEXT,
            ip_hash TEXT
        )
        SQL);
    $pdo->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS intentos (
            ip_hash TEXT NOT NULL,
            momento INTEGER NOT NULL
        )
        SQL);
    $pdo->exec('CREATE INDEX IF NOT EXISTS intentos_ip ON intentos (ip_hash, momento)');
    return $pdo;
}

/** Limita envíos por IP en una ventana de tiempo. Devuelve false si se excedió. */
function registrar_intento(PDO $pdo, string $ipHash, int $ahora, int $maximo = 5, int $ventana = 600): bool
{
    $pdo->prepare('DELETE FROM intentos WHERE momento < ?')->execute([$ahora - $ventana]);
    $consulta = $pdo->prepare('SELECT COUNT(*) FROM intentos WHERE ip_hash = ?');
    $consulta->execute([$ipHash]);
    if ((int) $consulta->fetchColumn() >= $maximo) {
        return false;
    }
    $pdo->prepare('INSERT INTO intentos (ip_hash, momento) VALUES (?, ?)')->execute([$ipHash, $ahora]);
    return true;
}

/** @param array<string, mixed> $datos */
function guardar_prospecto(PDO $pdo, array $datos, string $ipHash, DateTimeImmutable $ahora): string
{
    // Folio corto y legible para dictarlo por teléfono: BS-260106-7K2Q
    $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $sufijo = '';
    for ($i = 0; $i < 4; $i++) {
        $sufijo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
    }
    $folio = 'BS-' . $ahora->format('ymd') . '-' . $sufijo;

    $pdo->prepare(<<<SQL
        INSERT INTO prospectos (folio, creado, nombre, telefono, correo, codigo_postal, tipo_inmueble,
            recibo_bimestral, mensaje, origen, id_evento, campana, ip_hash)
        VALUES (:folio, :creado, :nombre, :telefono, :correo, :cp, :tipo, :recibo, :mensaje, :origen,
            :id_evento, :campana, :ip)
        SQL)->execute([
        ':folio' => $folio,
        ':creado' => $ahora->format(DATE_ATOM),
        ':nombre' => $datos['nombre'],
        ':telefono' => $datos['telefono'],
        ':correo' => $datos['correo'] ?: null,
        ':cp' => $datos['codigo_postal'],
        ':tipo' => $datos['tipo_inmueble'],
        ':recibo' => $datos['recibo_bimestral'],
        ':mensaje' => $datos['mensaje'] ?: null,
        ':origen' => $datos['origen'],
        ':id_evento' => $datos['id_evento'] ?: null,
        ':campana' => $datos['campana'] ? json_encode($datos['campana'], JSON_UNESCAPED_UNICODE) : null,
        ':ip' => $ipHash,
    ]);

    return $folio;
}
