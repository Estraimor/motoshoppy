<?php
require_once '../conexion/conexion.php';

$categoriaId = intval($_GET['categoria'] ?? 0);
if ($categoriaId > 0) {
    $stmt = $conexion->prepare("
        SELECT descripcion 
        FROM productos 
        WHERE Categoria_idCategoria = :cat 
          AND descripcion IS NOT NULL
          AND descripcion != ''
        ORDER BY idProducto ASC 
        LIMIT 1
    ");
    $stmt->execute([':cat' => $categoriaId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo $row['descripcion']; // ya está en JSON
    } else {
        echo '[]';
    }
} else {
    echo '[]';
}
