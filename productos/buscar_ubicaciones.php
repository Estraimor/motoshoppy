<?php
require_once '../conexion/conexion.php';

$term = $_GET['term'] ?? '';

$stmt = $conexion->prepare("
    SELECT
        CONCAT(lugar, IF(estante != '' AND estante IS NOT NULL, CONCAT(' - Estante ', estante), '')) AS id,
        CONCAT(lugar, IF(estante != '' AND estante IS NOT NULL, CONCAT(' - Estante ', estante), '')) AS text
    FROM ubicacion_producto
    WHERE lugar LIKE :term OR estante LIKE :term
    ORDER BY lugar, estante
    LIMIT 30
");

$stmt->execute([':term' => "%$term%"]);
header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
