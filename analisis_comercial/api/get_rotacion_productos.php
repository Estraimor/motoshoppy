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
      p.nombre AS producto,
      SUM(d.cantidad) AS unidades
    FROM ventas v
    JOIN detalle_venta d ON d.ventas_idVenta = v.idVenta
    JOIN productos p ON p.idProducto = d.producto_id
    WHERE $where
    GROUP BY p.idProducto
  ";

  $stmt = $conexion->prepare($sql);

  if ($tipo === 'rango') {
    $stmt->bindParam(':desde', $desde);
    $stmt->bindParam(':hasta', $hasta);
  }

  $stmt->execute();
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (!$rows) {
    echo json_encode(['ok' => true, 'data' => []]);
    exit;
  }

  $total = array_sum(array_column($rows, 'unidades'));
  $promedio = $total / count($rows);

  $data = array_map(fn($r) => [
    'producto' => $r['producto'],
    'unidades' => (int)$r['unidades'],
    'rotacion' => $r['unidades'] >= $promedio ? 'Alta' : 'Baja'
  ], $rows);

  echo json_encode(['ok' => true, 'data' => $data]);

} catch (Exception $e) {
  echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
