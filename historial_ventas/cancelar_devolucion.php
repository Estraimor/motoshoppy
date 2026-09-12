<?php
require_once '../conexion/conexion.php';
require_once '../settings/auditoria.php';
session_start();

$idDev   = intval($_POST['idDevolucion']);
$idVenta = intval($_POST['idVenta']);
$producto = intval($_POST['producto_id']);

// ===================================================
// 1) OBTENER CANTIDAD DEVUELTA
// ===================================================
$sql = "SELECT cantidad
        FROM devoluciones_venta
        WHERE idDevolucion = :idDev";

$stmt = $conexion->prepare($sql);
$stmt->bindParam(':idDev', $idDev, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "error_no_encontrado";
    exit;
}

$cant = intval($row['cantidad']);

// ===================================================
// 2) SUMAR STOCK REAL
// ===================================================
$stockAntesStmt = $conexion->prepare("
    SELECT cantidad_actual FROM stock_producto WHERE producto_idProducto = ?
");
$stockAntesStmt->execute([$producto]);
$cantidadActualAntes = (int)$stockAntesStmt->fetchColumn();

$sql = "UPDATE stock_producto
        SET cantidad_actual = cantidad_actual + :cant
        WHERE producto_idProducto = :prod";

$stmt = $conexion->prepare($sql);
$stmt->bindParam(':cant', $cant, PDO::PARAM_INT);
$stmt->bindParam(':prod', $producto, PDO::PARAM_INT);
$stmt->execute();

auditoria(
    $conexion,
    'UPDATE',
    'INVENTARIO',
    'stock_producto',
    $producto,
    "Canceló devolución de venta Nº {$idVenta}: egreso de {$cant} unidad(es) (revierte devolución)",
    ['cantidad_actual' => $cantidadActualAntes],
    ['cantidad_actual' => $cantidadActualAntes + $cant]
);

// ===================================================
// 3) BORRAR REGISTRO DE DEVOLUCIÓN
// ===================================================
$sql = "DELETE FROM devoluciones_venta
        WHERE idDevolucion = :idDev";

$stmt = $conexion->prepare($sql);
$stmt->bindParam(':idDev', $idDev, PDO::PARAM_INT);
$stmt->execute();

auditoria(
    $conexion,
    'DELETE',
    'ventas',
    'devoluciones_venta',
    $idDev,
    "Canceló la devolución del producto {$producto} de la venta Nº {$idVenta}",
    ['ventas_idVenta' => $idVenta, 'producto_idProducto' => $producto, 'cantidad' => $cant],
    null
);

// ===================================================
// 4) MARCAR EL DETALLE COMO NO DEVUELTO
// ===================================================
$sql = "UPDATE detalle_venta 
        SET devuelto = 0
        WHERE ventas_idVenta = :idVenta
          AND producto_idProducto = :prod";

$stmt = $conexion->prepare($sql);
$stmt->bindParam(':idVenta', $idVenta, PDO::PARAM_INT);
$stmt->bindParam(':prod', $producto, PDO::PARAM_INT);
$stmt->execute();

echo "ok";
