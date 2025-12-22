<?php
require_once '../../conexion/conexion.php';
header('Content-Type: application/json; charset=utf-8');

try {

  $tipo  = $_GET['tipo']  ?? 'mes';
  $desde = $_GET['desde'] ?? null;
  $hasta = $_GET['hasta'] ?? null;

  $where = '';

  switch ($tipo) {
    case 'dia':
      $where = "DATE(v.fecha) = CURDATE()";
      break;
    case 'semana':
      $where = "YEARWEEK(v.fecha, 1) = YEARWEEK(CURDATE(), 1)";
      break;
    case 'mes':
      $where = "MONTH(v.fecha) = MONTH(CURDATE()) 
                AND YEAR(v.fecha) = YEAR(CURDATE())";
      break;
    case 'rango':
      if (!$desde || !$hasta) throw new Exception('Fechas inválidas');
      $where = "DATE(v.fecha) BETWEEN :desde AND :hasta";
      break;
  }

  $sql = "
    SELECT
      COUNT(DISTINCT v.idVenta) AS ventas,
      COALESCE(SUM(d.cantidad * d.precio_unitario), 0) AS facturacion
    FROM ventas v
    JOIN detalle_venta d ON d.ventas_idVenta = v.idVenta
    WHERE $where
  ";

  $stmt = $conexion->prepare($sql);

  if ($tipo === 'rango') {
    $stmt->bindParam(':desde', $desde);
    $stmt->bindParam(':hasta', $hasta);
  }

  $stmt->execute();
  $data = $stmt->fetch(PDO::FETCH_ASSOC);

  $ventas = (int)$data['ventas'];
  $facturacion = (float)$data['facturacion'];
  $ticket = $ventas > 0 ? $facturacion / $ventas : 0;

  echo json_encode([
    'ok' => true,
    'facturacion' => round($facturacion, 2),
    'ventas' => $ventas,
    'ticket_promedio' => round($ticket, 2),
    'variacion' => 0
  ]);

} catch (Exception $e) {
  echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
