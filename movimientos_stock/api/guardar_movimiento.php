<?php
session_start();
require '../../conexion/conexion.php';

if (!isset($_POST['producto_id'], $_POST['tipo'], $_POST['cantidad'])) {
    echo json_encode(["status"=>"error","msg"=>"Datos incompletos"]);
    exit;
}

$producto_id = intval($_POST['producto_id']);
$tipo        = $_POST['tipo']; // a_exhibido o a_deposito
$cantidad    = intval($_POST['cantidad']);

if ($cantidad <= 0) {
    echo json_encode(["status"=>"error","msg"=>"Cantidad inválida"]);
    exit;
}

/* ==============================
   TRAER STOCK ACTUAL
============================== */
$stmt = $conexion->prepare("
    SELECT cantidad_actual, cantidad_exhibida
    FROM stock_producto
    WHERE producto_idProducto = ?
");
$stmt->execute([$producto_id]);
$stock = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stock) {
    echo json_encode(["status"=>"error","msg"=>"Stock no encontrado"]);
    exit;
}

$deposito   = (int)$stock['cantidad_actual'];
$exhibicion = (int)$stock['cantidad_exhibida'];

/* ==============================
   LÓGICA SEGÚN TIPO
============================== */

if ($tipo === 'a_exhibido') {

    if ($deposito < $cantidad) {
        echo json_encode(["status"=>"error","msg"=>"No hay suficiente stock en depósito"]);
        exit;
    }

    $deposito   -= $cantidad;
    $exhibicion += $cantidad;

} elseif ($tipo === 'a_deposito') {

    if ($exhibicion < $cantidad) {
        echo json_encode(["status"=>"error","msg"=>"No hay suficiente stock en exhibición"]);
        exit;
    }

    $exhibicion -= $cantidad;
    $deposito   += $cantidad;

} else {
    echo json_encode(["status"=>"error","msg"=>"Tipo inválido"]);
    exit;
}

/* ==============================
   ACTUALIZAR STOCK
============================== */
$upd = $conexion->prepare("
    UPDATE stock_producto
    SET cantidad_actual = ?, cantidad_exhibida = ?
    WHERE producto_idProducto = ?
");
$upd->execute([$deposito, $exhibicion, $producto_id]);

/* ==============================
   GUARDAR MOVIMIENTO
============================== */
$ins = $conexion->prepare("
    INSERT INTO movimiento_stock
    (producto_idProducto, cantidad, tipo, fecha)
    VALUES (?,?,?,NOW())
");
$ins->execute([$producto_id, $cantidad, $tipo]);

echo json_encode(["status"=>"ok"]);
