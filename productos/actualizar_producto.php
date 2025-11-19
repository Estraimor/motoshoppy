<?php
session_start();
require '../conexion/conexion.php';

if (!isset($_POST['id'])) {
    echo "ID inválido";
    exit;
}

$id = intval($_POST['id']);

// ==============================
//   DATOS PRINCIPALES
// ==============================
$codigo  = $_POST['codigo'] ?? '';
$nombre  = $_POST['nombre'] ?? '';
$modelo  = $_POST['modelo'] ?? '';
$marca   = $_POST['marca'] ?: null;
$precio_expuesto = floatval($_POST['precio'] ?? 0);
$peso_ml = $_POST['peso_ml'] ?: null;
$peso_g  = $_POST['peso_g'] ?: null;

// ==============================
//   PRECIO COSTO (solo admin = rol 1)
// ==============================
$extraCostoSQL = "";
if (isset($_SESSION['rol']) && $_SESSION['rol'] == 1) {
    $precio_costo = $_POST['precio_costo'] ?? null;
    $extraCostoSQL = ", precio_costo = :precio_costo";
}

// ==============================
//  JSON de atributos normales
// ==============================
$descripcion = "{}";

if (isset($_POST['atributos_clave']) && isset($_POST['atributos_valor'])) {

    $jsonFinal = [];

    foreach ($_POST['atributos_clave'] as $i => $clave) {

        $clave = trim($clave);
        $valor = trim($_POST['atributos_valor'][$i]);

        if ($clave !== "" && $valor !== "") {
            $jsonFinal[$clave] = $valor;
        }
    }

    if (!empty($jsonFinal)) {
        $descripcion = json_encode($jsonFinal, JSON_UNESCAPED_UNICODE);
    }
}

// ==============================
//   UPDATE PRODUCTO
// ==============================
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
        descripcion = :descripcion
    WHERE idproducto = :id
");

$sql->bindParam(':codigo', $codigo);
$sql->bindParam(':nombre', $nombre);
$sql->bindParam(':modelo', $modelo);
$sql->bindParam(':marca', $marca);
$sql->bindParam(':precio_expuesto', $precio_expuesto);
$sql->bindParam(':peso_ml', $peso_ml);
$sql->bindParam(':peso_g', $peso_g);
$sql->bindParam(':descripcion', $descripcion);

if (isset($precio_costo)) {
    $sql->bindParam(':precio_costo', $precio_costo);
}

$sql->bindParam(':id', $id);
$sql->execute();

// ==============================
//   ATRIBUTOS DE CUBIERTA
// ==============================
if (isset($_POST['aro'])) {

    $aro    = $_POST['aro'] ?? null;
    $ancho  = $_POST['ancho'] ?? null;
    $perfil = $_POST['perfil'] ?? null;
    $tipo   = $_POST['tipo'] ?? null;
    $aplic  = $_POST['varias'] ?? null;

    // ¿Existe?
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

// ==============================
//   UPDATE STOCK
// ==============================
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
            INSERT INTO stock_producto (producto_idProducto, stock_minimo, cantidad_actual, cantidad_exhibida)
            VALUES (?,?,?,?)
        ");
        $ins->execute([$id, $stock_minimo, $cantidad_actual, $cantidad_exhibida]);
    }
}

// ==============================
//   NUEVA IMAGEN (uploads/productos/...)
// ==============================
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
