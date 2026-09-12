<?php
require_once '../conexion/conexion.php';
require_once '../settings/auditoria.php';
session_start();

$idVenta = intval($_POST['idVenta']);

// =========================================
// 1) OBTENER ITEMS ANULADOS
// =========================================
$sql = "
    SELECT producto_idProducto AS producto_id, cantidad_devuelta
    FROM ventas_anuladas
    WHERE ventas_idVenta = :id
";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':id', $idVenta);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($items) {

    // =========================================
    // 2) RESTAR STOCK (REVERTIR LA DEVOLUCIÓN)
    // =========================================
    foreach ($items as $i) {

        $prod = $i['producto_id'];
        $cant = (int)$i['cantidad_devuelta'];

        $stockAntesStmt = $conexion->prepare("
            SELECT cantidad_actual FROM stock_producto WHERE producto_idProducto = ?
        ");
        $stockAntesStmt->execute([$prod]);
        $cantidadActualAntes = (int)$stockAntesStmt->fetchColumn();

        $sqlUpd = "
            UPDATE stock_producto
            SET cantidad_actual = cantidad_actual - :cant
            WHERE producto_idProducto = :prod
        ";

        $up = $conexion->prepare($sqlUpd);
        $up->bindParam(':cant', $cant);
        $up->bindParam(':prod', $prod);
        $up->execute();

        auditoria(
            $conexion,
            'UPDATE',
            'INVENTARIO',
            'stock_producto',
            $prod,
            "Reactivó venta Nº {$idVenta}: egreso de {$cant} unidad(es) (revierte anulación)",
            ['cantidad_actual' => $cantidadActualAntes],
            ['cantidad_actual' => $cantidadActualAntes - $cant]
        );
    }

    // =========================================
    // 3) ELIMINAR REGISTROS DE ANULACIÓN
    // =========================================
    $del = $conexion->prepare("
        DELETE FROM ventas_anuladas
        WHERE ventas_idVenta = :id
    ");

    $del->bindParam(':id', $idVenta);
    $del->execute();

    auditoria(
        $conexion,
        'DELETE',
        'ventas',
        'ventas_anuladas',
        $idVenta,
        "Reactivó la venta Nº {$idVenta} (eliminó sus anulaciones)",
        ['items' => $items],
        null
    );
}

echo "ok";
