<?php
require_once '../conexion/conexion.php';
$id = intval($_GET['id'] ?? 0);

$response = ['marcas' => [], 'json_keys' => []];

if ($id > 0) {
    // Obtener marcas de la categoría
    $stmt = $conexion->prepare("SELECT idmarcas, nombre_marca FROM marcas WHERE categoria_idCategoria = ?");
    $stmt->execute([$id]);
    $response['marcas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener las keys JSON de productos existentes de esa categoría
    $stmt = $conexion->prepare("SELECT descripcion FROM producto WHERE Categoria_idCategoria = ? AND descripcion IS NOT NULL");
    $stmt->execute([$id]);
    $keys = [];
    foreach ($stmt as $row) {
        $json = json_decode($row['descripcion'], true);
        if (is_array($json)) {
            $keys = array_unique(array_merge($keys, array_keys($json)));
        }
    }
    $response['json_keys'] = array_values($keys);
}

header('Content-Type: application/json');
echo json_encode($response);
