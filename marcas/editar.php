<?php
require_once '../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['idmarcas'] ?? 0);
    $nombre = trim($_POST['nombre_marca'] ?? '');
    $categoria = intval($_POST['categoria_idCategoria'] ?? 0);
    $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;

    if ($id > 0 && $nombre && $categoria > 0) {
        try {
            $stmt = $conexion->prepare("
                UPDATE marcas 
                SET nombre_marca = :nombre, categoria_idCategoria = :categoria, estado = :estado 
                WHERE idmarcas = :id
            ");
            $stmt->execute([
                ':id' => $id,
                ':nombre' => $nombre,
                ':categoria' => $categoria,
                ':estado' => $estado
            ]);

            header("Location: index.php?msg=editado");
            exit;
        } catch (PDOException $e) {
            die("Error al editar: " . $e->getMessage());
        }
    } else {
        header("Location: index.php?error=CamposObligatorios");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
