<?php
session_start();

require_once __DIR__ . '/../../conexion/conexion.php';
require_once __DIR__ . '/../../settings/auditoria.php';

/* =========================
   VALIDACIÓN MÉTODO
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$accion = $_POST['accion'] ?? '';

/* =========================
   CREAR USUARIO
========================= */
if ($accion === 'crear_usuario') {

    $stmt = $conexion->prepare("
        INSERT INTO usuario (nombre, apellido, dni, celular, usuario, pass)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['nombre'],
        $_POST['apellido'],
        $_POST['dni'],
        $_POST['celular'],
        $_POST['usuario'],
        password_hash($_POST['pass'], PASSWORD_DEFAULT)
    ]);

    $usuarioId = $conexion->lastInsertId();

    auditoria(
        $conexion,
        'INSERT',
        'usuarios',
        'usuario',
        $usuarioId,
        'Creó usuario',
        null,
        [
            'nombre'   => $_POST['nombre'],
            'apellido' => $_POST['apellido'],
            'dni'      => $_POST['dni'],
            'celular'  => $_POST['celular'],
            'usuario'  => $_POST['usuario']
        ],
        'usuario',          // 🔥 afectado_tabla
        $usuarioId          // 🔥 afectado_id
    );

    header("Location: index.php?msg=usuario_creado");
    exit;
}

/* =========================
   ASIGNAR / CAMBIAR ROLES
========================= */
if ($accion === 'asignar_roles') {

    $usuarioId = $_POST['usuario_id'] ?? null;
    $roles     = json_decode($_POST['roles_json'] ?? '[]', true);

    if (!$usuarioId) {
        header("Location: index.php?msg=error");
        exit;
    }

    /* 🔹 roles anteriores */
    $antes = $conexion->prepare("
        SELECT rol_id FROM usuario_roles WHERE usuario_id = ?
    ");
    $antes->execute([$usuarioId]);
    $antesRoles = $antes->fetchAll(PDO::FETCH_COLUMN);

    /* 🔹 borrar todos */
    $conexion->prepare("
        DELETE FROM usuario_roles WHERE usuario_id = ?
    ")->execute([$usuarioId]);

    /* 🔹 insertar nuevos */
    $stmt = $conexion->prepare("
        INSERT INTO usuario_roles (usuario_id, rol_id)
        VALUES (?, ?)
    ");

    foreach ($roles as $rolId) {
        $stmt->execute([$usuarioId, $rolId]);
    }

    auditoria(
        $conexion,
        'UPDATE',
        'usuarios',
        'usuario_roles',
        $usuarioId,
        'Actualizó roles del usuario',
        ['roles' => $antesRoles],
        ['roles' => $roles],
        'usuario',          // 🔥 afectado_tabla
        $usuarioId          // 🔥 afectado_id
    );

    header("Location: index.php?msg=roles_actualizados");
    exit;
}

/* =========================
   ELIMINAR USUARIO + ROLES
========================= */
if ($accion === 'eliminar_usuario') {

    $usuarioId = $_POST['usuario_id'] ?? null;

    if (!$usuarioId) {
        header("Location: index.php?msg=error");
        exit;
    }

    /* 🔹 datos usuario antes */
    $stmt = $conexion->prepare("SELECT * FROM usuario WHERE idusuario=?");
    $stmt->execute([$usuarioId]);
    $antesUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

    /* 🔹 roles antes */
    $stmt = $conexion->prepare("
        SELECT rol_id FROM usuario_roles WHERE usuario_id = ?
    ");
    $stmt->execute([$usuarioId]);
    $rolesAntes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    /* 🔹 borrar roles */
    $conexion->prepare("
        DELETE FROM usuario_roles WHERE usuario_id = ?
    ")->execute([$usuarioId]);

    /* 🔹 borrar usuario */
    $conexion->prepare("
        DELETE FROM usuario WHERE idusuario = ?
    ")->execute([$usuarioId]);

    auditoria(
        $conexion,
        'DELETE',
        'usuarios',
        'usuario / usuario_roles',
        $usuarioId,
        'Eliminó usuario y sus roles',
        [
            'usuario' => $antesUsuario,
            'roles'   => $rolesAntes
        ],
        null,
        'usuario',          // 🔥 afectado_tabla
        $usuarioId          // 🔥 afectado_id
    );

    header("Location: index.php?msg=usuario_eliminado");
    exit;
}

/* =========================
   ACCIÓN INVÁLIDA
========================= */
header("Location: index.php?msg=error");
exit;
