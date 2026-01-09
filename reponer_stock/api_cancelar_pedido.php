<?php
require_once '../conexion/conexion.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    exit;
}

$sql = "UPDATE reposicion SET estado = 'cancelado' WHERE idreposicion = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);
