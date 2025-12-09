<?php
require_once '../conexion/conexion.php';

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

        $sqlUpd = "
            UPDATE stock_producto
            SET cantidad_actual = cantidad_actual - :cant
            WHERE producto_idProducto = :prod
        ";

        $up = $conexion->prepare($sqlUpd);
        $up->bindParam(':cant', $i['cantidad_devuelta']);
        $up->bindParam(':prod', $i['producto_id']);
        $up->execute();
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
}

echo "ok";
