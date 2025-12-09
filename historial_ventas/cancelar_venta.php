<?php
require_once '../conexion/conexion.php';
session_start();

$idVenta = intval($_POST['idVenta']);
$motivo = trim($_POST['motivo']);
$usuario = $_SESSION['idusuario'] ?? 0; // Tu sistema usa idusuario

// =========================================
// 1) OBTENER DETALLES DE LA VENTA
// =========================================
$sql = "
    SELECT producto_idProducto AS producto_id, cantidad
    FROM detalle_venta
    WHERE ventas_idVenta = :id
";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':id', $idVenta);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$items) {
    echo "empty";
    exit;
}

// =========================================
// 2) ANULAR PRODUCTOS Y SUMAR STOCK
// =========================================
foreach ($items as $item) {

    $prod = $item['producto_id'];
    $cant = intval($item['cantidad']);

    // ----------------------------------------------------------
    // A) DEVOLVER STOCK AL DEPÓSITO (NO A EXHIBICIÓN)
    // ----------------------------------------------------------
    $sql = "
        UPDATE stock_producto 
        SET cantidad_actual = cantidad_actual + :cant
        WHERE producto_idProducto = :prod
    ";
    $up = $conexion->prepare($sql);
    $up->bindParam(':cant', $cant);
    $up->bindParam(':prod', $prod);
    $up->execute();

    // ----------------------------------------------------------
    // B) REGISTRAR ANULACIÓN
    // ----------------------------------------------------------
    $ins = $conexion->prepare("
        INSERT INTO ventas_anuladas
        (ventas_idVenta, producto_idProducto, cantidad_devuelta, motivo, usuario_idusuario, fecha)
        VALUES (:venta, :prod, :cant, :motivo, :user, NOW())
    ");

    $ins->bindParam(':venta', $idVenta);
    $ins->bindParam(':prod',  $prod);
    $ins->bindParam(':cant',  $cant);
    $ins->bindParam(':motivo', $motivo);
    $ins->bindParam(':user', $usuario);
    $ins->execute();
}

echo "ok";
