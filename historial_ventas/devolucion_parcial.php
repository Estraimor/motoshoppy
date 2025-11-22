<?php
require_once '../conexion/conexion.php';
session_start();

// ============================================
// DEBUG (solo activar si necesitás ver qué llega)
// ============================================
// echo "<pre>POST RECIBIDO: "; print_r($_POST);
// echo "JSON DE ITEMS: "; print_r(json_decode($_POST['items'], true));
// exit;

// ============================================
// 1) Recibir datos
// ============================================
$idVenta = $_POST['idVenta'];
$items   = json_decode($_POST['items'], true);
$motivo  = $_POST['motivo'] ?? '';
$usuario = $_SESSION['idusuario'];

// Seguridad mínima
if (!$idVenta || !is_array($items)) {
    echo "Error: Datos inválidos";
    exit;
}

// ============================================
// 2) Procesar cada producto seleccionado
// ============================================
foreach ($items as $it) {

    // Validación
    if (!isset($it['producto_id']) || !isset($it['cantidad'])) {
        continue;
    }

    $idDetalle  = $it['idDetalle'];
    $productoId = $it['producto_id'];
    $cantidad   = $it['cantidad'];

    // -----------------------------------------------------
    // A) Registrar devolución
    // -----------------------------------------------------
    $sql = "INSERT INTO devoluciones_venta 
            (venta_id, producto_id, cantidad, usuario_id, motivo)
            VALUES (?,?,?,?,?)";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$idVenta, $productoId, $cantidad, $usuario, $motivo]);

    // -----------------------------------------------------
    // B) Actualizar stock
    // -----------------------------------------------------
    $upd = $conexion->prepare("
        UPDATE stock_producto 
        SET cantidad_actual = cantidad_actual + ?, 
            cantidad_exhibida = cantidad_exhibida + ?
        WHERE producto_idProducto = ?
    ");
    $upd->execute([$cantidad, $cantidad, $productoId]);

    // -----------------------------------------------------
    // C) Actualizar detalle de venta
    // -----------------------------------------------------
    $upd2 = $conexion->prepare("
        UPDATE detalle_venta 
        SET cantidad = cantidad - ?, devuelto = 1
        WHERE idDetalle = ?
    ");
    $upd2->execute([$cantidad, $idDetalle]);
}

// ============================================
// 3) ¿La venta quedó en cero?
// ============================================
$sqlCheck = $conexion->prepare("
    SELECT SUM(cantidad) AS totalRestante
    FROM detalle_venta
    WHERE venta_id = ?
");
$sqlCheck->execute([$idVenta]);
$totalRestante = $sqlCheck->fetchColumn();

if ($totalRestante == 0) {
    echo "completa";   // <-- esto detona "Venta cancelada completamente"
    exit;
}

// ============================================
// 4) Todo bien
// ============================================
echo "ok";
