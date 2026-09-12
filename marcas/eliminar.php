<?php
session_start();
require_once '../conexion/conexion.php';
require_once '../settings/auditoria.php';

header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
    exit;
}

try {
    $antesStmt = $conexion->prepare("SELECT nombre_marca FROM marcas WHERE idmarcas = ?");
    $antesStmt->execute([$id]);
    $nombre = $antesStmt->fetchColumn();

    $conexion->prepare("UPDATE marcas SET estado = 0 WHERE idmarcas = ?")->execute([$id]);

    auditoria($conexion, 'UPDATE', 'marcas', 'marcas', $id,
        "Desactivó marca: " . ($nombre ?: "ID {$id}"), ['estado' => 1], ['estado' => 0]);

    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error al desactivar: ' . $e->getMessage()]);
}
