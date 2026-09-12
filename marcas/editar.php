<?php
session_start();
require_once '../conexion/conexion.php';
require_once '../settings/auditoria.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['idmarcas'] ?? 0);
    $nombre = trim($_POST['nombre_marca'] ?? '');
    $categoria = intval($_POST['categoria_idCategoria'] ?? 0);
    $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;

    if ($id > 0 && $nombre && $categoria > 0) {
        try {
            $antesStmt = $conexion->prepare("SELECT nombre_marca, categoria_idCategoria, estado FROM marcas WHERE idmarcas = ?");
            $antesStmt->execute([$id]);
            $antes = $antesStmt->fetch(PDO::FETCH_ASSOC);

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

            auditoria(
                $conexion,
                'UPDATE',
                'marcas',
                'marcas',
                $id,
                "Editó marca: {$nombre}",
                $antes,
                ['nombre_marca' => $nombre, 'categoria_idCategoria' => $categoria, 'estado' => $estado]
            );

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
