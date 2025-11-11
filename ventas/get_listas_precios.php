<?php
header('Content-Type: application/json; charset=utf-8');
require '../conexion/conexion.php';

try {
    $sql = "SELECT nombre_lista, porcentaje_descuento 
            FROM precio_lista 
            WHERE activo = 1 
            ORDER BY id ASC";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $listas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'ok' => true,
        'listas' => array_map(function($r) {
            return [
                'nombre_lista' => $r['nombre_lista'],
                'porcentaje_descuento' => (float)$r['porcentaje_descuento']
            ];
        }, $listas)
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'msg' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
