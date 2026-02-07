<?php
session_start();

require_once '../conexion/conexion.php';
require_once '../settings/bootstrap.php'; // incluye auditoria + helpers

/* =========================
   VALIDACIONES BASICAS
========================= */

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: roles.php");
    exit;
}

// Acción válida
$accion = $_POST['accion'] ?? '';
$accionesPermitidas = ['crear', 'editar', 'eliminar'];

if (!in_array($accion, $accionesPermitidas)) {
    header("Location: roles.php");
    exit;
}

/* =========================
   CREAR
========================= */
if ($accion === 'crear') {

    // Validación mínima
    if (empty($_POST['nombre'])) {
        header("Location: roles.php?msg=error");
        exit;
    }

    $stmt = $conexion->prepare("
        INSERT INTO roles (nombre_rol, detalle_rol, estado)
        VALUES (?, ?, 1)
    ");
    $stmt->execute([
        trim($_POST['nombre']),
        trim($_POST['detalle'] ?? '')
    ]);

    $idNuevo = $conexion->lastInsertId();

    auditoria(
        $conexion,
        "INSERT",
        "roles",
        "roles",
        $idNuevo,
        "Creó rol: " . $_POST['nombre'],
        null,
        $_POST
    );

    header("Location: roles.php?msg=created");
    exit;
}

/* =========================
   EDITAR
========================= */
if ($accion === 'editar') {

    if (empty($_POST['id']) || empty($_POST['nombre'])) {
        header("Location: roles.php?msg=error");
        exit;
    }

    // 🔹 ANTES
    $antes = $conexion->prepare("SELECT * FROM roles WHERE idroles=?");
    $antes->execute([$_POST['id']]);
    $antesData = $antes->fetch(PDO::FETCH_ASSOC);

    if (!$antesData) {
        header("Location: roles.php?msg=notfound");
        exit;
    }

    // 🔹 UPDATE
    $stmt = $conexion->prepare("
        UPDATE roles
        SET nombre_rol=?, detalle_rol=?, estado=?
        WHERE idroles=?
    ");
    $stmt->execute([
        trim($_POST['nombre']),
        trim($_POST['detalle'] ?? ''),
        (int) $_POST['estado'],
        $_POST['id']
    ]);

    // 🔹 DESPUÉS
    $despues = $conexion->prepare("SELECT * FROM roles WHERE idroles=?");
    $despues->execute([$_POST['id']]);
    $despuesData = $despues->fetch(PDO::FETCH_ASSOC);

    auditoria(
        $conexion,
        "UPDATE",
        "roles",
        "roles",
        $_POST['id'],
        "Editó rol: " . $_POST['nombre'],
        $antesData,
        $despuesData
    );

    header("Location: roles.php?msg=updated");
    exit;
}

/* =========================
   ELIMINAR
========================= */
if ($accion === 'eliminar') {

    if (empty($_POST['id'])) {
        header("Location: roles.php?msg=error");
        exit;
    }

    // 🔹 ANTES
    $antes = $conexion->prepare("SELECT * FROM roles WHERE idroles=?");
    $antes->execute([$_POST['id']]);
    $antesData = $antes->fetch(PDO::FETCH_ASSOC);

    if (!$antesData) {
        header("Location: roles.php?msg=notfound");
        exit;
    }

    // 🔹 DELETE
    $stmt = $conexion->prepare("DELETE FROM roles WHERE idroles=?");
    $stmt->execute([$_POST['id']]);

    auditoria(
        $conexion,
        "DELETE",
        "roles",
        "roles",
        $_POST['id'],
        "Eliminó rol: " . $antesData['nombre_rol'],
        $antesData,
        null
    );

    header("Location: roles.php?msg=deleted");
    exit;
}
