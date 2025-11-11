<?php
require_once '../conexion/conexion.php';

$term = trim($_GET['term'] ?? '');
if ($term !== '') {
    $stmt = $conexion->prepare("SELECT idCategoria, nombre_categoria FROM categoria WHERE nombre_categoria LIKE :term LIMIT 10");
    $stmt->execute([':term' => "%$term%"]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($categorias);
}
