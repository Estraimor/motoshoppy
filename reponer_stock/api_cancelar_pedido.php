<?php
require_once '../conexion/conexion.php';

$id = $_POST['id'];

$conexion->prepare("
    UPDATE reposicion
    SET estado = 'cancelado'
    WHERE idreposicion = ?
")->execute([$id]);

echo json_encode(['ok' => true]);
