<?php
require_once '../conexion/conexion.php';

header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
    exit;
}

try {
    $conexion->prepare("UPDATE marcas SET estado = 1 WHERE idmarcas = ?")->execute([$id]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error al activar: ' . $e->getMessage()]);
}
