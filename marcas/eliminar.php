<?php
require_once '../conexion/conexion.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $conexion->prepare("DELETE FROM marcas WHERE idmarcas = :id");
        $stmt->execute([':id' => $id]);

        header("Location: index.php?msg=eliminado");
        exit;
    } catch (PDOException $e) {
        die("Error al eliminar: " . $e->getMessage());
    }
} else {
    header("Location: index.php?error=IDInvalido");
    exit;
}
