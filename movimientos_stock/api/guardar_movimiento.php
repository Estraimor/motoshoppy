<?php
session_start();

require '../../conexion/conexion.php';
require '../../settings/auditoria.php';

header('Content-Type: application/json');

if (!isset($_SESSION['idusuario'])) {
    echo json_encode(["status"=>"error","msg"=>"Sesión expirada"]);
    exit;
}

/* ==============================
   VALIDACIÓN
============================== */

if (!isset($_POST['producto_id'], $_POST['tipo'], $_POST['cantidad'])) {
    echo json_encode(["status"=>"error","msg"=>"Datos incompletos"]);
    exit;
}

$producto_id = intval($_POST['producto_id']);
$tipo        = $_POST['tipo'];
$cantidad    = intval($_POST['cantidad']);
$usuario_id  = $_SESSION['idusuario'];

if ($cantidad <= 0) {
    echo json_encode(["status"=>"error","msg"=>"Cantidad inválida"]);
    exit;
}

try {

    $conexion->beginTransaction();

    /* ==============================
       TRAER STOCK ACTUAL
    ============================== */

    $stmt = $conexion->prepare("
        SELECT cantidad_actual, cantidad_exhibida
        FROM stock_producto
        WHERE producto_idProducto = ?
        FOR UPDATE
    ");
    $stmt->execute([$producto_id]);
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$stock) {
        throw new Exception("Stock no encontrado");
    }

    $deposito_original   = (int)$stock['cantidad_actual'];
    $exhibicion_original = (int)$stock['cantidad_exhibida'];

    $deposito   = $deposito_original;
    $exhibicion = $exhibicion_original;

    /* ==============================
       LÓGICA
    ============================== */

    if ($tipo === 'a_exhibido') {

        if ($deposito < $cantidad) {
            throw new Exception("No hay suficiente stock en depósito");
        }

        $deposito   -= $cantidad;
        $exhibicion += $cantidad;

    } elseif ($tipo === 'a_deposito') {

        if ($exhibicion < $cantidad) {
            throw new Exception("No hay suficiente stock en exhibición");
        }

        $exhibicion -= $cantidad;
        $deposito   += $cantidad;

    } else {
        throw new Exception("Tipo inválido");
    }

    /* ==============================
       UPDATE STOCK
    ============================== */

    $upd = $conexion->prepare("
        UPDATE stock_producto
        SET cantidad_actual = ?, cantidad_exhibida = ?
        WHERE producto_idProducto = ?
    ");
    $upd->execute([$deposito, $exhibicion, $producto_id]);

    /* ==============================
       INSERT MOVIMIENTO
    ============================== */

    $ins = $conexion->prepare("
        INSERT INTO movimiento_stock
        (producto_idProducto, cantidad, tipo, fecha, usuario_id)
        VALUES (?,?,?,?,?)
    ");

    $ins->execute([
        $producto_id,
        $cantidad,
        $tipo,
        date('Y-m-d H:i:s'),
        $usuario_id
    ]);

    $movimiento_id = $conexion->lastInsertId();

    /* ==============================
       AUDITORÍA
    ============================== */

    auditoria(
        $conexion,
        'UPDATE',
        'INVENTARIO',
        'stock_producto',
        $producto_id,
        "Movimiento de stock ({$tipo}) por {$cantidad} unidades",
        [
            'cantidad_actual'   => $deposito_original,
            'cantidad_exhibida' => $exhibicion_original
        ],
        [
            'cantidad_actual'   => $deposito,
            'cantidad_exhibida' => $exhibicion
        ]
    );

    $conexion->commit();

    echo json_encode(["status"=>"ok"]);

} catch (Exception $e) {

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    echo json_encode([
        "status"=>"error",
        "msg"=>$e->getMessage()
    ]);
}
