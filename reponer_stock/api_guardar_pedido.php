<?php
require_once '../conexion/conexion.php';

header('Content-Type: application/json');

// Leer JSON enviado desde frontend
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !is_array($data)) {
    echo json_encode([
        'ok' => false,
        'error' => 'Datos inválidos'
    ]);
    exit;
}

$conexion->beginTransaction();

try {

    foreach ($data as $idProveedor => $info) {

        // Validar proveedor e items
        if (empty($idProveedor) || empty($info['items']) || !is_array($info['items'])) {
            continue;
        }

        /* ===================================
           INSERT REPOSICION (CABECERA)
        =================================== */
        $stmt = $conexion->prepare("
            INSERT INTO reposicion (
                proveedores_idproveedores,
                estado,
                fecha_pedido
            ) VALUES (?, 'pedido', NOW())
        ");
        $stmt->execute([$idProveedor]);

        $idReposicion = $conexion->lastInsertId();

        /* ===================================
           INSERT DETALLE
        =================================== */
        $stmtDetalle = $conexion->prepare("
            INSERT INTO reposicion_detalle (
                reposicion_idreposicion,
                producto_idProducto,
                cantidad,
                codigo_proveedor
            ) VALUES (?, ?, ?, ?)
        ");

        foreach ($info['items'] as $item) {

            if (
                empty($item['id']) ||
                empty($item['cantidad']) ||
                $item['cantidad'] <= 0
            ) {
                continue;
            }

            // Código del proveedor (puede venir vacío)
            $codigoProveedor = $item['codigo_proveedor'] ?? null;

            $stmtDetalle->execute([
                $idReposicion,
                $item['id'],
                $item['cantidad'],
                $codigoProveedor
            ]);
        }
    }

    $conexion->commit();

    echo json_encode([
        'ok' => true,
        'mensaje' => 'Pedidos guardados correctamente'
    ]);

} catch (Exception $e) {

    $conexion->rollBack();

    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
