<?php
require_once '../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conexion->prepare("INSERT INTO categoria (nombre_categoria, descripcion, estado) VALUES (:nombre, :descripcion, :estado)");
    $stmt->execute([
        ':nombre' => $_POST['nombre_categoria'],
        ':descripcion' => $_POST['descripcion'],
        ':estado' => $_POST['estado']
    ]);
    header("Location: index.php");
    exit;
}
?>
