<?php
require_once '../conexion/conexion.php';

$categoria = $_GET['categoria'] ?? '';

$sql = "
SELECT DISTINCT m.idmarcas, m.nombre_marca
FROM producto p
INNER JOIN marcas m 
    ON m.idmarcas = p.marcas_idmarcas
WHERE p.categoria_idCategoria = ?
ORDER BY m.nombre_marca ASC
";

$stmt = $conexion->prepare($sql);
$stmt->execute([$categoria]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));