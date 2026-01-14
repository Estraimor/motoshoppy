<?php
require_once __DIR__ . '/../conexion/conexion.php';

// Cargamos los datos del proveedor si existe un ID
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conexion->prepare("SELECT * FROM proveedores WHERE idproveedores = :id");
    $stmt->execute([':id' => $id]);
    $proveedor = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $id        = $_POST['idproveedores'];
    $empresa   = trim($_POST['empresa']);
    $ubicacion = trim($_POST['ubicacion']);
    $telefono  = trim($_POST['telefono']);
    $email     = trim($_POST['email']);
    $vendedor        = trim($_POST['vendedor']);
    $numero_vendedor = trim($_POST['numero_vendedor']); 

    $sql = "UPDATE proveedores 
        SET empresa = :empresa,
            ubicacion = :ubicacion,
            telefono = :telefono,
            email = :email,
            vendedor = :vendedor,
            numero_vendedor = :numero_vendedor
        WHERE idproveedores = :id";

$stmt = $conexion->prepare($sql);
$stmt->execute([
    ':empresa'         => $empresa,
    ':ubicacion'       => $ubicacion,
    ':telefono'        => $telefono,
    ':email'           => $email,
    ':vendedor'        => $vendedor,
    ':numero_vendedor' => $numero_vendedor,
    ':id'              => $id
]);

    // Redirigimos con un mensaje de éxito
    header('Location: index.php?msg=actualizado');
    exit;
}
?>
