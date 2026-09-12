<?php
session_start();
require_once '../../../conexion/conexion.php';
require_once '../../../settings/auditoria.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$estado = $data['estado'];

$antesStmt = $conexion->prepare("SELECT nombre_lista, activo FROM precio_lista WHERE id = ?");
$antesStmt->execute([$id]);
$antes = $antesStmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conexion->prepare("
  UPDATE precio_lista
  SET activo = ?
  WHERE id = ?
");

$stmt->execute([$estado, $id]);

auditoria(
    $conexion,
    'UPDATE',
    'DESCUENTOS',
    'precio_lista',
    $id,
    ($estado ? "Activó" : "Desactivó") . " lista de descuento: " . ($antes['nombre_lista'] ?? "ID {$id}"),
    ['activo' => $antes['activo'] ?? null],
    ['activo' => $estado]
);

echo json_encode(["ok"=>true]);