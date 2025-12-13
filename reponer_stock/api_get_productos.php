<?php
require_once '../conexion/conexion.php';
$id = $_GET['idCategoria'] ?? 0;

$q = $conexion->prepare("SELECT idProducto,nombre FROM producto WHERE Categoria_idCategoria=? ORDER BY nombre");
$q->execute([$id]);
echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
