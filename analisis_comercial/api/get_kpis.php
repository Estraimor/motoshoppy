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

  $tipo  = $_GET['tipo']  ?? 'mes';
  $modo  = $_GET['modo']  ?? 'tiempo'; // tiempo | metodo
  $desde = $_GET['desde'] ?? null;
  $hasta = $_GET['hasta'] ?? null;

  /* =========================
     CONDICIÓN DE FECHAS
  ========================= */
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
      if (!$desde || !$hasta) {
        throw new Exception('Fechas inválidas');
      }
      $where = "DATE(v.fecha) BETWEEN :desde AND :hasta";
      break;

    default:
      throw new Exception('Tipo inválido');
  }

  /* ===================================================
     🟢 MODO MÉTODOS DE PAGO
  =================================================== */
  if ($modo === 'metodo') {

    $sql = "
      SELECT
        mp.nombre AS metodo,
        COALESCE(SUM(v.total), 0) AS facturacion
      FROM metodo_pago mp
      LEFT JOIN ventas v
        ON v.metodo_pago_idmetodo_pago = mp.idmetodo_pago
       AND $where
      GROUP BY mp.idmetodo_pago
      ORDER BY facturacion DESC
    ";

    $stmt = $conexion->prepare($sql);

    if ($tipo === 'rango') {
      $stmt->bindParam(':desde', $desde);
      $stmt->bindParam(':hasta', $hasta);
    }

    $stmt->execute();

    echo json_encode([
      'ok'   => true,
      'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
    exit;
  }

  /* ===================================================
     🔵 MODO TIEMPO (GRÁFICOS + KPIs)
  =================================================== */

  switch ($tipo) {
    case 'dia':
      $group = "HOUR(v.fecha)";
      $label = "DATE_FORMAT(v.fecha, '%H:00')";
      break;

    default:
      $group = "DATE(v.fecha)";
      $label = "DATE_FORMAT(v.fecha, '%d/%m')";
  }

  $sql = "
    SELECT
      $label AS label,
      SUM(v.total) AS total,
      COUNT(v.idVenta) AS ventas
    FROM ventas v
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

  /* =========================
     RESUMEN PARA KPIs
  ========================= */
  $facturacionTotal = 0;
  $ventasTotal = 0;

  foreach ($rows as $r) {
    $facturacionTotal += (float)$r['total'];
    $ventasTotal      += (int)$r['ventas'];
  }

  echo json_encode([
    'ok'          => true,

    // 👉 KPIs
    'facturacion' => $facturacionTotal,
    'ventas'      => $ventasTotal,

    // 👉 Gráficos
    'labels'      => array_column($rows, 'label'),
    'totales'     => array_map('floatval', array_column($rows, 'total')),
    'ventas_arr'  => array_map('intval', array_column($rows, 'ventas'))
  ]);

} catch (Exception $e) {

  echo json_encode([
    'ok'  => false,
    'msg' => $e->getMessage()
  ]);
}
