<?php
session_start();
require_once '../conexion/conexion.php';
require_once '../settings/auditoria.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($_SESSION['idusuario'])) {
  echo json_encode(['ok' => false]);
  exit;
}

$antesStmt = $conexion->prepare("SELECT nombre, apellido, dni, celular, avatar FROM usuario WHERE idusuario = ?");
$antesStmt->execute([$_SESSION['idusuario']]);
$antes = $antesStmt->fetch(PDO::FETCH_ASSOC);

$permitidos = ['nombre', 'apellido', 'dni', 'celular', 'avatar'];
$set = [];
$params = [];
$despues = [];

foreach ($permitidos as $campo) {
  if (isset($data[$campo])) {
    $valor = trim($data[$campo]);

    // ❗ no obligamos a que esté cargado
    $set[] = "$campo = :$campo";
    $params[":$campo"] = $valor;
    $despues[$campo] = $valor;

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

auditoria($conexion, 'UPDATE', 'perfil', 'usuario', $_SESSION['idusuario'],
    'Actualizó sus datos de perfil', $antes, $despues);

echo json_encode(['ok' => true]);
