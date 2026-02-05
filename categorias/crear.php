<?php

require_once '../settings/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre      = $_POST['nombre_categoria'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $estado      = $_POST['estado'] ?? 1;

    $stmt = $conexion->prepare("
        INSERT INTO categoria (nombre_categoria, descripcion, estado)
        VALUES (:nombre, :descripcion, :estado)
    ");

    $ok = $stmt->execute([
        ':nombre'      => $nombre,
        ':descripcion' => $descripcion,
        ':estado'      => $estado
    ]);

    /* =========================
       AUDITORÍA
    ========================= */
    if ($ok) {

        $id = $conexion->lastInsertId();

        auditoria(
            $conexion,
            "INSERT",
            "categorias",
            "categoria",
            $id,
            "Creó categoría: $nombre"
        );
    }

    header("Location: index.php");
    exit;
}
?>
