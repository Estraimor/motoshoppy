<?php
require_once '../conexion/conexion.php';
session_start();

// ============================================
// 1) Recibir datos
// ============================================
$idVenta = $_POST['idVenta'];
$items   = json_decode($_POST['items'], true);
$motivo  = $_POST['motivo'] ?? '';
$usuario = $_SESSION['idusuario'];

if (!$idVenta || !is_array($items)) {
    echo "Error: Datos invalidos";
    exit;
}

$procesados = 0;

// ============================================
// 2) Procesar cada producto seleccionado
// ============================================
foreach ($items as $it) {

    if (!isset($it['producto_id']) || !isset($it['cantidad'])) {
        continue;
    }

    $idDetalle  = $it['idDetalle'];
    $productoId = $it['producto_id'];
    $cantidad   = $it['cantidad'];

    // -----------------------------------------------------
    // A) ¿Ya existe una devolución de este producto?
    // -----------------------------------------------------
    $check = $conexion->prepare("
        SELECT COUNT(*) 
        FROM devoluciones_venta 
        WHERE venta_id = ? AND producto_id = ?
    ");
    $check->execute([$idVenta, $productoId]);
    
    if ($check->fetchColumn() > 0) {
        continue; // Ya devuelto → saltar
    }

    // -----------------------------------------------------
    // Registrar devolución
    // -----------------------------------------------------
    $sql = "INSERT INTO devoluciones_venta 
            (venta_id, producto_id, cantidad, usuario_id, motivo)
            VALUES (?,?,?,?,?)";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$idVenta, $productoId, $cantidad, $usuario, $motivo]);

    // -----------------------------------------------------
    // B) Aumentar stock
    // -----------------------------------------------------
    $upd = $conexion->prepare("
        UPDATE stock_producto 
        SET cantidad_actual = cantidad_actual + ?, 
            cantidad_exhibida = cantidad_exhibida + ?
        WHERE producto_idProducto = ?
    ");
    $upd->execute([$cantidad, $cantidad, $productoId]);

    // -----------------------------------------------------
    // C) NO descontar más cantidad del detalle
    // -----------------------------------------------------
    $upd2 = $conexion->prepare("
        UPDATE detalle_venta 
        SET devuelto = 1
        WHERE idDetalle = ?
    ");
    $upd2->execute([$idDetalle]);

    $procesados++;
}

// ============================================
// 3) Si no procesó ninguno
// ============================================
if ($procesados == 0) {
    echo "no_hay_productos"; 
    exit;
}

// ============================================
// 4) ¿Venta totalmente devuelta?
// ============================================
$sqlCheck = $conexion->prepare("
    SELECT COUNT(*) 
    FROM detalle_venta
    WHERE venta_id = ? AND devuelto = 0
");
$sqlCheck->execute([$idVenta]);
$restantes = $sqlCheck->fetchColumn();

if ($restantes == 0) {
    echo "completa";
    exit;
}

// ============================================
// 5) OK
// ============================================
echo "ok";
