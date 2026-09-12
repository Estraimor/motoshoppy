<?php
session_start();
require_once '../conexion/conexion.php';
require_once '../settings/auditoria.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php?msg=error');
    exit;
}

$stmt = $conexion->prepare("SELECT COUNT(*) FROM ventas WHERE clientes_idCliente = ?");
$stmt->execute([$id]);
if ((int)$stmt->fetchColumn() > 0) {
    header('Location: index.php?msg=error');
    exit;
}

$antesStmt = $conexion->prepare("SELECT nombre, apellido, dni, celular, email FROM clientes WHERE idCliente = ?");
$antesStmt->execute([$id]);
$antes = $antesStmt->fetch(PDO::FETCH_ASSOC);

$conexion->prepare("DELETE FROM clientes WHERE idCliente = ?")->execute([$id]);

auditoria(
    $conexion,
    'DELETE',
    'clientes',
    'clientes',
    $id,
    "Eliminó cliente: " . ($antes['nombre'] ?? "ID {$id}") . " " . ($antes['apellido'] ?? ''),
    $antes,
    null
);

header('Location: index.php?msg=eliminado');
exit;
