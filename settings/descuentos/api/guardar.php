<?php
require_once '../../conexion/conexion.php';

$id = $_POST['id'] ?? null;
$nombre = $_POST['nombre_lista'];
$porcentaje = $_POST['porcentaje_descuento'];
$activo = isset($_POST['activo']) ? 1 : 0;

if($id){
  $stmt = $conexion->prepare("
    UPDATE precio_lista 
    SET nombre_lista=?, porcentaje_descuento=?, activo=? 
    WHERE id=?
  ");
  $stmt->execute([$nombre,$porcentaje,$activo,$id]);
}else{
  $stmt = $conexion->prepare("
    INSERT INTO precio_lista (nombre_lista, porcentaje_descuento, activo)
    VALUES (?,?,?)
  ");
  $stmt->execute([$nombre,$porcentaje,$activo]);
}

echo json_encode(["ok"=>true]);