<?php
include '../conexion/conexion.php';
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $sql = $conexion->prepare("DELETE FROM producto WHERE idproducto = ?");
    $ok = $sql->execute([$id]);
    echo $ok ? "OK" : "ERROR";
}
?>
