<?php
require_once '../../conexion/conexion.php';
header('Content-Type: application/json; charset=utf-8');

try {

  // === MES ACTUAL ===
  $sqlActual = "
    SELECT
      COUNT(DISTINCT v.idVenta) AS ventas,
      COALESCE(SUM(d.cantidad * d.precio_unitario), 0) AS facturacion
    FROM ventas v
    JOIN detalle_venta d ON d.ventas_idVenta = v.idVenta
    WHERE MONTH(v.fecha) = MONTH(CURDATE())
      AND YEAR(v.fecha) = YEAR(CURDATE())
  ";

  $actual = $conexion->query($sqlActual)->fetch(PDO::FETCH_ASSOC);

  $ventasMes = (int)$actual['ventas'];
  $facturacionMes = (float)$actual['facturacion'];
  $ticketPromedio = $ventasMes > 0 ? $facturacionMes / $ventasMes : 0;

  // === MES ANTERIOR ===
  $sqlAnterior = "
    SELECT
      COALESCE(SUM(d.cantidad * d.precio_unitario), 0) AS facturacion
    FROM ventas v
    JOIN detalle_venta d ON d.ventas_idVenta = v.idVenta
    WHERE MONTH(v.fecha) = MONTH(CURDATE() - INTERVAL 1 MONTH)
      AND YEAR(v.fecha) = YEAR(CURDATE() - INTERVAL 1 MONTH)
  ";

  $anterior = $conexion->query($sqlAnterior)->fetch(PDO::FETCH_ASSOC);
  $facturacionAnterior = (float)$anterior['facturacion'];

  // === VARIACIÓN ===
  if ($facturacionAnterior > 0) {
    $variacion = (($facturacionMes - $facturacionAnterior) / $facturacionAnterior) * 100;
  } else {
    $variacion = 0;
  }

  echo json_encode([
    'ok' => true,
    'facturacion_mes' => round($facturacionMes, 2),
    'ventas_mes' => $ventasMes,
    'ticket_promedio' => round($ticketPromedio, 2),
    'variacion' => round($variacion, 2)
  ]);

} catch (Exception $e) {
  echo json_encode([
    'ok' => false,
    'msg' => $e->getMessage()
  ]);
}
