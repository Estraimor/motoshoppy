<?php

require_once '../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria_id     = intval($_POST['categoria_id'] ?? 0);
$marca_id = !empty($_POST['marcas_idmarcas']) ? intval($_POST['marcas_idmarcas']) : null;
    $codigo           = trim($_POST['codigo'] ?? '');
    $nombre           = trim($_POST['nombre'] ?? '');
    $modelo           = trim($_POST['modelo'] ?? '');
    $precio_costo     = $_POST['precio_costo'] !== '' ? floatval($_POST['precio_costo']) : null;
    $precio_expuesto  = $_POST['precio_expuesto'] !== '' ? floatval($_POST['precio_expuesto']) : null;

    $peso_ml = isset($_POST['peso_ml']) && $_POST['peso_ml'] !== '' ? intval($_POST['peso_ml']) : null;
    $peso_g  = isset($_POST['peso_g']) && $_POST['peso_g'] !== '' ? intval($_POST['peso_g']) : null;
    if (isset($_POST['sin_peso'])) {
        $peso_ml = null;
        $peso_g  = null;
    }

    // Campos de stock
    $stock_minimo    = isset($_POST['stock_minimo']) ? intval($_POST['stock_minimo']) : 0;
    $cantidad_actual = isset($_POST['cantidad_actual']) ? intval($_POST['cantidad_actual']) : 0;
    $cantidad_exhibida = isset($_POST['cantidad_exhibida']) ? intval($_POST['cantidad_exhibida']) : 0;

    // Ubicación
    $ubicacion_id = null;
    if (!empty($_POST['ubicacion_id'])) {
        $ubicacion_id = intval($_POST['ubicacion_id']);
    } elseif (!empty($_POST['nueva_ubicacion'])) {
        $partes = explode('-', $_POST['nueva_ubicacion'], 2);
        $lugar = trim($partes[0] ?? '');
        $estante = trim($partes[1] ?? '');
        if ($lugar) {
            $stmtUb = $conexion->prepare("INSERT INTO ubicacion_producto (lugar, estante) VALUES (:lugar, :estante)");
            $stmtUb->execute([
                ':lugar' => $lugar,
                ':estante' => $estante
            ]);
            $ubicacion_id = $conexion->lastInsertId();
        }
    }

    // Validación mínima
    if ($categoria_id <= 0 || !$nombre) {
        header("Location: index.php?error=CamposObligatorios");
        exit;
    }

    // --- Procesar JSON ---
    $json_data = [];
    if (!empty($_POST['json']) && is_array($_POST['json'])) {
        foreach ($_POST['json'] as $clave => $valor) {
            $enabled = $_POST['json_enabled'][$clave] ?? "0";
            if ($enabled == "1" && trim($clave) !== '' && trim($valor) !== '') {
                $json_data[$clave] = $valor;
            }
        }
    }
    if (!empty($_POST['json_new_keys']) && is_array($_POST['json_new_keys'])) {
        foreach ($_POST['json_new_keys'] as $i => $clave) {
            $valor   = $_POST['json_new_values'][$i] ?? '';
            $enabled = $_POST['json_new_enabled'][$i] ?? "0";
            if ($enabled == "1" && trim($clave) !== '' && trim($valor) !== '') {
                $json_data[$clave] = $valor;
            }
        }
    }
    $descripcion_json = !empty($json_data) ? json_encode($json_data, JSON_UNESCAPED_UNICODE) : null;

    // --- Procesar imagen ---
    $ruta_imagen = null;
    if (!empty($_FILES['imagen']['name'])) {
        $directorio = "../uploads/productos/";
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = "prod_" . ($codigo ?: uniqid()) . "_" . time() . "." . strtolower($ext);
        $ruta_destino = $directorio . $nombre_archivo;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $ruta_imagen = "uploads/productos/" . $nombre_archivo;
        }
    }

    try {
        $conexion->beginTransaction();

        // === Insertar producto ===
        $stmt = $conexion->prepare("
            INSERT INTO producto 
                (Categoria_idCategoria, marcas_idmarcas, codigo, nombre, modelo, 
                 precio_costo, precio_expuesto, peso_ml, peso_g, 
                 descripcion, ubicacion_producto_idubicacion_producto, imagen) 
            VALUES 
                (:categoria, :marca, :codigo, :nombre, :modelo, 
                 :precio_costo, :precio_expuesto, :peso_ml, :peso_g, 
                 :descripcion, :ubicacion, :imagen)
        ");
        $stmt->execute([
            ':categoria'       => $categoria_id,
            ':marca'           => $marca_id,
            ':codigo'          => $codigo,
            ':nombre'          => $nombre,
            ':modelo'          => $modelo,
            ':precio_costo'    => $precio_costo,
            ':precio_expuesto' => $precio_expuesto,
            ':peso_ml'         => $peso_ml,
            ':peso_g'          => $peso_g,
            ':descripcion'     => $descripcion_json,
            ':ubicacion'       => $ubicacion_id,
            ':imagen'          => $ruta_imagen
        ]);

        $idProducto = $conexion->lastInsertId();

        // === Insertar stock ===
        $stmtStock = $conexion->prepare("
            INSERT INTO stock_producto (producto_idProducto, stock_minimo, cantidad_actual, cantidad_exhibida)
            VALUES (:idProducto, :stock_minimo, :cantidad_actual, :cantidad_exhibida)
        ");
        $stmtStock->execute([
            ':idProducto' => $idProducto,
            ':stock_minimo' => $stock_minimo,
            ':cantidad_actual' => $cantidad_actual,
            ':cantidad_exhibida' => $cantidad_exhibida
        ]);

        $conexion->commit();

        header("Location: alta_productos.php?msg=ProductoCreado");
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack();
        die("❌ Error al guardar producto: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
