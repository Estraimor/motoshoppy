<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../conexion/conexion.php';
require_once __DIR__ . '/../auditoria.php';

try {

    $usd_ars = floatval($_POST['usd_ars'] ?? 0);
    $usd_pyg = floatval($_POST['usd_pyg'] ?? 0);
    $ars_pyg = floatval($_POST['ars_pyg'] ?? 0);

    if ($usd_ars <= 0 || $usd_pyg <= 0 || $ars_pyg <= 0) {
        echo json_encode(['ok'=>false,'msg'=>'Valores inválidos']);
        exit;
    }

    $stmt = $conexion->prepare("
        INSERT INTO cotizacion (usd_ars, usd_pyg, ars_pyg, fuente)
        VALUES (?, ?, ?, 'Manual')
    ");

    $stmt->execute([$usd_ars, $usd_pyg, $ars_pyg]);

    auditoria(
        $conexion,
        'INSERT',
        'cotizacion',
        'cotizacion',
        $conexion->lastInsertId(),
        'Actualizó cotización manual',
        null,
        [
            'usd_ars'=>$usd_ars,
            'usd_pyg'=>$usd_pyg,
            'ars_pyg'=>$ars_pyg
        ]
    );

    echo json_encode(['ok'=>true]);

} catch (Throwable $e) {
    echo json_encode([
        'ok'=>false,
        'msg'=>$e->getMessage()
    ]);
}