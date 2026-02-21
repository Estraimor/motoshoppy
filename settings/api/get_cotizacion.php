<?php
require_once '../conexion/conexion.php';

$stmt = $conexion->query("
    SELECT *
    FROM cotizacion
    ORDER BY fecha_actualizacion DESC
    LIMIT 1
");

$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($data ?: []);
