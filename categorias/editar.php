<?php
require_once '../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conexion->prepare("UPDATE categoria SET nombre_categoria=:nombre, descripcion=:descripcion, estado=:estado WHERE idCategoria=:id");
    $stmt->execute([
        ':nombre' => $_POST['nombre_categoria'],
        ':descripcion' => $_POST['descripcion'],
        ':estado' => $_POST['estado'],
        ':id' => $_POST['idCategoria']
    ]);
    header("Location: index.php");
    exit;
}
?>
