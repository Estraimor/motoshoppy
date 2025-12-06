<?php
require_once "../conexion/conexion.php";

$result = [];

// Métodos de pago
$stmt = $conexion->query("SELECT idmetodo_pago AS id, nombre FROM metodo_pago ORDER BY idmetodo_pago ASC");
$result['metodos_pago'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tipos de comprobante
$stmt = $conexion->query("SELECT idtipo_comprobante AS id, nombre FROM tipo_comprobante ORDER BY idtipo_comprobante ASC");
$result['comprobantes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monedas (PYG, ARS, USD)
$stmt = $conexion->query("SELECT idmoneda AS id, codigo, nombre FROM moneda ORDER BY idmoneda ASC");
$result['monedas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);
