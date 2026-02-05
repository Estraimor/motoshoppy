<?php
session_start();

include '../conexion/conexion.php';
include '../settings/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: roles.php");
    exit;
}

$accion = $_POST['accion'] ?? '';

/* ========= CREAR ========= */
if ($accion === 'crear') {

    $stmt = $conexion->prepare("
        INSERT INTO roles (nombre_rol, detalle_rol, estado)
        VALUES (?, ?, 1)
    ");
    $stmt->execute([$_POST['nombre'], $_POST['detalle']]);

    auditoria(
        $conexion,
        "INSERT",
        "roles",
        "roles",
        $conexion->lastInsertId(),
        "Creó rol: ".$_POST['nombre'],
        null,
        $_POST
    );
}

/* ========= EDITAR ========= */
if ($accion === 'editar') {

    $antes = $conexion->prepare("SELECT * FROM roles WHERE idroles=?");
    $antes->execute([$_POST['id']]);
    $antesData = $antes->fetch(PDO::FETCH_ASSOC);

    $stmt = $conexion->prepare("
        UPDATE roles
        SET nombre_rol=?, detalle_rol=?, estado=?
        WHERE idroles=?
    ");
    $stmt->execute([
        $_POST['nombre'],
        $_POST['detalle'],
        $_POST['estado'],
        $_POST['id']
    ]);

    $despues = $conexion->prepare("SELECT * FROM roles WHERE idroles=?");
    $despues->execute([$_POST['id']]);
    $despuesData = $despues->fetch(PDO::FETCH_ASSOC);

    auditoria(
        $conexion,
        "UPDATE",
        "roles",
        "roles",
        $_POST['id'],
        "Editó rol: ".$_POST['nombre'],
        $antesData,
        $despuesData
    );
}

/* ========= ELIMINAR ========= */
if ($accion === 'eliminar') {

    $antes = $conexion->prepare("SELECT * FROM roles WHERE idroles=?");
    $antes->execute([$_POST['id']]);
    $antesData = $antes->fetch(PDO::FETCH_ASSOC);

    $stmt = $conexion->prepare("DELETE FROM roles WHERE idroles=?");
    $stmt->execute([$_POST['id']]);

    auditoria(
        $conexion,
        "DELETE",
        "roles",
        "roles",
        $_POST['id'],
        "Eliminó rol",
        $antesData,
        null
    );
}

header("Location: roles.php");
exit;
