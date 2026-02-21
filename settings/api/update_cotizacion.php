<?php
require_once '../../conexion/conexion.php';
require_once '../settings/auditoria.php';

$usd_ars = floatval($_POST['usd_ars']);
$usd_pyg = floatval($_POST['usd_pyg']);

if ($usd_ars <= 0 || $usd_pyg <= 0) {
    echo json_encode(['ok'=>false,'msg'=>'Valores inválidos']);
    exit;
}

$ars_pyg = $usd_ars / $usd_pyg;

$stmt = $conexion->prepare("
    INSERT INTO cotizacion (usd_ars, usd_pyg, ars_pyg, fuente)
    VALUES (?, ?, ?, 'Manual')
");

$stmt->execute([$usd_ars, $usd_pyg, $ars_pyg]);

auditoria($conexion,'INSERT','cotizacion','cotizacion',$conexion->lastInsertId(),'Actualizó cotización',null,[
    'usd_ars'=>$usd_ars,
    'usd_pyg'=>$usd_pyg
]);

echo json_encode(['ok'=>true]);
