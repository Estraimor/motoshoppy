<?php
session_start();
require '../conexion/conexion.php';
require '../settings/auditoria.php';

$id = intval($_POST['id'] ?? 0);
$estado = intval($_POST['estado'] ?? 1);

if ($id <= 0) {
    echo "Error";
    exit;
}

$antesStmt = $conexion->prepare("SELECT estado, nombre FROM producto WHERE idproducto = ?");
$antesStmt->execute([$id]);
$antes = $antesStmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conexion->prepare("UPDATE producto SET estado=? WHERE idproducto=?");
$stmt->execute([$estado, $id]);

auditoria(
    $conexion,
    'UPDATE',
    'productos',
    'producto',
    $id,
    ($estado ? "Activó" : "Desactivó") . " producto: " . ($antes['nombre'] ?? "ID {$id}"),
    ['estado' => $antes['estado'] ?? null],
    ['estado' => $estado]
);

echo "OK";
