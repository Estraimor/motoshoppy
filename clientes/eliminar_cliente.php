<?php
require_once '../conexion/conexion.php';

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

$conexion->prepare("DELETE FROM clientes WHERE idCliente = ?")->execute([$id]);

header('Location: index.php?msg=eliminado');
exit;
