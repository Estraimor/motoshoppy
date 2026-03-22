<?php
require_once '../../../conexion/conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$estado = $data['estado'];

$stmt = $conexion->prepare("
  UPDATE precio_lista 
  SET activo = ?
  WHERE id = ?
");

$stmt->execute([$estado, $id]);

echo json_encode(["ok"=>true]);