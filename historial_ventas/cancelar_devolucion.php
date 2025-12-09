<?php
require_once '../conexion/conexion.php';

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
$sql = "UPDATE stock_producto 
        SET cantidad_actual = cantidad_actual + :cant
        WHERE producto_idProducto = :prod";

$stmt = $conexion->prepare($sql);
$stmt->bindParam(':cant', $cant, PDO::PARAM_INT);
$stmt->bindParam(':prod', $producto, PDO::PARAM_INT);
$stmt->execute();

// ===================================================
// 3) BORRAR REGISTRO DE DEVOLUCIÓN
// ===================================================
$sql = "DELETE FROM devoluciones_venta 
        WHERE idDevolucion = :idDev";

$stmt = $conexion->prepare($sql);
$stmt->bindParam(':idDev', $idDev, PDO::PARAM_INT);
$stmt->execute();

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
