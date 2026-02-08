<?php
session_start();

require_once '../conexion/conexion.php';
require_once '../settings/bootstrap.php'; // incluye auditoria + helpers

/* =========================
   VALIDACIONES BÁSICAS
========================= */

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: roles.php");
    exit;
}

// Acción válida
$accion = $_POST['accion'] ?? '';
$accionesPermitidas = ['crear', 'editar', 'eliminar'];

if (!in_array($accion, $accionesPermitidas, true)) {
    header("Location: roles.php");
    exit;
}

/* =========================
   CREAR ROL
========================= */
if ($accion === 'crear') {

    if (empty($_POST['nombre'])) {
        header("Location: roles.php?msg=error");
        exit;
    }

    $nombre  = trim($_POST['nombre']);
    $detalle = trim($_POST['detalle'] ?? '');

    $stmt = $conexion->prepare("
        INSERT INTO roles (nombre_rol, detalle_rol, estado)
        VALUES (?, ?, 1)
    ");
    $stmt->execute([$nombre, $detalle]);

    $idNuevo = $conexion->lastInsertId();

    auditoria(
        $conexion,
        'INSERT',
        'roles',
        'roles',
        $idNuevo,
        "Creó rol: {$nombre}",
        null,
        [
            'nombre_rol'  => $nombre,
            'detalle_rol' => $detalle,
            'estado'      => 1
        ],
        'roles',      // 🔥 afectado_tabla
        $idNuevo      // 🔥 afectado_id
    );

    header("Location: roles.php?msg=created");
    exit;
}

/* =========================
   EDITAR ROL
========================= */
if ($accion === 'editar') {

    if (empty($_POST['id']) || empty($_POST['nombre'])) {
        header("Location: roles.php?msg=error");
        exit;
    }

    $id      = (int) $_POST['id'];
    $nombre  = trim($_POST['nombre']);
    $detalle = trim($_POST['detalle'] ?? '');
    $estado  = (int) $_POST['estado'];

    // 🔹 ANTES
    $stmt = $conexion->prepare("SELECT * FROM roles WHERE idroles=?");
    $stmt->execute([$id]);
    $antesData = $stmt->fetch(PDO::FETCH_ASSOC);

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
    $stmt->execute([$nombre, $detalle, $estado, $id]);

    // 🔹 DESPUÉS
    $stmt = $conexion->prepare("SELECT * FROM roles WHERE idroles=?");
    $stmt->execute([$id]);
    $despuesData = $stmt->fetch(PDO::FETCH_ASSOC);

    auditoria(
        $conexion,
        'UPDATE',
        'roles',
        'roles',
        $id,
        "Editó rol: {$nombre}",
        $antesData,
        $despuesData,
        'roles',   // 🔥 afectado_tabla
        $id        // 🔥 afectado_id
    );

    header("Location: roles.php?msg=updated");
    exit;
}

/* =========================
   ELIMINAR ROL
========================= */
if ($accion === 'eliminar') {

    if (empty($_POST['id'])) {
        header("Location: roles.php?msg=error");
        exit;
    }

    $id = (int) $_POST['id'];

    // 🔹 ANTES
    $stmt = $conexion->prepare("SELECT * FROM roles WHERE idroles=?");
    $stmt->execute([$id]);
    $antesData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$antesData) {
        header("Location: roles.php?msg=notfound");
        exit;
    }

    // 🔹 DELETE
    $stmt = $conexion->prepare("DELETE FROM roles WHERE idroles=?");
    $stmt->execute([$id]);

    auditoria(
        $conexion,
        'DELETE',
        'roles',
        'roles',
        $id,
        "Eliminó rol: {$antesData['nombre_rol']}",
        $antesData,
        null,
        'roles',   // 🔥 afectado_tabla
        $id        // 🔥 afectado_id
    );

    header("Location: roles.php?msg=deleted");
    exit;
}
