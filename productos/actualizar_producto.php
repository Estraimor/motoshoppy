<?php
session_start();
require '../conexion/conexion.php';

$id = $_POST['idproducto'] ?? null;
if(!$id){
    echo "ID inválido";
    exit;
}

// === DATOS GENERALES DEL PRODUCTO ===
$codigo = $_POST['codigo'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$modelo = $_POST['modelo'] ?? '';
$marca = $_POST['marca'] ?? null;
$categoria = $_POST['categoria'] ?? null;
$precio_expuesto = $_POST['precio_expuesto'] ?? 0;
$peso_ml = $_POST['peso_(ml)'] ?? null;
$peso_g = $_POST['peso_(g)'] ?? null;

// === DESCRIPCIÓN (solo productos normales) ===
$descripcion = $_POST['descripcion'] ?? '{}';

// === SOLO ADMIN puede editar precio de costo ===
$extraCosto = "";
if(($_SESSION['rol'] ?? 0) == 1){
    $precio_costo = $_POST['precio_costo'] ?? null;
    $extraCosto = ", precio_costo = :precio_costo";
}

// === UPDATE PRODUCTO ===
$sql = $conexion->prepare("
    UPDATE producto
    SET codigo=:codigo, nombre=:nombre, modelo=:modelo,
        marcas_idmarcas=:marca, Categoria_idCategoria=:categoria,
        precio_expuesto=:precio_expuesto $extraCosto,
        peso_ml=:peso_ml, peso_g=:peso_g,
        descripcion=:descripcion
    WHERE idproducto=:id
");

$sql->bindParam(':codigo',$codigo);
$sql->bindParam(':nombre',$nombre);
$sql->bindParam(':modelo',$modelo);
$sql->bindParam(':marca',$marca);
$sql->bindParam(':categoria',$categoria);
$sql->bindParam(':precio_expuesto',$precio_expuesto);
$sql->bindParam(':peso_ml',$peso_ml);
$sql->bindParam(':peso_g',$peso_g);
$sql->bindParam(':descripcion',$descripcion);
if(isset($precio_costo)) $sql->bindParam(':precio_costo',$precio_costo);
$sql->bindParam(':id',$id);

$sql->execute();


// =================================================
// === PRODUCTO TIPO CUBIERTA (si tiene aro) ========
// =================================================
if(isset($_POST['aro'])){

    $aro = $_POST['aro'] ?? null;
    $ancho = $_POST['ancho'] ?? null;
    $perfil = $_POST['perfil_cubierta'] ?? null;
    $tipo = $_POST['tipo'] ?? null;
    $aplic = $_POST['varias_aplicaciones'] ?? null;

    // ¿Ya existe el registro?
    $check = $conexion->prepare("SELECT idatributos_cubiertas FROM atributos_cubiertas WHERE producto_idProducto = ?");
    $check->execute([$id]);

    if($check->fetch()){
        // UPDATE
        $upd = $conexion->prepare("
            UPDATE atributos_cubiertas
            SET aro=?, ancho=?, perfil_cubierta=?, tipo=?, varias_aplicaciones=?
            WHERE producto_idProducto=?
        ");
        $upd->execute([$aro, $ancho, $perfil, $tipo, $aplic, $id]);

    } else {
        // INSERT
        $ins = $conexion->prepare("
            INSERT INTO atributos_cubiertas (producto_idProducto, aro, ancho, perfil_cubierta, tipo, varias_aplicaciones)
            VALUES (?,?,?,?,?,?)
        ");
        $ins->execute([$id, $aro, $ancho, $perfil, $tipo, $aplic]);
    }
}


// =================================================
// === IMAGEN NUEVA (si se subió) ==================
// =================================================
if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0){
    $dest = "img/productos/$id.jpg";
    move_uploaded_file($_FILES['imagen']['tmp_name'], "../".$dest);
    $conexion->query("UPDATE producto SET imagen='$dest' WHERE idproducto=$id");
}

echo "OK";
