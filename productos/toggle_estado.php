<?php
require '../conexion/conexion.php';

$id = intval($_POST['id'] ?? 0);
$estado = intval($_POST['estado'] ?? 1);

if ($id <= 0) {
    echo "Error";
    exit;
}

$stmt = $conexion->prepare("UPDATE producto SET estado=? WHERE idproducto=?");
$stmt->execute([$estado, $id]);

echo "OK";
