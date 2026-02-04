<?php
session_start();
require_once '../conexion/conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($_SESSION['idusuario'])) {
  echo json_encode(['ok' => false]);
  exit;
}

$permitidos = ['nombre', 'apellido', 'dni', 'celular', 'avatar'];
$set = [];
$params = [];

foreach ($permitidos as $campo) {
  if (isset($data[$campo])) {
    $valor = trim($data[$campo]);

    // ❗ no obligamos a que esté cargado
    $set[] = "$campo = :$campo";
    $params[":$campo"] = $valor;

    // mantener sesión sincronizada
    $_SESSION[$campo] = $valor;
  }
}

if (empty($set)) {
  echo json_encode(['ok' => false]);
  exit;
}

$params[':id'] = $_SESSION['idusuario'];

$sql = "UPDATE usuario SET " . implode(', ', $set) . " WHERE idusuario = :id";
$stmt = $conexion->prepare($sql);
$stmt->execute($params);

echo json_encode(['ok' => true]);
