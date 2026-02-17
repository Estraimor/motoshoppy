<?php
require '../../conexion/conexion.php';

$stmt = $conexion->query("
    SELECT m.*, p.nombre
    FROM movimiento_stock m
    JOIN producto p ON p.idproducto = m.producto_idProducto
    ORDER BY m.fecha DESC
");

echo json_encode([
    "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);
