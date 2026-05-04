<?php
require_once '../conexion/conexion.php';

$id       = (int)($_POST['idCliente'] ?? 0);
$nombre   = trim($_POST['nombre']     ?? '');
$apellido = trim($_POST['apellido']   ?? '');
$dni      = trim($_POST['dni']        ?? '');
$celular  = trim($_POST['celular']    ?? '');
$email    = trim($_POST['email']      ?? '');

if (!$id || !$nombre || !$apellido || !$dni) {
    header('Location: index.php?msg=error_datos');
    exit;
}

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

header('Location: index.php?msg=actualizado');
exit;
