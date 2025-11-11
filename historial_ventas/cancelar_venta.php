<?php
require_once '../conexion/conexion.php';
session_start();

$idVenta = $_POST['idVenta'];
$motivo = $_POST['motivo'] ?? '';
$usuario = $_SESSION['id'];

// 1) Obtener los productos de la venta
$sql = "SELECT producto_id, cantidad FROM detalle_venta WHERE venta_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$idVenta]);
$items = $stmt->fetchAll();

// 2) Reponer stock
foreach ($items as $item) {
    $upd = $conexion->prepare("UPDATE producto SET stock = stock + ? WHERE idProducto = ?");
    $upd->execute([$item['cantidad'], $item['producto_id']]);
}

// 3) Marcar venta como anulada
$sql = "UPDATE ventas SET estado='Anulada', motivo_anulacion=?, fecha_anulacion=NOW(), usuario_anulo=? WHERE idVenta=?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$motivo, $usuario, $idVenta]);

echo "ok";
