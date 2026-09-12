<?php
session_start();
require_once '../conexion/conexion.php';
require_once '../settings/auditoria.php';

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

$antesStmt = $conexion->prepare("SELECT * FROM producto WHERE idproducto = ?");
$antesStmt->execute([$id]);
$productoAntes = $antesStmt->fetch(PDO::FETCH_ASSOC);

try {
    $conexion->beginTransaction();

    $conexion->prepare("DELETE FROM atributos_cubiertas WHERE producto_idProducto = ?")->execute([$id]);
    $conexion->prepare("DELETE FROM movimiento_stock WHERE producto_idProducto = ?")->execute([$id]);
    $conexion->prepare("DELETE FROM stock_producto WHERE producto_idProducto = ?")->execute([$id]);
    $conexion->prepare("DELETE FROM producto WHERE idproducto = ?")->execute([$id]);

    auditoria(
        $conexion,
        'DELETE',
        'productos',
        'producto',
        $id,
        "Eliminó producto: " . ($productoAntes['nombre'] ?? "ID {$id}"),
        $productoAntes,
        null
    );

    $conexion->commit();

    echo json_encode(['ok' => true]);

} catch (PDOException $e) {
    $conexion->rollBack();
    echo json_encode(['ok' => false, 'msg' => 'Error al eliminar: ' . $e->getMessage()]);
}
