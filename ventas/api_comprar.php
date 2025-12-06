<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion/conexion.php';
date_default_timezone_set('America/Argentina/Buenos_Aires');

try {

    if (empty($_SESSION['idusuario'])) {
        throw new Exception("Sesión expirada o no iniciada.");
    }
    $usuario_id = intval($_SESSION['idusuario']);

    // === Leer JSON ===
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || empty($data['productos'])) {
        throw new Exception("Datos de venta inválidos.");
    }

    // === Datos recibidos ===
    $tipo_comprobante = intval($data['tipo_comprobante'] ?? 0); 
    $metodo_pago = intval($data['metodo_pago'] ?? 1);
    $moneda = intval($data['moneda'] ?? 1);  // DEFAULT PYG SIEMPRE
    $productos = $data['productos'];
    $total = floatval($data['total'] ?? 0);
    $clienteData = $data['cliente'] ?? null;

    // === Iniciar transacción ===
    $conexion->beginTransaction();

    // === Verificar o crear cliente ===
    $cliente_id = null;

    if ($tipo_comprobante > 1 && $clienteData) { // FACTURA
        $dni = trim($clienteData['dni']);

        $buscar = $conexion->prepare("SELECT idCliente FROM clientes WHERE dni = ? LIMIT 1");
        $buscar->execute([$dni]);
        $cli = $buscar->fetch(PDO::FETCH_ASSOC);

        if ($cli) {
            $cliente_id = $cli['idCliente'];
        } else {
            $ins = $conexion->prepare("
                INSERT INTO clientes (apellido, nombre, dni, celular, email, fecha_alta, estado)
                VALUES (?, ?, ?, ?, NULL, NOW(), 1)
            ");
            $ins->execute([
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
        (fecha, total, observaciones, metodo_pago_idmetodo_pago, tipo_comprobante_idtipo_comprobante,
         clientes_idCliente, usuario_idusuario, moneda_idmoneda)
        VALUES (NOW(), ?, NULL, ?, ?, ?, ?, ?)
    ");

    $stmtVenta->execute([
        $total,
        $metodo_pago,
        $tipo_comprobante,
        $cliente_id,
        $usuario_id,
        $moneda
    ]);

    $venta_id = $conexion->lastInsertId();

    // === Insertar detalle ===
    $stmtDetalle = $conexion->prepare("
        INSERT INTO detalle_venta (ventas_idVenta, producto_idProducto, cantidad, precio_unitario)
        VALUES (?, ?, ?, ?)
    ");

    $stmtStock = $conexion->prepare("
        UPDATE stock_producto
        SET cantidad_actual = cantidad_actual - ?
        WHERE producto_idProducto = ?
    ");

    foreach ($productos as $p) {
        $idProd = intval($p['idProducto']);
        $cantidad = intval($p['cantidad']);
        $precio = floatval($p['precio_expuesto']);

        $stmtDetalle->execute([$venta_id, $idProd, $cantidad, $precio]);
        $stmtStock->execute([$cantidad, $idProd]);
    }

    // === Confirmar ===
    $conexion->commit();

    echo json_encode([
        'ok' => true,
        'msg' => 'Venta registrada correctamente',
        'venta_id' => $venta_id
    ]);

} catch (Exception $e) {

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    echo json_encode([
        'ok' => false,
        'msg' => $e->getMessage()
    ]);
}
