<?php
session_start();

require '../../conexion/conexion.php';
require '../../settings/auditoria.php';

header('Content-Type: application/json');

if (!isset($_POST['producto_id'], $_POST['minimo'], $_POST['deposito'], $_POST['exhibido'])) {
    echo json_encode(["status"=>"error","msg"=>"Datos incompletos"]);
    exit;
}

$producto_id = intval($_POST['producto_id']);
$minimo      = intval($_POST['minimo']);
$deposito    = intval($_POST['deposito']);
$exhibido    = intval($_POST['exhibido']);

if ($minimo <= 0) {
    echo json_encode(["status"=>"error","msg"=>"Stock mínimo inválido"]);
    exit;
}

try {

    $conexion->beginTransaction();

    /* ==========================================
       VERIFICAR SI YA EXISTE REGISTRO
    ========================================== */

    $stmt = $conexion->prepare("
        SELECT *
        FROM stock_producto
        WHERE producto_idProducto = ?
        FOR UPDATE
    ");
    $stmt->execute([$producto_id]);
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($stock) {

        // 📌 UPDATE
        $upd = $conexion->prepare("
            UPDATE stock_producto
            SET stock_minimo = ?,
                cantidad_actual = ?,
                cantidad_exhibida = ?
            WHERE producto_idProducto = ?
        ");

        $upd->execute([
            $minimo,
            $deposito,
            $exhibido,
            $producto_id
        ]);

        auditoria(
            $conexion,
            'UPDATE',
            'INVENTARIO',
            'stock_producto',
            $producto_id,
            'Configuración inicial de stock (update)',
            [
                'stock_minimo'       => $stock['stock_minimo'],
                'cantidad_actual'    => $stock['cantidad_actual'],
                'cantidad_exhibida'  => $stock['cantidad_exhibida']
            ],
            [
                'stock_minimo'       => $minimo,
                'cantidad_actual'    => $deposito,
                'cantidad_exhibida'  => $exhibido
            ]
        );

    } else {

        // 📌 INSERT
        $ins = $conexion->prepare("
            INSERT INTO stock_producto
            (producto_idProducto, stock_minimo, cantidad_actual, cantidad_exhibida)
            VALUES (?,?,?,?)
        ");

        $ins->execute([
            $producto_id,
            $minimo,
            $deposito,
            $exhibido
        ]);

        auditoria(
            $conexion,
            'INSERT',
            'INVENTARIO',
            'stock_producto',
            $producto_id,
            'Configuración inicial de stock',
            null,
            [
                'stock_minimo'       => $minimo,
                'cantidad_actual'    => $deposito,
                'cantidad_exhibida'  => $exhibido
            ]
        );
    }

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
