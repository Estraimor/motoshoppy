<?php
require '../../conexion/conexion.php';

header('Content-Type: application/json');

$sql = "
SELECT 
    IFNULL(sp.cantidad_actual,0) AS deposito,
    IFNULL(sp.cantidad_exhibida,0) AS exhibido,
    IFNULL(sp.stock_minimo,0) AS minimo
FROM producto p
LEFT JOIN stock_producto sp 
    ON sp.producto_idProducto = p.idproducto
";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sinStock = 0;
$criticos = 0;
$rotar    = 0;
$bajo     = 0;
$ok       = 0;

foreach ($productos as $p) {

    $deposito = (int)$p['deposito'];
    $exhibido = (int)$p['exhibido'];
    $minimo   = (int)$p['minimo'];
    $total    = $deposito + $exhibido;

    // 🔴 SIN STOCK TOTAL
    if ($total === 0) {
        $sinStock++;
        continue;
    }

    // 🔴 CRÍTICO (ambos debajo del mínimo)
    if ($deposito < $minimo && $exhibido < $minimo) {
        $criticos++;
    }

    // 🟠 ROTAR (sin exhibido pero hay depósito)
    elseif ($exhibido === 0 && $deposito > 0) {
        $rotar++;
    }

    // 🟡 BAJO MÍNIMO (uno debajo)
    elseif ($deposito < $minimo || $exhibido < $minimo) {
        $bajo++;
    }

    else {
        $ok++;
    }
}

echo json_encode([
    "sin_stock" => $sinStock,
    "criticos"  => $criticos,
    "rotar"     => $rotar,
    "bajo"      => $bajo,
    "ok"        => $ok
]);
