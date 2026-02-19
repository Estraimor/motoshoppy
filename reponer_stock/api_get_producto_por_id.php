<?php
require_once '../conexion/conexion.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode([
        'ok' => false,
        'msg' => 'ID inválido'
    ]);
    exit;
}

$stmt = $conexion->prepare("
    SELECT 
        idProducto,
        nombre,
        categoria_idCategoria AS idcategoria,
        marcas_idmarcas AS idmarca,
        codigo,
        imagen
    FROM producto
    WHERE idProducto = ?
    LIMIT 1
");

$stmt->execute([$id]);

$p = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'ok' => $p ? true : false,
    'producto' => $p
]);
