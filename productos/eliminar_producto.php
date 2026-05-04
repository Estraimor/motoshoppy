<?php
require_once '../conexion/conexion.php';

header('Content-Type: application/json');

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
    exit;
}

// Verificar si el producto tiene ventas
$stmt = $conexion->prepare("
    SELECT COUNT(*) FROM detalle_venta WHERE producto_idProducto = ?
");
$stmt->execute([$id]);
$cantVentas = (int)$stmt->fetchColumn();

if ($cantVentas > 0) {
    echo json_encode([
        'ok'     => false,
        'ventas' => $cantVentas,
        'msg'    => "Este producto tiene {$cantVentas} venta(s) registrada(s) y no puede eliminarse."
    ]);
    exit;
}

try {
    $conexion->beginTransaction();

    $conexion->prepare("DELETE FROM atributos_cubiertas WHERE producto_idProducto = ?")->execute([$id]);
    $conexion->prepare("DELETE FROM movimiento_stock WHERE producto_idProducto = ?")->execute([$id]);
    $conexion->prepare("DELETE FROM stock_producto WHERE producto_idProducto = ?")->execute([$id]);
    $conexion->prepare("DELETE FROM producto WHERE idproducto = ?")->execute([$id]);

    $conexion->commit();

    echo json_encode(['ok' => true]);

} catch (PDOException $e) {
    $conexion->rollBack();
    echo json_encode(['ok' => false, 'msg' => 'Error al eliminar: ' . $e->getMessage()]);
}
