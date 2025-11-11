<?php
require_once '../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_marca'] ?? '');
    $categoria = intval($_POST['categoria_idCategoria'] ?? 0);
    $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;

    if ($nombre && $categoria > 0) {
        try {
            $stmt = $conexion->prepare("
                INSERT INTO marcas (nombre_marca, categoria_idCategoria, estado)
                VALUES (:nombre, :categoria, :estado)
            ");
            $stmt->execute([
                ':nombre' => $nombre,
                ':categoria' => $categoria,
                ':estado' => $estado
            ]);

            header("Location: index.php?msg=creado");
            exit;
        } catch (PDOException $e) {
            die("Error al guardar: " . $e->getMessage());
        }
    } else {
        header("Location: index.php?error=CamposObligatorios");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
