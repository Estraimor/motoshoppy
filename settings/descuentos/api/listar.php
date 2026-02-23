<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../conexion/conexion.php';

try {
    $stmt = $conexion->query("
        SELECT 
            id,
            nombre_lista,
            porcentaje_descuento,
            activo
        FROM precio_lista
        ORDER BY id DESC
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode([
        'data' => [],
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}