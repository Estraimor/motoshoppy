<?php
session_start();
require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/../settings/auditoria.php';

// Recibimos el ID del proveedor a dar de baja
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];

    $antesStmt = $conexion->prepare("SELECT activo, empresa FROM proveedores WHERE idproveedores = :id");
    $antesStmt->execute([':id' => $id]);
    $antes = $antesStmt->fetch(PDO::FETCH_ASSOC);

    // Cambiamos el estado de 'activo' a 0 (inactivo)
    $sql = "UPDATE proveedores
            SET activo = IF(activo = 1, 0, 1)
            WHERE idproveedores = :id";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id]);

    if ($antes) {
        $nuevoEstado = $antes['activo'] == 1 ? 0 : 1;
        auditoria(
            $conexion,
            'UPDATE',
            'proveedores',
            'proveedores',
            $id,
            ($nuevoEstado ? "Activó" : "Desactivó") . " proveedor: " . $antes['empresa'],
            ['activo' => $antes['activo']],
            ['activo' => $nuevoEstado]
        );
    }

    // Redirigimos con mensaje de cambio de estado
    header('Location: index.php?msg=estado');
    exit;
}
?>
