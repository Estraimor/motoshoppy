<?php
session_start();
require '../conexion/conexion.php';

if (!isset($_POST['id'])) {
    echo "ID inválido";
    exit;
}

$id = intval($_POST['id']);

/* ==============================
   DATOS PRINCIPALES
============================== */

$codigo  = $_POST['codigo'] ?? '';
$nombre  = $_POST['nombre'] ?? '';
$modelo  = $_POST['modelo'] ?? '';
$marca   = $_POST['marca'] !== '' ? $_POST['marca'] : null;
$precio_expuesto = floatval($_POST['precio'] ?? 0);
$peso_ml = $_POST['peso_ml'] !== '' ? $_POST['peso_ml'] : null;
$peso_g  = $_POST['peso_g'] !== '' ? $_POST['peso_g'] : null;

/* ==============================
   PRECIO COSTO (solo admin)
============================== */

$extraCostoSQL = "";
$precio_costo = null;

if (isset($_SESSION['rol']) && $_SESSION['rol'] == 1) {
    $precio_costo = $_POST['precio_costo'] !== '' ? $_POST['precio_costo'] : null;
    $extraCostoSQL = ", precio_costo = :precio_costo";
}

/* ==============================
   JSON ATRIBUTOS ADICIONALES
============================== */

$descripcion = null;

if (isset($_POST['atributos_json'])) {

    $jsonRecibido = json_decode($_POST['atributos_json'], true);

    if (is_array($jsonRecibido)) {
        $descripcion = json_encode($jsonRecibido, JSON_UNESCAPED_UNICODE);
    }
}

/* ==============================
   UPDATE PRODUCTO
============================== */

$sql = $conexion->prepare("
    UPDATE producto
    SET codigo = :codigo,
        nombre = :nombre,
        modelo = :modelo,
        marcas_idmarcas = :marca,
        precio_expuesto = :precio_expuesto
        $extraCostoSQL,
        peso_ml = :peso_ml,
        peso_g = :peso_g,
        descripcion = COALESCE(:descripcion, descripcion)
    WHERE idproducto = :id
");

$sql->bindValue(':codigo', $codigo);
$sql->bindValue(':nombre', $nombre);
$sql->bindValue(':modelo', $modelo);
$sql->bindValue(':marca', $marca);
$sql->bindValue(':precio_expuesto', $precio_expuesto);
$sql->bindValue(':peso_ml', $peso_ml);
$sql->bindValue(':peso_g', $peso_g);
$sql->bindValue(':descripcion', $descripcion);

if ($precio_costo !== null) {
    $sql->bindValue(':precio_costo', $precio_costo);
}

$sql->bindValue(':id', $id);

$sql->execute();

/* ==============================
   ATRIBUTOS DE CUBIERTA
============================== */

if (isset($_POST['aro'])) {

    $aro    = $_POST['aro'] ?? null;
    $ancho  = $_POST['ancho'] ?? null;
    $perfil = $_POST['perfil'] ?? null;
    $tipo   = $_POST['tipo'] ?? null;
    $aplic  = $_POST['varias'] ?? null;

    $check = $conexion->prepare("SELECT idatributos_cubiertas FROM atributos_cubiertas WHERE producto_idProducto = ?");
    $check->execute([$id]);

    if ($check->fetch()) {

        $upd = $conexion->prepare("
            UPDATE atributos_cubiertas
            SET aro=?, ancho=?, perfil_cubierta=?, tipo=?, varias_aplicaciones=?
            WHERE producto_idProducto=?
        ");
        $upd->execute([$aro, $ancho, $perfil, $tipo, $aplic, $id]);

    } else {

        $ins = $conexion->prepare("
            INSERT INTO atributos_cubiertas 
            (producto_idProducto, aro, ancho, perfil_cubierta, tipo, varias_aplicaciones)
            VALUES (?,?,?,?,?,?)
        ");
        $ins->execute([$id, $aro, $ancho, $perfil, $tipo, $aplic]);
    }
}

/* ==============================
   UPDATE STOCK
============================== */

if (isset($_POST['stock_minimo'])) {

    $stock_minimo      = intval($_POST['stock_minimo']);
    $cantidad_actual   = intval($_POST['cantidad_actual']);
    $cantidad_exhibida = intval($_POST['cantidad_exhibida']);

    $check = $conexion->prepare("SELECT idstock_producto FROM stock_producto WHERE producto_idProducto = ?");
    $check->execute([$id]);

    if ($check->fetch()) {

        $upd = $conexion->prepare("
            UPDATE stock_producto
            SET stock_minimo=?, cantidad_actual=?, cantidad_exhibida=?
            WHERE producto_idProducto=?
        ");
        $upd->execute([$stock_minimo, $cantidad_actual, $cantidad_exhibida, $id]);

    } else {

        $ins = $conexion->prepare("
            INSERT INTO stock_producto 
            (producto_idProducto, stock_minimo, cantidad_actual, cantidad_exhibida)
            VALUES (?,?,?,?)
        ");
        $ins->execute([$id, $stock_minimo, $cantidad_actual, $cantidad_exhibida]);
    }
}

/* ==============================
   NUEVA IMAGEN
============================== */

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {

    $directorio = "../uploads/productos/";

    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

    $nombreArchivo = "prod_" . $codigo . "_" . time() . "." . $ext;

    $rutaDestino = $directorio . $nombreArchivo;

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {

        $rutaBD = "uploads/productos/" . $nombreArchivo;

        $upd = $conexion->prepare("UPDATE producto SET imagen=? WHERE idproducto=?");
        $upd->execute([$rutaBD, $id]);
    }
}

echo "OK";
?>
