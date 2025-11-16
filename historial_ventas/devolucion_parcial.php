<?php
require_once '../conexion/conexion.php';
session_start();

// Datos recibidos
$idVenta = $_POST['idVenta'];
$items = json_decode($_POST['items'], true); // array con productos y cantidades
$motivo = $_POST['motivo'] ?? '';
$usuario = $_SESSION['idusuario'];

// ============================================
// 1) Procesar cada producto seleccionado
// ============================================
foreach ($items as $it) {

    $productoId = $it['producto_id'];
    $cantidad   = $it['cantidad'];

    // 1) Registrar devolución
    $sql = "INSERT INTO devoluciones_venta (venta_id, producto_id, cantidad, usuario_id, motivo)
            VALUES (?,?,?,?,?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$idVenta, $productoId, $cantidad, $usuario, $motivo]);

    // 2) Reponer stock en stock_producto
    $upd = $conexion->prepare("
        UPDATE stock_producto 
        SET cantidad_actual = cantidad_actual + ?, 
            cantidad_exhibida = cantidad_exhibida + ?
        WHERE producto_idProducto = ?
    ");
    $upd->execute([$cantidad, $cantidad, $productoId]);

    // 3) Actualizar detalle de venta
    $upd2 = $conexion->prepare("
        UPDATE detalle_venta 
        SET cantidad = cantidad - ?
        WHERE venta_id = ? AND producto_id = ?
    ");
    $upd2->execute([$cantidad, $idVenta, $productoId]);
}

// ============================================
// 2) ¿Quedó la venta en 0?
// ============================================
$sqlCheck = $conexion->prepare("
    SELECT SUM(cantidad) AS totalRestante
    FROM detalle_venta
    WHERE venta_id = ?
");
$sqlCheck->execute([$idVenta]);
$totalRestante = $sqlCheck->fetchColumn();

// Si quedó en cero
if ($totalRestante == 0) {
    echo "completa";
    exit;
}

// Sino todo OK
echo "ok";
