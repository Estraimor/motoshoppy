<?php
require_once '../../conexion/conexion.php';

$id = $_GET['id'] ?? 0;

$stmt = $conexion->prepare("
    SELECT 
        p.idProducto AS idproducto,
        p.nombre,
        p.codigo,
        m.nombre_marca AS marca,
        c.nombre_categoria AS categoria,
        COALESCE(sp.cantidad_exhibida,0) AS cantidad_exhibida,
        COALESCE(sp.cantidad_actual,0) AS cantidad_actual,
        COALESCE(sp.stock_minimo,0) AS stock_minimo
    FROM producto p
    LEFT JOIN stock_producto sp 
        ON sp.producto_idProducto = p.idProducto
    LEFT JOIN marcas m 
        ON m.idMarcas = p.marcas_idMarcas
    LEFT JOIN categoria c 
        ON c.idCategoria = p.categoria_idCategoria
    WHERE p.idProducto = ?
");

$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if($producto){
    echo json_encode([
        'ok' => true,
        'producto' => $producto
    ]);
} else {
    echo json_encode([
        'ok' => false
    ]);
}
