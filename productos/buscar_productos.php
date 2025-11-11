<?php
include '../conexion/conexion.php';

$busqueda  = trim($_POST['busqueda'] ?? '');
$marca     = intval($_POST['marca'] ?? 0);
$categoria = intval($_POST['categoria'] ?? 0);
$min       = floatval($_POST['min'] ?? 0);
$max       = floatval($_POST['max'] ?? 0);
$proveedor = intval($_POST['proveedor'] ?? 0);
$orden     = $_POST['orden'] ?? '';

$query = "
SELECT p.idproducto, p.codigo, p.nombre, p.modelo, p.precio_expuesto,
       c.nombre_categoria,
       m.idmarcas AS idmarca,               -- ✅ agregar
       m.nombre_marca,
       p.descripcion, p.peso_ml, p.peso_g, p.imagen,
       u.lugar, u.estante
FROM producto p
LEFT JOIN categoria c ON p.Categoria_idCategoria = c.idCategoria
LEFT JOIN marcas m ON p.marcas_idmarcas = m.idmarcas
LEFT JOIN ubicacion_producto u ON p.ubicacion_producto_idubicacion_producto = u.idubicacion_producto
ORDER BY p.idproducto DESC

";

$params = [];

// === Filtros dinámicos ===
if ($busqueda !== '') {
    $query .= " AND (p.nombre LIKE ? OR p.codigo LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

if ($marca > 0) $query .= " AND p.marcas_idmarcas = $marca";
if ($categoria > 0) $query .= " AND p.Categoria_idCategoria = $categoria";
if ($proveedor > 0) $query .= " AND p.proveedor_idproveedores = $proveedor";
if ($min > 0) $query .= " AND p.precio_expuesto >= $min";
if ($max > 0) $query .= " AND p.precio_expuesto <= $max";

// === Ordenamiento ===
switch ($orden) {
    case 'precio_asc':
        $query .= " ORDER BY (p.precio_expuesto + 0) ASC";
        break;
    case 'precio_desc':
        $query .= " ORDER BY (p.precio_expuesto + 0) DESC";
        break;
    case 'nombre_asc':
        $query .= " ORDER BY p.nombre ASC";
        break;
    case 'nombre_desc':
        $query .= " ORDER BY p.nombre DESC";
        break;
    default:
        $query .= " ORDER BY p.idProducto DESC";
        break;
}


$stmt = $conexion->prepare($query);
$stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// === Si no hay resultados ===
if (!$resultados) {
    echo '<tr><td colspan="6" class="text-center text-muted">Sin resultados</td></tr>';
    exit;
}

foreach ($resultados as $p) {

    $codigo  = htmlspecialchars($p['codigo'] ?? '');
    $nombre  = htmlspecialchars($p['nombre'] ?? '');
    $marca   = htmlspecialchars($p['nombre_marca'] ?? '');
    $cat     = htmlspecialchars($p['nombre_categoria'] ?? '');
    $precio  = $p['precio_expuesto'] ?? 0;

    echo "
    <tr>
        <td>$codigo</td>
        <td>$nombre</td>
        <td>$marca</td>
        <td>$cat</td>
        <td data-order='$precio'>$" . number_format($precio, 2, ',', '.') . "</td>
        <td class='text-center'>
            <button class='btn btn-info btn-sm ver-detalle'
                data-bs-toggle='modal' data-bs-target='#modalDetalle'
                data-producto='" . json_encode($p, JSON_UNESCAPED_UNICODE) . "'>
                <i class='fa-solid fa-circle-info'></i> Detalle
            </button>
        </td>
    </tr>";
}

?>
