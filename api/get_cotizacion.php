
<?php
include '../conexion/conexion.php';
header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT * FROM cotizacion ORDER BY id DESC LIMIT 1";
$stmt = $conexion->query($sql);
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: []);
