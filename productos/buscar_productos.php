<?php
include '../conexion/conexion.php';

/* =========================
   VARIABLES DATATABLES
========================= */

$draw   = intval($_POST['draw'] ?? 0);
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);

/* =========================
   FILTROS PERSONALIZADOS
========================= */

$busqueda  = trim($_POST['busqueda'] ?? '');
$marca     = intval($_POST['marca'] ?? 0);
$categoria = intval($_POST['categoria'] ?? 0);
$min       = floatval($_POST['min'] ?? 0);
$max       = floatval($_POST['max'] ?? 0);
$proveedor = intval($_POST['proveedor'] ?? 0);
$orden     = $_POST['orden'] ?? '';

/* =========================
   BASE QUERY
========================= */

$queryBase = "
FROM producto p
LEFT JOIN categoria c 
    ON p.Categoria_idCategoria = c.idCategoria
LEFT JOIN marcas m 
    ON p.marcas_idmarcas = m.idmarcas
LEFT JOIN ubicacion_producto u 
    ON p.ubicacion_producto_idubicacion_producto = u.idubicacion_producto
WHERE 1=1
";

$params = [];

/* =========================
   FILTROS
========================= */

if ($busqueda !== '') {
    $queryBase .= " AND (p.nombre LIKE ? OR p.codigo LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

if ($marca > 0) {
    $queryBase .= " AND p.marcas_idmarcas = ?";
    $params[] = $marca;
}

if ($categoria > 0) {
    $queryBase .= " AND p.Categoria_idCategoria = ?";
    $params[] = $categoria;
}

if ($proveedor > 0) {
    $queryBase .= " AND p.proveedor_idproveedores = ?";
    $params[] = $proveedor;
}

if ($min > 0) {
    $queryBase .= " AND p.precio_expuesto >= ?";
    $params[] = $min;
}

if ($max > 0) {
    $queryBase .= " AND p.precio_expuesto <= ?";
    $params[] = $max;
}

/* =========================
   TOTAL SIN FILTROS
========================= */

$totalQuery = $conexion->query("SELECT COUNT(*) FROM producto");
$recordsTotal = $totalQuery->fetchColumn();

/* =========================
   TOTAL CON FILTROS
========================= */

$countStmt = $conexion->prepare("SELECT COUNT(*) $queryBase");
$countStmt->execute($params);
$recordsFiltered = $countStmt->fetchColumn();

/* =========================
   ORDENAMIENTO
========================= */

$orderSQL = " ORDER BY p.idproducto DESC";

switch ($orden) {
    case 'precio_asc':
        $orderSQL = " ORDER BY p.precio_expuesto ASC";
        break;
    case 'precio_desc':
        $orderSQL = " ORDER BY p.precio_expuesto DESC";
        break;
    case 'nombre_asc':
        $orderSQL = " ORDER BY p.nombre ASC";
        break;
    case 'nombre_desc':
        $orderSQL = " ORDER BY p.nombre DESC";
        break;
}

/* =========================
   QUERY FINAL CON PAGINACIÓN
========================= */

$dataQuery = "
SELECT 
    p.idproducto,
    p.codigo,
    p.estado,
    p.nombre,
    p.modelo,
    p.precio_expuesto,
    p.precio_costo,
    p.descripcion,
    p.peso_ml,
    p.peso_g,
    p.imagen,
    m.idmarcas AS idmarca,
    m.nombre_marca,
    c.nombre_categoria,
    u.lugar,
    u.estante
$queryBase
$orderSQL
LIMIT $start, $length
";

$stmt = $conexion->prepare($dataQuery);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   RESPUESTA JSON DATATABLES
========================= */

echo json_encode([
    "draw" => $draw,
    "recordsTotal" => intval($recordsTotal),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $data
]);
