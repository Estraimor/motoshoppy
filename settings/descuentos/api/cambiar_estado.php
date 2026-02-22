<?php
require_once '../../    conexion/conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$estado = $data['estado'];

// OPCIONAL: solo permitir un descuento activo
if($estado == 1){
  $conexion->exec("UPDATE precio_lista SET activo = 0");
}

$stmt = $conexion->prepare("
  UPDATE precio_lista 
  SET activo = ?
  WHERE id = ?
");

$stmt->execute([$estado, $id]);

echo json_encode(["ok"=>true]);