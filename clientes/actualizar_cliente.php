<?php
session_start();
require_once '../conexion/conexion.php';
require_once '../settings/auditoria.php';

$id       = (int)($_POST['idCliente'] ?? 0);
$nombre   = trim($_POST['nombre']     ?? '');
$apellido = trim($_POST['apellido']   ?? '');
$dni      = trim($_POST['dni']        ?? '');
$celular  = trim($_POST['celular']    ?? '');
$email    = trim($_POST['email']      ?? '');

if (!$id || !$nombre || !$dni) {
    header('Location: index.php?msg=error_datos');
    exit;
}

$antesStmt = $conexion->prepare("SELECT nombre, apellido, dni, celular, email FROM clientes WHERE idCliente = ?");
$antesStmt->execute([$id]);
$antes = $antesStmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conexion->prepare("
    UPDATE clientes
    SET nombre   = :nombre,
        apellido = :apellido,
        dni      = :dni,
        celular  = :celular,
        email    = :email
    WHERE idCliente = :id
");
$stmt->execute([
    ':nombre'   => $nombre,
    ':apellido' => $apellido,
    ':dni'      => $dni,
    ':celular'  => $celular ?: null,
    ':email'    => $email   ?: null,
    ':id'       => $id,
]);

auditoria(
    $conexion,
    'UPDATE',
    'clientes',
    'clientes',
    $id,
    "Actualizó cliente: {$nombre} {$apellido}",
    $antes,
    ['nombre' => $nombre, 'apellido' => $apellido, 'dni' => $dni, 'celular' => $celular, 'email' => $email]
);

header('Location: index.php?msg=actualizado');
exit;
