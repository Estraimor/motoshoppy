<?php
require '../conexion/conexion.php';

$sql = $conexion->query("SELECT idCategoria, nombre_categoria FROM categoria ORDER BY nombre_categoria");

while($c = $sql->fetch(PDO::FETCH_ASSOC)){
    echo "<option value='{$c['idCategoria']}'>{$c['nombre_categoria']}</option>";
}
