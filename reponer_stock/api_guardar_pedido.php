<?php
require_once '../settings/bootstrap.php'; // conexión + auditoría

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
        if (
            empty($idProveedor) ||
            empty($info['items']) ||
            !is_array($info['items'])
        ) {
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

        /* ===== AUDITORÍA CABECERA ===== */
        auditoria(
            $conexion,
            'INSERT',
            'reposiciones',
            'reposicion',
            $idReposicion,
            'Creó reposición',
            null,
            [
                'proveedores_idproveedores' => $idProveedor,
                'estado' => 'pedido'
            ],
            $idReposicion,
            'reposicion'
        );

        /* ===================================
           INSERT DETALLE
        =================================== */
        $stmtDetalle = $conexion->prepare("
            INSERT INTO reposicion_detalle (
                reposicion_idreposicion,
                producto_idProducto,
                cantidad,
                precio_unitario,
                codigo_proveedor
            ) VALUES (?, ?, ?, ?, ?)
        ");

        $totalPedido = 0; // por si luego querés usarlo

        foreach ($info['items'] as $item) {

            if (
                empty($item['id']) ||
                empty($item['cantidad']) ||
                $item['cantidad'] <= 0
            ) {
                continue;
            }

            $precioUnitario = isset($item['precio_unitario'])
                ? floatval($item['precio_unitario'])
                : 0;

            $codigoProveedor = $item['codigo_proveedor'] ?? null;

            $stmtDetalle->execute([
                $idReposicion,
                $item['id'],
                intval($item['cantidad']),
                $precioUnitario,
                $codigoProveedor
            ]);

            $idDetalle = $conexion->lastInsertId();

            // acumular total por si lo querés usar luego
            $totalPedido += ($precioUnitario * intval($item['cantidad']));

            /* ===== AUDITORÍA DETALLE ===== */
            auditoria(
                $conexion,
                'INSERT',
                'reposiciones',
                'reposicion_detalle',
                $idDetalle,
                'Agregó ítem a reposición',
                null,
                [
                    'reposicion_idreposicion' => $idReposicion,
                    'producto_idProducto'     => $item['id'],
                    'cantidad'                => intval($item['cantidad']),
                    'precio_unitario'         => $precioUnitario,
                    'codigo_proveedor'        => $codigoProveedor
                ],
                $idReposicion,
                'reposicion'
            );
        }

        /* ==================================================
           OPCIONAL (NO ACTIVO AHORA):
           GUARDAR TOTAL AUTOMÁTICO EN CABECERA
           (Yo recomiendo hacerlo al impactar)
        ================================================== */
        /*
        $stmtUpdate = $conexion->prepare("
            UPDATE reposicion
            SET costo_total = ?
            WHERE idreposicion = ?
        ");
        $stmtUpdate->execute([$totalPedido, $idReposicion]);
        */
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
