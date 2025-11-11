<?php
require_once '../conexion/conexion.php';
header('Content-Type: application/json; charset=utf-8');

try {
  $sql = "
    SELECT 
      v.idVenta,
      v.fecha,
      u.nombre AS vendedor,
      CONCAT(c.apellido, ' ', c.nombre) AS cliente,
      p.nombre AS producto,
      d.cantidad,
      d.precio_unitario,
      (d.cantidad * d.precio_unitario) AS subtotal
    FROM ventas v
    LEFT JOIN usuario u ON u.idusuario = v.usuario_id
    LEFT JOIN clientes c ON c.idCliente = v.cliente_id
    INNER JOIN detalle_venta d ON d.venta_id = v.idVenta
    INNER JOIN producto p ON p.idProducto = d.producto_id
    WHERE DATE(v.fecha) = CURDATE()
    ORDER BY v.fecha DESC
  ";

  $stmt = $conexion->query($sql);
  $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'ok' => true,
    'ventas' => $ventas
  ]);

} catch (Exception $e) {
  echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
