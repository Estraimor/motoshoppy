<?php
require_once '../conexion/conexion.php';

if (isset($_GET['id'])) {
    $stmt = $conexion->prepare("DELETE FROM categoria WHERE idCategoria=:id");
    $stmt->execute([':id' => $_GET['id']]);
}
header("Location: index.php");
exit;
?>
