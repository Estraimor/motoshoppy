<?php
require_once __DIR__ . '/../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {

    // Recibimos los datos del formulario
    $empresa   = trim($_POST['empresa']);
    $ubicacion = trim($_POST['ubicacion']);
    $telefono  = trim($_POST['telefono']);
    $email     = trim($_POST['email']);
    $vendedor        = trim($_POST['vendedor']);
    $numero_vendedor = trim($_POST['numero_vendedor']); 

    // Insertamos el proveedor en la base de datos
    $sql = "INSERT INTO proveedores (empresa, ubicacion, telefono, email, vendedor, numero_vendedor, activo) 
            VALUES (:empresa, :ubicacion, :telefono, :email, :vendedor, :numero_vendedor, 1)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':empresa'   => $empresa,
        ':ubicacion' => $ubicacion,
        ':telefono'  => $telefono,
        ':email'     => $email,
        ':vendedor'        => $vendedor,
        ':numero_vendedor' => $numero_vendedor
    ]);

    // Redirigimos con un mensaje de éxito
    header('Location: index.php?msg=insertado');
    exit;
}
?>
