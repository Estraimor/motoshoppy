<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../conexion/conexion.php';

try {

    $stmt = $conexion->query("
        SELECT usd_ars, usd_pyg, ars_pyg, fecha_actualizacion
        FROM cotizacion
        ORDER BY id DESC
        LIMIT 1
    ");

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo json_encode([]);
        exit;
    }

    echo json_encode($data);

} catch (Throwable $e) {
    echo json_encode([
        'error' => true,
        'msg' => $e->getMessage()
    ]);
}