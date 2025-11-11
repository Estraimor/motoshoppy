<?php
require_once '../conexion/conexion.php';
header('Content-Type: application/json; charset=UTF-8');

$sql = "
  SELECT 
    v.idVenta,
    v.fecha,
    u.usuario AS vendedor,
    c.apellido, c.nombre, c.dni,
    d.producto_id AS idProducto,
    p.nombre AS producto,
    d.cantidad,
    d.precio_unitario,
    (d.cantidad * d.precio_unitario) AS subtotal
  FROM ventas v
  LEFT JOIN usuario u   ON u.idusuario   = v.usuario_id
  LEFT JOIN clientes c  ON c.idCliente   = v.cliente_id
  JOIN detalle_venta d  ON d.venta_id    = v.idVenta
  JOIN producto p       ON p.idProducto  = d.producto_id
  WHERE DATE(v.fecha) = CURDATE()
  ORDER BY v.fecha DESC, v.idVenta DESC
";
$datos = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['ok'=>true,'rows'=>$datos], JSON_UNESCAPED_UNICODE);
