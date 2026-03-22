<?php
header('Content-Type: application/json');
session_start();

require_once '../conexion/conexion.php';
date_default_timezone_set('America/Argentina/Buenos_Aires');

/* =========================
   CONSTANTES
========================= */

define('CLIENTE_CONSUMIDOR_FINAL', 1);

define('METODO_EFECTIVO', 1);
define('METODO_TARJETA', 2);
define('METODO_TRANSFERENCIA', 3);
define('METODO_MERCADO_PAGO', 4);

define('MONEDA_GUARANI', 1);
define('MONEDA_ARS', 2);
define('MONEDA_USD', 3);

try {

    if (empty($_SESSION['idusuario'])) {
        throw new Exception("Sesión expirada o no iniciada.");
    }

    $usuario_id = (int)$_SESSION['idusuario'];

    /* =========================
       LEER JSON
    ========================= */

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || empty($data['productos'])) {
        throw new Exception("Datos de venta inválidos.");
    }

    $tipo_comprobante = (int)($data['tipo_comprobante'] ?? 0);
    $metodo_pago      = (int)($data['metodo_pago'] ?? METODO_EFECTIVO);
    $moneda           = (int)($data['moneda'] ?? MONEDA_GUARANI);
    $productos        = $data['productos'];
    $clienteData      = $data['cliente'] ?? null;

    /* =========================
       VALIDAR METODO DE PAGO
    ========================= */

    $metodosValidos = [
        METODO_EFECTIVO,
        METODO_TARJETA,
        METODO_TRANSFERENCIA,
        METODO_MERCADO_PAGO
    ];

    if (!in_array($metodo_pago, $metodosValidos)) {
        $metodo_pago = METODO_EFECTIVO;
    }

    /* =========================
       VALIDAR MONEDA
    ========================= */

    $monedasValidas = [
        MONEDA_GUARANI,
        MONEDA_ARS,
        MONEDA_USD
    ];

    if (!in_array($moneda, $monedasValidas)) {
        $moneda = MONEDA_GUARANI;
    }

    /* =========================
       SI NO ES EFECTIVO → GUARANI
    ========================= */

    if ($metodo_pago !== METODO_EFECTIVO) {
        $moneda = MONEDA_GUARANI;
    }

    $conexion->beginTransaction();

    /* =========================
       CLIENTE
    ========================= */

    $cliente_id = CLIENTE_CONSUMIDOR_FINAL;

    if ($clienteData && !empty($clienteData['dni'])) {

        $dni = trim($clienteData['dni']);

        $buscar = $conexion->prepare("
            SELECT idCliente, nombre, apellido, celular
            FROM clientes
            WHERE dni = ?
            LIMIT 1
        ");

        $buscar->execute([$dni]);
        $cli = $buscar->fetch(PDO::FETCH_ASSOC);

        if ($cli) {

            $cliente_id = $cli['idCliente'];

            if ($tipo_comprobante > 1) {

                $nombreNuevo   = trim($clienteData['nombre'] ?? '');
                $apellidoNuevo = trim($clienteData['apellido'] ?? '');
                $celularNuevo  = trim($clienteData['celular'] ?? '');

                $nombreFinal   = !empty($cli['nombre'])   ? $cli['nombre']   : $nombreNuevo;
                $apellidoFinal = !empty($cli['apellido']) ? $cli['apellido'] : $apellidoNuevo;
                $celularFinal  = !empty($cli['celular'])  ? $cli['celular']  : $celularNuevo;

                $updCliente = $conexion->prepare("
                    UPDATE clientes
                    SET nombre = ?, apellido = ?, celular = ?
                    WHERE idCliente = ?
                ");

                $updCliente->execute([
                    $nombreFinal,
                    $apellidoFinal,
                    $celularFinal,
                    $cliente_id
                ]);
            }

        } else {

            $ins = $conexion->prepare("
                INSERT INTO clientes (
                    apellido,
                    nombre,
                    dni,
                    celular,
                    email,
                    fecha_alta,
                    estado
                )
                VALUES (?, ?, ?, ?, NULL, NOW(), 1)
            ");

            $ins->execute([
                $clienteData['apellido'] ?? '',
                $clienteData['nombre'] ?? '',
                $dni,
                $clienteData['celular'] ?? ''
            ]);

            $cliente_id = $conexion->lastInsertId();
        }
    }

    /* =========================
       INSERTAR VENTA
    ========================= */

    $stmtVenta = $conexion->prepare("
        INSERT INTO ventas (
            fecha,
            total,
            observaciones,
            metodo_pago_idmetodo_pago,
            tipo_comprobante_idtipo_comprobante,
            clientes_idCliente,
            usuario_idusuario,
            moneda_idmoneda
        )
        VALUES (NOW(), 0, NULL, ?, ?, ?, ?, ?)
    ");

    $stmtVenta->execute([
        $metodo_pago,
        $tipo_comprobante,
        $cliente_id,
        $usuario_id,
        $moneda
    ]);

    $venta_id = $conexion->lastInsertId();

    /* =========================
       DETALLE DE VENTA
    ========================= */

    $stmtDetalle = $conexion->prepare("
        INSERT INTO detalle_venta (
            ventas_idVenta,
            producto_idProducto,
            cantidad,
            precio_base,
            porcentaje_descuento,
            precio_unitario,
            devuelto
        )
        VALUES (?, ?, ?, ?, ?, ?, 0)
    ");

    $totalVenta = 0;

    foreach ($productos as $p) {

        $idProd   = (int)$p['idProducto'];
        $cantidad = (int)$p['cantidad'];

        $precioBase = (float)$p['precio_base'];
        $precioUnit = (float)$p['precio_unitario'];
        $descuento  = (float)$p['porcentaje_descuento'];

        if ($precioUnit <= 0 || $cantidad <= 0) {
            throw new Exception("Precio o cantidad inválidos.");
        }

        $subtotal = round($precioUnit * $cantidad, 2);

        /* =========================
           CONTROL DE STOCK
        ========================= */

        $check = $conexion->prepare("
            SELECT cantidad_exhibida, cantidad_actual
            FROM stock_producto
            WHERE producto_idProducto = ?
            FOR UPDATE
        ");

        $check->execute([$idProd]);
        $stk = $check->fetch(PDO::FETCH_ASSOC);

        if (!$stk) {
            throw new Exception("Error de stock. Producto ID: $idProd");
        }

        $ex = (int)$stk['cantidad_exhibida'];
        $gr = (int)$stk['cantidad_actual'];

        if ($ex <= 0 && $gr <= 0) {
            throw new Exception("Sin stock disponible. Producto ID: $idProd");
        }

        if ($ex > 0) {

            $nuevoEx = max(0, $ex - $cantidad);
            $resto   = max(0, $cantidad - $ex);
            $nuevoGr = max(0, $gr - $resto);

        } else {

            $nuevoEx = 0;
            $nuevoGr = max(0, $gr - $cantidad);
        }

        if ($nuevoEx < 0 || $nuevoGr < 0) {
            throw new Exception("Stock negativo no permitido.");
        }

        $upd = $conexion->prepare("
            UPDATE stock_producto
            SET cantidad_exhibida = ?, cantidad_actual = ?
            WHERE producto_idProducto = ?
        ");

        $upd->execute([$nuevoEx, $nuevoGr, $idProd]);

        /* =========================
           INSERT DETALLE
        ========================= */

        $stmtDetalle->execute([
            $venta_id,
            $idProd,
            $cantidad,
            $precioBase,
            $descuento,
            $precioUnit
        ]);

        $totalVenta += $subtotal;
    }

    /* =========================
       ACTUALIZAR TOTAL
    ========================= */

    $updTotal = $conexion->prepare("
        UPDATE ventas
        SET total = ?
        WHERE idVenta = ?
    ");

    $updTotal->execute([$totalVenta, $venta_id]);

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