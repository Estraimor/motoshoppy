<?php
require_once __DIR__ . '/../../../conexion/conexion.php';
require_once __DIR__ . '/../../../settings/auditoria.php'; // <-- ajustá si tu ruta cambia

header('Content-Type: application/json; charset=utf-8');

try {

    $id         = $_POST['id'] ?? null;
    $nombre     = trim($_POST['nombre_lista'] ?? '');
    $porcentaje = $_POST['porcentaje_descuento'] ?? '';
    $activo     = isset($_POST['activo']) ? 1 : 0;

    if ($nombre === '' || $porcentaje === '') {
        echo json_encode(["ok" => false, "msg" => "Complete nombre y porcentaje"]);
        exit;
    }

    // normalizar porcentaje (por si viene con coma)
    $porcentaje = str_replace(',', '.', $porcentaje);

    // ========= UPDATE =========
    if ($id) {

        // ANTES
        $stmtAntes = $conexion->prepare("SELECT * FROM precio_lista WHERE id = ? LIMIT 1");
        $stmtAntes->execute([$id]);
        $antes = $stmtAntes->fetch(PDO::FETCH_ASSOC);

        if (!$antes) {
            echo json_encode(["ok" => false, "msg" => "No existe el descuento"]);
            exit;
        }

        // UPDATE
        $stmt = $conexion->prepare("
            UPDATE precio_lista
            SET nombre_lista = ?, porcentaje_descuento = ?, activo = ?
            WHERE id = ?
        ");
        $stmt->execute([$nombre, $porcentaje, $activo, $id]);

        // DESPUÉS
        $stmtDespues = $conexion->prepare("SELECT * FROM precio_lista WHERE id = ? LIMIT 1");
        $stmtDespues->execute([$id]);
        $despues = $stmtDespues->fetch(PDO::FETCH_ASSOC);

        // AUDITORIA
        auditoria(
            $conexion,
            'UPDATE',
            'DESCUENTOS',          // módulo (poné el nombre que uses)
            'precio_lista',        // tabla
            $id,
            'Editó descuento',
            $antes,
            $despues
        );

        echo json_encode(["ok" => true, "msg" => "Descuento actualizado"]);
        exit;
    }

    // ========= INSERT =========
    $stmt = $conexion->prepare("
        INSERT INTO precio_lista (nombre_lista, porcentaje_descuento, activo)
        VALUES (?,?,?)
    ");
    $stmt->execute([$nombre, $porcentaje, $activo]);

    $nuevoId = $conexion->lastInsertId();

    // DESPUÉS (registro creado)
    $stmtDespues = $conexion->prepare("SELECT * FROM precio_lista WHERE id = ? LIMIT 1");
    $stmtDespues->execute([$nuevoId]);
    $despues = $stmtDespues->fetch(PDO::FETCH_ASSOC);

    // AUDITORIA
    auditoria(
        $conexion,
        'INSERT',
        'DESCUENTOS',
        'precio_lista',
        $nuevoId,
        'Creó descuento',
        null,
        $despues
    );

    echo json_encode(["ok" => true, "msg" => "Descuento creado"]);

} catch (Throwable $e) {
    echo json_encode(["ok" => false, "msg" => "Error: " . $e->getMessage()]);
}