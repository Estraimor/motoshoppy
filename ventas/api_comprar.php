<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion/conexion.php';
date_default_timezone_set('America/Argentina/Buenos_Aires');

try {
    // === Verificar sesión ===
    if (empty($_SESSION['idusuario'])) {
        throw new Exception("Sesión expirada o no iniciada.");
    }
    $usuario_id = intval($_SESSION['idusuario']);

    // === Leer JSON ===
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || empty($data['productos'])) {
        throw new Exception("Datos de venta inválidos.");
    }

    // === Datos base ===
    $tipo_comprobante = $data['tipo_comprobante'] ?? 'ninguno';
    $metodo_pago = $data['metodo_pago'] ?? 'efectivo';
    $productos = $data['productos'];
    $total = floatval($data['total'] ?? 0);
    $clienteData = $data['cliente'] ?? null;

    // === Obtener cotización actual ===
    $qCot = $conexion->query("SELECT usd_ars, ars_pyg FROM cotizacion ORDER BY fecha_actualizacion DESC LIMIT 1");
    $cot = $qCot->fetch(PDO::FETCH_ASSOC);
    $cot_usd = $cot['usd_ars'] ?? 0;
    $cot_ars = $cot['ars_pyg'] ?? 0;

    // === Iniciar transacción ===
    $conexion->beginTransaction();

    // === Verificar o crear cliente ===
    $cliente_id = null;
    if ($tipo_comprobante === 'factura' && $clienteData) {
        $dni = trim($clienteData['dni']);
        $buscar = $conexion->prepare("SELECT idCliente FROM clientes WHERE dni = ? LIMIT 1");
        $buscar->execute([$dni]);
        $cli = $buscar->fetch(PDO::FETCH_ASSOC);

        if ($cli) {
            $cliente_id = $cli['idCliente'];
        } else {
            $insertCli = $conexion->prepare("
                INSERT INTO clientes (apellido, nombre, dni, celular, email, fecha_alta, estado)
                VALUES (?, ?, ?, ?, NULL, NOW(), 1)
            ");
            $insertCli->execute([
                $clienteData['apellido'] ?? '',
                $clienteData['nombre'] ?? '',
                $clienteData['dni'] ?? '',
                $clienteData['celular'] ?? ''
            ]);
            $cliente_id = $conexion->lastInsertId();
        }
    }

    // === Insertar venta ===
    $stmtVenta = $conexion->prepare("
        INSERT INTO ventas 
        (fecha, total, metodo_pago, tipo_comprobante, usuario_id, cliente_id, cotizacion_usd, cotizacion_ars)
        VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtVenta->execute([
        $total,
        $metodo_pago,
        $tipo_comprobante,
        $usuario_id,
        $cliente_id,
        $cot_usd,
        $cot_ars
    ]);

    $venta_id = $conexion->lastInsertId();

    // === Insertar detalle ===
    $stmtDetalle = $conexion->prepare("
        INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario)
        VALUES (?, ?, ?, ?)
    ");

    $stmtStock = $conexion->prepare("
        UPDATE stock_producto
        SET cantidad_actual = cantidad_actual - ?
        WHERE producto_idProducto = ?
    ");

    foreach ($productos as $p) {
        $idProd = intval($p['idProducto']);
        $cantidad = intval($p['cantidad'] ?? 1);
        $precio = floatval($p['precio_expuesto'] ?? 0);

        $stmtDetalle->execute([$venta_id, $idProd, $cantidad, $precio]);
        $stmtStock->execute([$cantidad, $idProd]);
    }

    // === Confirmar ===
    $conexion->commit();

    echo json_encode([
        'ok' => true,
        'msg' => 'Venta registrada correctamente',
        'venta_id' => $venta_id,
        'tipo_comprobante' => $tipo_comprobante,
        'metodo_pago' => $metodo_pago,
        'cliente_id' => $cliente_id
    ]);
} catch (Exception $e) {
    if ($conexion->inTransaction()) $conexion->rollBack();
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
