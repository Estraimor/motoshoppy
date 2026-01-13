<?php
header('Content-Type: application/json');
session_start();

require_once '../conexion/conexion.php';
date_default_timezone_set('America/Argentina/Buenos_Aires');

/* =========================
   CONSTANTES DEL SISTEMA
========================= */
define('CLIENTE_CONSUMIDOR_FINAL', 1);

define('METODO_EFECTIVO', 1);
define('METODO_TARJETA', 2);

define('MONEDA_GUARANI', 1);
define('MONEDA_ARS', 2);
define('MONEDA_USD', 3);

try {

    if (empty($_SESSION['idusuario'])) {
        throw new Exception("Sesión expirada o no iniciada.");
    }

    $usuario_id = intval($_SESSION['idusuario']);

    /* =========================
       LEER JSON
    ========================= */
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || empty($data['productos'])) {
        throw new Exception("Datos de venta inválidos.");
    }

    /* =========================
       DATOS PRINCIPALES
    ========================= */
    $tipo_comprobante = intval($data['tipo_comprobante'] ?? 0);
    $metodo_pago = intval($data['metodo_pago'] ?? METODO_EFECTIVO);
    $moneda = intval($data['moneda'] ?? MONEDA_GUARANI);
    $productos = $data['productos'];
    $total = floatval($data['total'] ?? 0);
    $clienteData = $data['cliente'] ?? null;

    /* =========================
       REGLA CLAVE: TARJETA → GUARANÍ
    ========================= */
    if ($metodo_pago === METODO_TARJETA) {
        $moneda = MONEDA_GUARANI;
    }

    /* =========================
       INICIAR TRANSACCIÓN
    ========================= */
    $conexion->beginTransaction();

    /* =========================
       CLIENTE
    ========================= */
    $cliente_id = CLIENTE_CONSUMIDOR_FINAL;

    if ($tipo_comprobante > 1 && $clienteData) {

        $dni = trim($clienteData['dni']);

        $buscar = $conexion->prepare("
            SELECT idCliente 
            FROM clientes 
            WHERE dni = ? 
            LIMIT 1
        ");
        $buscar->execute([$dni]);
        $cli = $buscar->fetch(PDO::FETCH_ASSOC);

        if ($cli) {
            $cliente_id = $cli['idCliente'];
        } else {
            $ins = $conexion->prepare("
                INSERT INTO clientes (
                    apellido, nombre, dni, celular, email, fecha_alta, estado
                )
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

    /* =========================
       DETALLE DE VENTA
    ========================= */
    $stmtDetalle = $conexion->prepare("
        INSERT INTO detalle_venta (
            ventas_idVenta,
            producto_idProducto,
            cantidad,
            precio_unitario
        )
        VALUES (?, ?, ?, ?)
    ");

    foreach ($productos as $p) {

        $idProd = intval($p['idProducto']);
        $cantidad = intval($p['cantidad']);
        $precio = floatval($p['precio_expuesto']);

        /* ===== CONTROL DE STOCK ===== */
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
            $resto = max(0, $cantidad - $ex);
            $nuevoGr = max(0, $gr - $resto);
        } else {
            $nuevoEx = 0;
            $nuevoGr = max(0, $gr - $cantidad);
        }

        if ($nuevoEx < 0 || $nuevoGr < 0) {
            throw new Exception("Stock negativo no permitido. Producto ID: $idProd");
        }

        $upd = $conexion->prepare("
            UPDATE stock_producto
            SET cantidad_exhibida = ?, cantidad_actual = ?
            WHERE producto_idProducto = ?
        ");
        $upd->execute([$nuevoEx, $nuevoGr, $idProd]);

        $stmtDetalle->execute([
            $venta_id,
            $idProd,
            $cantidad,
            $precio
        ]);
    }

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
