<?php
require_once '../conexion/conexion.php';

$cliente = $_POST['cliente'];

$stmt = $conexion->prepare("
    SELECT p.nombre, COUNT(d.idDetalle) AS veces
    FROM ventas v
    JOIN detalle_venta d ON d.ventas_idVenta = v.idVenta
    JOIN producto p ON p.idProducto = d.producto_idProducto
    WHERE v.clientes_idCliente = :cliente
    GROUP BY p.idProducto
    ORDER BY veces DESC
    LIMIT 1
");

$stmt->execute([':cliente'=>$cliente]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($data);
