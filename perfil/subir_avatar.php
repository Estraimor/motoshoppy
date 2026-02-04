<?php
session_start();
require_once '../conexion/conexion.php';

if (!isset($_SESSION['idusuario']) || empty($_FILES['avatar'])) {
  echo json_encode(['ok' => false]);
  exit;
}

$id = $_SESSION['idusuario'];
$archivo = $_FILES['avatar'];

$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$permitidas = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($ext, $permitidas)) {
  echo json_encode(['ok' => false]);
  exit;
}

/* =============================
   CREAR CARPETA SI NO EXISTE
============================= */
$dir = __DIR__ . '/avatars/';
if (!is_dir($dir)) {
  mkdir($dir, 0777, true);
}

/* =============================
   NOMBRE ÚNICO
============================= */
$nombreArchivo = "user_$id.$ext";
$rutaFisica = $dir . $nombreArchivo;
$rutaBD = "perfil/avatars/$nombreArchivo";

/* =============================
   MOVER ARCHIVO
============================= */
if (!move_uploaded_file($archivo['tmp_name'], $rutaFisica)) {
  echo json_encode(['ok' => false]);
  exit;
}

/* =============================
   GUARDAR EN BD
============================= */
$stmt = $conexion->prepare("
  UPDATE usuario SET avatar = :avatar WHERE idusuario = :id
");
$stmt->execute([
  ':avatar' => $rutaBD,
  ':id' => $id
]);

$_SESSION['avatar'] = $rutaBD;

echo json_encode(['ok' => true]);
