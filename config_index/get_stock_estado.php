<?php
require_once '../conexion/conexion.php';
header('Content-Type: application/json; charset=UTF-8');

/*
   CLASIFICACIONES:
   - SIN STOCK → cantidad_actual = 0
   - BAJO STOCK → 1..stock_minimo
   - ÓPTIMO → > stock_minimo

   Y AHORA TAMBIÉN PARA EXHIBICIÓN:
   - SIN EXHIBICIÓN → cantidad_exhibida = 0
   - BAJO EXHIBICIÓN → 1..stock_minimo
   - ÓPTIMO EXHIBICIÓN → > stock_minimo
*/

$sql = "
    SELECT 
        -- STOCK GENERAL
        SUM(CASE WHEN sp.cantidad_actual = 0 THEN 1 ELSE 0 END) AS sin_general,
        SUM(CASE WHEN sp.cantidad_actual > 0 AND sp.cantidad_actual <= sp.stock_minimo THEN 1 ELSE 0 END) AS bajo_general,
        SUM(CASE WHEN sp.cantidad_actual > sp.stock_minimo THEN 1 ELSE 0 END) AS optimo_general,

        -- STOCK EN EXHIBICIÓN
        SUM(CASE WHEN sp.cantidad_exhibida = 0 THEN 1 ELSE 0 END) AS sin_exhibida,
        SUM(CASE WHEN sp.cantidad_exhibida > 0 AND sp.cantidad_exhibida <= sp.stock_minimo THEN 1 ELSE 0 END) AS bajo_exhibida,
        SUM(CASE WHEN sp.cantidad_exhibida > sp.stock_minimo THEN 1 ELSE 0 END) AS optimo_exhibida
    FROM stock_producto sp
";

$rows = $conexion->query($sql)->fetch(PDO::FETCH_ASSOC);

// =======================
// ALERTAS
// =======================

$alertas = [];

if ($rows['sin_general'] > 0) {
    $alertas[] = "Hay productos SIN STOCK general.";
}

if ($rows['bajo_general'] > 0) {
    $alertas[] = "Hay productos con STOCK BAJO general.";
}

if ($rows['sin_exhibida'] > 0) {
    $alertas[] = "Hay productos SIN STOCK en exhibición.";
}

if ($rows['bajo_exhibida'] > 0) {
    $alertas[] = "Hay productos con STOCK BAJO en exhibición.";
}

// Si no hay alertas → mostrar OK
if (empty($alertas)) {
    $alertas[] = "Todos los productos tienen stock suficiente.";
}

// =======================
// RESPUESTA JSON
// =======================
echo json_encode([
    'general' => [
        'optimo' => (int)$rows['optimo_general'],
        'bajo'   => (int)$rows['bajo_general'],
        'sin'    => (int)$rows['sin_general']
    ],
    'exhibicion' => [
        'optimo' => (int)$rows['optimo_exhibida'],
        'bajo'   => (int)$rows['bajo_exhibida'],
        'sin'    => (int)$rows['sin_exhibida']
    ],
    'alertas' => $alertas
]);
