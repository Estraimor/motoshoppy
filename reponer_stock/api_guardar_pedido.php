<?php
require_once '../conexion/conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !is_array($data)) {
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
    exit;
}

$conexion->beginTransaction();

try {

    foreach ($data as $idProveedor => $info) {

        // Validar proveedor e items
        if (empty($idProveedor) || empty($info['items'])) {
            continue;
        }

        // ================================
        // INSERT REPOSICION (PEDIDO)
        // ================================
        $stmt = $conexion->prepare("
            INSERT INTO reposicion (
                proveedores_idproveedores,
                estado,
                fecha_pedido
            ) VALUES (?, 'pedido', NOW())
        ");
        $stmt->execute([$idProveedor]);

        $idReposicion = $conexion->lastInsertId();

        // ================================
        // INSERT DETALLE
        // ================================
        $stmtDetalle = $conexion->prepare("
            INSERT INTO reposicion_detalle (
                reposicion_idreposicion,
                producto_idProducto,
                cantidad
            ) VALUES (?, ?, ?)
        ");

        foreach ($info['items'] as $item) {

            if (
                empty($item['id']) ||
                empty($item['cantidad']) ||
                $item['cantidad'] <= 0
            ) {
                continue;
            }

            $stmtDetalle->execute([
                $idReposicion,
                $item['id'],
                $item['cantidad']
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
