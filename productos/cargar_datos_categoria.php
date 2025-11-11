<?php
require_once '../conexion/conexion.php';

$id = intval($_GET['id'] ?? 0);

$response = [
    'marcas' => [],
    'json_keys' => [],
    'json_values' => [],
    'aplicaciones' => []
];

if ($id > 0) {
    // === 1. Obtener marcas ===
    $stmt = $conexion->prepare("SELECT idmarcas, nombre_marca FROM marcas WHERE categoria_idCategoria = ?");
    $stmt->execute([$id]);
    $response['marcas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // === 2. Obtener JSONs de productos ===
    $stmt = $conexion->prepare("
        SELECT descripcion 
        FROM producto 
        WHERE Categoria_idCategoria = ? 
          AND descripcion IS NOT NULL 
          AND descripcion <> ''
    ");
    $stmt->execute([$id]);

    $keys = [];
    $values = [];

    foreach ($stmt as $row) {
        $json = json_decode($row['descripcion'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            foreach ($json as $k => $v) {
                $kLimpio = trim($k);
                if (!in_array($kLimpio, $keys)) {
                    $keys[] = $kLimpio;
                }
                // ✅ Ahora los valores se dejan vacíos
                $values[$kLimpio] = "";
            }
        }
    }

    $response['json_keys'] = array_values($keys);
    $response['json_values'] = $values;

    // === 3. Categorías especiales (Cubiertas/Ruedas) ===
    $categoriasEspeciales = [12, 13];
    if (in_array($id, $categoriasEspeciales)) {
        $stmtCub = $conexion->prepare("
            SELECT aro, ancho, perfil_cubierta, tipo, varias_aplicaciones 
            FROM atributos_cubiertas 
            INNER JOIN producto ON atributos_cubiertas.producto_idProducto = producto.idProducto
            WHERE producto.Categoria_idCategoria = ? 
            LIMIT 1
        ");
        $stmtCub->execute([$id]);
        $cubierta = $stmtCub->fetch(PDO::FETCH_ASSOC);

        if ($cubierta) {
            $response['cubiertas'] = [
                'aro' => $cubierta['aro'],
                'ancho' => $cubierta['ancho'],
                'perfil_cubierta' => $cubierta['perfil_cubierta'],
                'tipo' => $cubierta['tipo']
            ];

            if (!empty($cubierta['varias_aplicaciones'])) {
                $jsonAplicaciones = json_decode($cubierta['varias_aplicaciones'], true);
                if (is_array($jsonAplicaciones)) {
                    if (array_keys($jsonAplicaciones) === range(0, count($jsonAplicaciones) - 1)) {
                        $assoc = [];
                        foreach ($jsonAplicaciones as $i => $valor) {
                            $assoc["Aplicación " . ($i + 1)] = $valor;
                        }
                        $response['aplicaciones'] = $assoc;
                    } else {
                        $response['aplicaciones'] = $jsonAplicaciones;
                    }
                } else {
                    $response['aplicaciones'] = ["Texto" => $cubierta['varias_aplicaciones']];
                }
            }
        }
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
