<?php
require_once '../conexion/conexion.php';
session_start();

$idVenta = $_POST['idVenta'];
$producto = $_POST['producto_id'];
$cant = $_POST['cantidad'];
$motivo = $_POST['motivo'] ?? '';
$usuario = $_SESSION['id'];

// 1) Registrar devolución
$sql = "INSERT INTO devoluciones_venta (venta_id, producto_id, cantidad, usuario_id, motivo)
        VALUES (?,?,?,?,?)";
$stmt = $conexion->prepare($sql);
$stmt->execute([$idVenta, $producto, $cant, $usuario, $motivo]);

// 2) Reponer stock
$upd = $conexion->prepare("UPDATE producto SET stock = stock + ? WHERE idProducto = ?");
$upd->execute([$cant, $producto]);

// 3) Restar esa cantidad de la venta
$upd2 = $conexion->prepare("UPDATE detalle_venta SET cantidad = cantidad - ? WHERE venta_id=? AND producto_id=?");
$upd2->execute([$cant, $idVenta, $producto]);

echo "ok";
