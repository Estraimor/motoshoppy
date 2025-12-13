<?php
require_once __DIR__ . '/../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {

    // Recibimos los datos del formulario
    $empresa   = trim($_POST['empresa']);
    $ubicacion = trim($_POST['ubicacion']);
    $telefono  = trim($_POST['telefono']);
    $email     = trim($_POST['email']);

    // Insertamos el proveedor en la base de datos
    $sql = "INSERT INTO proveedores (empresa, ubicacion, telefono, email, activo) 
            VALUES (:empresa, :ubicacion, :telefono, :email, 1)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':empresa'   => $empresa,
        ':ubicacion' => $ubicacion,
        ':telefono'  => $telefono,
        ':email'     => $email
    ]);

    // Redirigimos con un mensaje de éxito
    header('Location: index.php?msg=insertado');
    exit;
}
?>
