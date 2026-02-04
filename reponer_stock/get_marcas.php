<?php
require_once '../conexion/conexion.php';

$idCategoria = $_GET['idCategoria'] ?? 0;

$q = $conexion->prepare("
    SELECT idmarcas, nombre_marca
    FROM marcas
    WHERE categoria_idCategoria = ?
    AND estado = 1
    ORDER BY nombre_marca
");

$q->execute([$idCategoria]);

echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
