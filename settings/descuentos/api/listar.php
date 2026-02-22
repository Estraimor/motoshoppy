<?php
require_once '../../conexion/conexion.php';

$stmt = $conexion->query("SELECT * FROM precio_lista ORDER BY id DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["data"=>$data]);