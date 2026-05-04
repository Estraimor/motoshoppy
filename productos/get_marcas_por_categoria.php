<?php
require_once '../conexion/conexion.php';

header('Content-Type: application/json');

$categoria = intval($_GET['categoria'] ?? 0);

if (!$categoria) {
    // Sin categoría → devolver todas las marcas
    $stmt = $conexion->query("SELECT idmarcas, nombre_marca FROM marcas ORDER BY nombre_marca ASC");
} else {
    $stmt = $conexion->prepare("
        SELECT DISTINCT m.idmarcas, m.nombre_marca
        FROM producto p
        INNER JOIN marcas m ON m.idmarcas = p.marcas_idmarcas
        WHERE p.Categoria_idCategoria = ?
        ORDER BY m.nombre_marca ASC
    ");
    $stmt->execute([$categoria]);
}

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
