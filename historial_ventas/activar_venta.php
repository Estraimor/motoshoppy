<?php
require_once '../conexion/conexion.php';

$idVenta = intval($_POST['idVenta']);

// obtener anulaciones
$sql = "SELECT producto_id, cantidad_devuelta 
        FROM ventas_anuladas 
        WHERE venta_id = :id";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':id', $idVenta);
$stmt->execute();
$items = $stmt->fetchAll();

foreach ($items as $i) {

    $sql = "UPDATE stock_producto
            SET cantidad_actual = cantidad_actual - :cant
            WHERE producto_idProducto = :prod";
    $up = $conexion->prepare($sql);
    $up->bindParam(':cant', $i['cantidad_devuelta']);
    $up->bindParam(':prod', $i['producto_id']);
    $up->execute();
}

$del = $conexion->prepare("DELETE FROM ventas_anuladas WHERE venta_id = :id");
$del->bindParam(':id', $idVenta);
$del->execute();

echo "ok";
