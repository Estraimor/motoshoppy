<?php
session_start();
require_once '../../conexion/conexion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idusuario'])) {
  echo json_encode([
    'ok' => false,
    'msg' => 'Sesión expirada'
  ]);
  exit;
}
try {

  $tipo  = $_GET['tipo'] ?? 'mes';
  $desde = $_GET['desde'] ?? null;
  $hasta = $_GET['hasta'] ?? null;

  $where  = '';
  $group  = '';
  $label  = '';

  switch ($tipo) {

    case 'dia':
      $where = "DATE(v.fecha) = CURDATE()";
      $group = "HOUR(v.fecha)";
      $label = "DATE_FORMAT(v.fecha, '%H:00')";
      break;

    case 'semana':
      $where = "YEARWEEK(v.fecha, 1) = YEARWEEK(CURDATE(), 1)";
      $group = "DATE(v.fecha)";
      $label = "DATE_FORMAT(v.fecha, '%d/%m')";
      break;

    case 'mes':
      $where = "MONTH(v.fecha) = MONTH(CURDATE())
                AND YEAR(v.fecha) = YEAR(CURDATE())";
      $group = "DATE(v.fecha)";
      $label = "DATE_FORMAT(v.fecha, '%d/%m')";
      break;

    case 'rango':
      if (!$desde || !$hasta) {
        throw new Exception('Fechas inválidas');
      }
      $where = "DATE(v.fecha) BETWEEN :desde AND :hasta";
      $group = "DATE(v.fecha)";
      $label = "DATE_FORMAT(v.fecha, '%d/%m')";
      break;

    default:
      throw new Exception('Tipo de filtro inválido');
  }

  $sql = "
    SELECT
      $label AS label,
      SUM(d.cantidad * d.precio_unitario) AS total,
      COUNT(DISTINCT v.idVenta) AS ventas
    FROM ventas v
    JOIN detalle_venta d ON d.ventas_idVenta = v.idVenta
    WHERE $where
    GROUP BY $group
    ORDER BY MIN(v.fecha)
  ";

  $stmt = $conexion->prepare($sql);

  if ($tipo === 'rango') {
    $stmt->bindParam(':desde', $desde);
    $stmt->bindParam(':hasta', $hasta);
  }

  $stmt->execute();
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $labels = [];
  $totales = [];
  $cantidades = [];

  foreach ($rows as $r) {
    $labels[]     = $r['label'];
    $totales[]    = (float)$r['total'];
    $cantidades[] = (int)$r['ventas'];
  }

  echo json_encode([
    'ok' => true,
    'labels' => $labels,
    'totales' => $totales,
    'ventas' => $cantidades
  ]);

} catch (Exception $e) {
  echo json_encode([
    'ok' => false,
    'msg' => $e->getMessage()
  ]);
}
