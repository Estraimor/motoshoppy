<?php
require_once '../conexion/conexion.php';
header('Content-Type: application/json; charset=utf-8');

try {
    // Traer la última cotización registrada
    $sql = "SELECT usd_ars, usd_pyg, ars_pyg, fecha_actualizacion, fuente
            FROM cotizacion
            ORDER BY id DESC
            LIMIT 1";
    $stmt = $conexion->query($sql);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no hay datos, devuelve un array vacío
    if (!$data) {
        echo json_encode([
            "error" => true,
            "mensaje" => "No hay cotización registrada."
        ]);
        exit;
    }

    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode([
        "error" => true,
        "mensaje" => "Error al obtener cotización: " . $e->getMessage()
    ]);
}
