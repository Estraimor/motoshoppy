<?php
require_once '../../conexion/conexion.php';

$id = $_GET['id'];

$stmt = $conexion->prepare("SELECT * FROM precio_lista WHERE id=?");
$stmt->execute([$id]);

echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));