<?php
session_start();
require_once '../conexion/conexion.php';
require_once '../settings/auditoria.php';

$nombre   = trim($_POST['nombre']   ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$dni      = trim($_POST['dni']      ?? '');
$celular  = trim($_POST['celular']  ?? '');
$email    = trim($_POST['email']    ?? '');

if (!$nombre || !$dni) {
    header('Location: index.php?msg=error_datos');
    exit;
}

$stmt = $conexion->prepare("
    INSERT INTO clientes (nombre, apellido, dni, celular, email, fecha_alta, estado)
    VALUES (:nombre, :apellido, :dni, :celular, :email, NOW(), 1)
");
$stmt->execute([
    ':nombre'   => $nombre,
    ':apellido' => $apellido,
    ':dni'      => $dni,
    ':celular'  => $celular ?: null,
    ':email'    => $email   ?: null,
]);

auditoria(
    $conexion,
    'INSERT',
    'clientes',
    'clientes',
    $conexion->lastInsertId(),
    "Creó cliente: {$nombre} {$apellido}",
    null,
    ['nombre' => $nombre, 'apellido' => $apellido, 'dni' => $dni, 'celular' => $celular, 'email' => $email]
);

header('Location: index.php?msg=creado');
exit;
