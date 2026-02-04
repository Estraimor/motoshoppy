<?php
require_once '../conexion/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$idMarca = $_GET['idMarca'] ?? 0;

$q = $conexion->prepare("
    SELECT 
        idProducto,
        nombre,
        imagen
    FROM producto
    WHERE marcas_idmarcas = ?
    ORDER BY nombre
");

$q->execute([$idMarca]);

echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
