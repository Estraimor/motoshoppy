<?php
require_once __DIR__ . '/../conexion/conexion.php';

// Recibimos el ID del proveedor a dar de baja
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    // Cambiamos el estado de 'activo' a 0 (inactivo)
    $sql = "UPDATE proveedores 
            SET activo = IF(activo = 1, 0, 1)
            WHERE idproveedores = :id";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id]);

    // Redirigimos con mensaje de cambio de estado
    header('Location: index.php?msg=estado');
    exit;
}
?>
