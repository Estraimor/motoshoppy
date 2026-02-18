<?php
require '../../conexion/conexion.php';

$stmt = $conexion->query("
    SELECT 
    m.idmovimiento_stock,
    m.fecha,
    m.cantidad,
    m.tipo,
    p.nombre AS producto,
    ma.nombre_marca AS marca,
    CONCAT(u.nombre,' ',u.apellido) AS usuario
FROM movimiento_stock m
LEFT JOIN producto p 
    ON p.idProducto = m.producto_idProducto
LEFT JOIN marcas ma 
    ON ma.idmarcas = p.marcas_idmarcas
LEFT JOIN usuario u 
    ON u.idusuario = m.usuario_id
ORDER BY m.fecha DESC;
");

$movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "data" => $movimientos
]);
