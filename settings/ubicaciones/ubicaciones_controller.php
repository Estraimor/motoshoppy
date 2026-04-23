<?php
session_start();
require_once __DIR__ . '/../../conexion/conexion.php';

$accion = $_POST['accion'] ?? '';

// Crear
if ($accion === 'crear') {
    $lugar   = trim($_POST['lugar']   ?? '');
    $estante = trim($_POST['estante'] ?? '');

    if (!$lugar) {
        echo json_encode(['ok' => false, 'msg' => 'El lugar es obligatorio.']);
        exit;
    }

    $stmt = $conexion->prepare("INSERT INTO ubicacion_producto (lugar, estante) VALUES (?, ?)");
    $stmt->execute([$lugar, $estante]);

    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'id' => $conexion->lastInsertId()]);
    exit;
}

// Editar
if ($accion === 'editar') {
    $id      = intval($_POST['id'] ?? 0);
    $lugar   = trim($_POST['lugar']   ?? '');
    $estante = trim($_POST['estante'] ?? '');

    if (!$id || !$lugar) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'msg' => 'Datos inválidos.']);
        exit;
    }

    $stmt = $conexion->prepare("UPDATE ubicacion_producto SET lugar = ?, estante = ? WHERE idubicacion_producto = ?");
    $stmt->execute([$lugar, $estante, $id]);

    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

// Eliminar
if ($accion === 'eliminar') {
    $id = intval($_POST['id'] ?? 0);

    if (!$id) {
        header("Location: index.php");
        exit;
    }

    // Desvincular productos que usen esta ubicación
    $conexion->prepare("
        UPDATE producto SET ubicacion_producto_idubicacion_producto = NULL
        WHERE ubicacion_producto_idubicacion_producto = ?
    ")->execute([$id]);

    $conexion->prepare("DELETE FROM ubicacion_producto WHERE idubicacion_producto = ?")->execute([$id]);

    header("Location: index.php?msg=eliminada");
    exit;
}

header('Content-Type: application/json');
echo json_encode(['ok' => false, 'msg' => 'Acción no válida.']);
