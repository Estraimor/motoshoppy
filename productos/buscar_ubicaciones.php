<?php
require_once '../conexion/conexion.php';

$term = $_GET['term'] ?? '';

$stmt = $conexion->prepare("
    SELECT 
        CONCAT(lugar, IF(estante != '', CONCAT(' - Estante ', estante), '')) as label,
        idubicacion_producto
    FROM ubicacion_producto
    WHERE lugar LIKE ?
");

$stmt->execute(["%$term%"]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));