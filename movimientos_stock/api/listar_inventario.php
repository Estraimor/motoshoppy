<?php
require '../../conexion/conexion.php';
header('Content-Type: application/json');

/* =====================================================
   TRAER INVENTARIO COMPLETO CON INFO EXTRA
===================================================== */

$sql = "
SELECT 
    p.idproducto,
    p.nombre,
    p.codigo,
    m.nombre_marca AS marca,
    c.nombre_categoria AS categoria,
    IFNULL(sp.cantidad_actual,0) AS cantidad_actual,
    IFNULL(sp.cantidad_exhibida,0) AS cantidad_exhibida,
    IFNULL(sp.stock_minimo,0) AS stock_minimo
FROM producto p
LEFT JOIN stock_producto sp 
    ON sp.producto_idProducto = p.idproducto
LEFT JOIN marcas m
    ON m.idmarcas = p.marcas_idmarcas
LEFT JOIN categoria c
    ON c.idcategoria = p.categoria_idcategoria
WHERE p.estado = 1
ORDER BY p.nombre ASC
";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];

foreach ($productos as $p) {

    $deposito  = (int)$p['cantidad_actual'];
    $exhibido  = (int)$p['cantidad_exhibida'];
    $minimo    = (int)$p['stock_minimo'];
    $total     = $deposito + $exhibido;

    $estado = '';
    $rowClass = '';

    /* =====================================================
       LÓGICA VISUAL DE ESTADO
    ===================================================== */

    if ($minimo === 0 && $total === 0) {

        $estado = "
            <span class='badge bg-secondary'>
                ⚙ No configurado
            </span>";
        $rowClass = 'estado-sin-stock';

    } elseif ($total === 0) {

        $estado = "
            <span class='badge bg-danger'>
                🔴 Sin stock
            </span>";
        $rowClass = 'estado-sin-stock';

    } elseif ($exhibido === 0 && $deposito > 0) {

        $estado = "
            <span class='badge bg-warning text-dark'>
                🔄 Mover a exhibición
            </span>";
        $rowClass = 'estado-rotar';

    } elseif ($deposito < $minimo && $exhibido < $minimo) {

        $estado = "
            <span class='badge bg-danger'>
                ⚠ Crítico
            </span>";
        $rowClass = 'estado-critico';

    } elseif ($deposito < $minimo || $exhibido < $minimo) {

        $estado = "
            <span class='badge bg-info text-dark'>
                📉 Bajo mínimo
            </span>";
        $rowClass = 'estado-bajo';

    } else {

        $estado = "
            <span class='badge bg-success'>
                ✔ OK
            </span>";
        $rowClass = 'estado-ok';
    }

    /* =====================================================
       BOTÓN PARA ABRIR MODAL
    ===================================================== */

    $productoJson = htmlspecialchars(json_encode([
        "idproducto"        => $p['idproducto'],
        "nombre"            => $p['nombre'],
        "codigo"            => $p['codigo'],
        "marca"             => $p['marca'] ?? '',
        "categoria"         => $p['categoria'] ?? '',
        "cantidad_actual"   => $deposito,
        "cantidad_exhibida" => $exhibido,
        "stock_minimo"      => $minimo
    ]), ENT_QUOTES, 'UTF-8');

    $accion = "
        <button 
            class='btn btn-outline-light btn-sm btn-stock'
            data-producto='{$productoJson}'
        >
            <i class='fa-solid fa-sliders'></i>
        </button>
    ";

    $data[] = [
        "idproducto"        => $p['idproducto'],
        "nombre"            => $p['nombre'],
        "codigo"            => $p['codigo'],
        "cantidad_actual"   => $deposito,
        "cantidad_exhibida" => $exhibido,
        "stock_minimo"      => $minimo,
        "estado_html"       => $estado,
        "accion_html"       => $accion,
        "DT_RowClass"       => $rowClass   // 🔥 clave para pintar fila
    ];
}

echo json_encode([
    "data" => $data
]);
