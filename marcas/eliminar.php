<?php
require_once '../conexion/conexion.php';

header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
    exit;
}

// Verificar si la marca tiene productos asociados
$stmt = $conexion->prepare("SELECT COUNT(*) FROM producto WHERE marcas_idmarcas = ?");
$stmt->execute([$id]);
$cantProductos = (int)$stmt->fetchColumn();

if ($cantProductos > 0) {
    echo json_encode([
        'ok'        => false,
        'productos' => $cantProductos,
        'msg'       => "Esta marca tiene {$cantProductos} producto(s) asociado(s) y no puede eliminarse."
    ]);
    exit;
}

try {
    $conexion->prepare("DELETE FROM marcas WHERE idmarcas = ?")->execute([$id]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error al eliminar: ' . $e->getMessage()]);
}
