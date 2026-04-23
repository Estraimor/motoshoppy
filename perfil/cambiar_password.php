<?php
session_start();
require_once __DIR__ . '/../conexion/conexion.php';

header('Content-Type: application/json');

if (empty($_SESSION['idusuario'])) {
    echo json_encode(['ok' => false, 'msg' => 'Sesión no válida.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['actual']) || empty($data['nueva'])) {
    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos.']);
    exit;
}

$id     = $_SESSION['idusuario'];
$actual = $data['actual'];
$nueva  = trim($data['nueva']);

if (strlen($nueva) < 8) {
    echo json_encode(['ok' => false, 'msg' => 'La contraseña debe tener al menos 8 caracteres.']);
    exit;
}

// Obtener contraseña actual
$stmt = $conexion->prepare("SELECT pass FROM usuario WHERE idusuario = :id");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();

if (!$row) {
    echo json_encode(['ok' => false, 'msg' => 'Usuario no encontrado.']);
    exit;
}

// Verificar contraseña actual (texto plano)
if ($row['pass'] !== $actual) {
    echo json_encode(['ok' => false, 'msg' => 'La contraseña actual es incorrecta.']);
    exit;
}

// Guardar nueva contraseña
$upd = $conexion->prepare("UPDATE usuario SET pass = :pass WHERE idusuario = :id");
$upd->execute([':pass' => $nueva, ':id' => $id]);

echo json_encode(['ok' => true]);
