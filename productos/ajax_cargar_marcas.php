<?php
require '../conexion/conexion.php';

$sql = $conexion->query("SELECT idmarcas, nombre_marca FROM marcas WHERE estado = 1 ORDER BY nombre_marca");

while($m = $sql->fetch(PDO::FETCH_ASSOC)){
    echo "<option value='{$m['idmarcas']}'>{$m['nombre_marca']}</option>";
}
