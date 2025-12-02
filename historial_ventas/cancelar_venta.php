<?php
require_once '../conexion/conexion.php';
session_start();

$idVenta = intval($_POST['idVenta']);
$motivo = trim($_POST['motivo']);
$usuario = $_SESSION['usuario_id'] ?? 0; // Ajustalo según tu login

// =========================================
// 1) OBTENER DETALLES DE LA VENTA
// =========================================
$sql = "SELECT producto_id, cantidad 
        FROM detalle_venta 
        WHERE venta_id = :id";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':id', $idVenta);
$stmt->execute();
$items = $stmt->fetchAll();

if (!$items) {
    echo "empty";
    exit;
}

// =========================================
// 2) POR CADA PRODUCTO → SUMAR STOCK
// =========================================
foreach ($items as $item) {

    $prod = $item['producto_id'];
    $cant = $item['cantidad'];

    // stock_producto
    $sql = "UPDATE stock_producto 
            SET cantidad_actual = cantidad_actual + :cant
            WHERE producto_idProducto = :prod";
    $up = $conexion->prepare($sql);
    $up->bindParam(':cant', $cant);
    $up->bindParam(':prod', $prod);
    $up->execute();

    // guardar registro en ventas_anuladas
    $ins = $conexion->prepare("
        INSERT INTO ventas_anuladas
        (venta_id, producto_id, cantidad_devuelta, motivo, usuario_anulo, fecha)
        VALUES (:venta, :prod, :cant, :motivo, :user, NOW())
    ");

    $ins->bindParam(':venta', $idVenta);
    $ins->bindParam(':prod', $prod);
    $ins->bindParam(':cant', $cant);
    $ins->bindParam(':motivo', $motivo);
    $ins->bindParam(':user', $usuario);
    $ins->execute();
}

echo "ok";
