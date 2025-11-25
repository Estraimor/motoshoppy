<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../conexion/conexion.php';

/* ==========================
   PARÁMETROS DE FILTRO
========================== */
$q         = trim($_GET['q'] ?? '');
$marca     = $_GET['marca'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$pmin      = $_GET['pmin'] ?? '';
$pmax      = $_GET['pmax'] ?? '';
$ordenar   = $_GET['ordenar'] ?? '';

/* ==========================
   CONSULTA BASE
========================== */
$sql = "
SELECT 
  p.idProducto,
  p.nombre,
  p.codigo,
  p.modelo,
  p.precio_expuesto,
  p.imagen,
  p.descripcion,
  m.nombre_marca,
  c.nombre_categoria,
  c.idCategoria,
  s.cantidad_actual AS stock_actual,
  s.stock_minimo,
  s.cantidad_exhibida,
  CASE 
    WHEN s.cantidad_actual <= 0 THEN 'sin_stock'
    WHEN s.cantidad_actual <= s.stock_minimo THEN 'bajo_stock'
    ELSE 'ok'
  END AS stock_estado,
  ac.aro,
  ac.ancho,
  ac.perfil_cubierta,
  ac.tipo,
  ac.varias_aplicaciones
FROM producto p
LEFT JOIN marcas m ON m.idmarcas = p.marcas_idmarcas
LEFT JOIN categoria c ON c.idCategoria = p.Categoria_idCategoria
LEFT JOIN stock_producto s ON s.producto_idProducto = p.idProducto
LEFT JOIN atributos_cubiertas ac ON ac.producto_idProducto = p.idProducto
WHERE 1=1
";




$params = [];

/* ==========================
   FILTROS
========================== */
if ($q !== '') {
  $sql .= " AND (p.nombre LIKE ? OR p.codigo LIKE ?) ";
  $params[] = "%$q%";
  $params[] = "%$q%";
}
if ($marca !== '') {
  $sql .= " AND p.marcas_idmarcas = ? ";
  $params[] = $marca;
}
if ($categoria !== '') {
  $sql .= " AND p.Categoria_idCategoria = ? ";
  $params[] = $categoria;
}
if ($pmin !== '' && is_numeric($pmin)) {
  $sql .= " AND p.precio_expuesto >= ? ";
  $params[] = $pmin;
}
if ($pmax !== '' && is_numeric($pmax)) {
  $sql .= " AND p.precio_expuesto <= ? ";
  $params[] = $pmax;
}

/* ==========================
   ORDENAMIENTO
========================== */
switch ($ordenar) {
  case 'precio_asc':  $sql .= " ORDER BY p.precio_expuesto ASC ";  break;
  case 'precio_desc': $sql .= " ORDER BY p.precio_expuesto DESC "; break;
  case 'nombre_asc':  $sql .= " ORDER BY p.nombre ASC ";           break;
  case 'nombre_desc': $sql .= " ORDER BY p.nombre DESC ";          break;
  default:            $sql .= " ORDER BY p.idProducto DESC ";
}

/* ==========================
   LÍMITE DE RESULTADOS
========================== */
$sql .= " LIMIT 100 ";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==========================
   PROCESAMIENTO DE RESULTADOS
========================== */
$out = [];
foreach ($rows as $r) {
  // Generar mini descripción desde JSON
  $mini = '';
  if (!empty($r['descripcion'])) {
    $j = json_decode($r['descripcion'], true);
    if (is_array($j)) {
      $pairs = array_slice($j, 0, 3, true);
      $tmp = [];
      foreach ($pairs as $k => $v) {
        if (is_array($v)) $v = implode('/', $v);
        $tmp[] = "<span class='text-warning'>$k:</span> " . htmlspecialchars((string)$v);
      }
      $mini = implode(' · ', $tmp);
    }
  }
  $r['descripcion_res'] = $mini;

  // Control visual de stock usando cantidad_exhibida
$exhibido = (int)($r['cantidad_exhibida'] ?? 0);

$r['stock_estado'] = 
    $exhibido <= 0 ? 'sin_stock' :
    ($exhibido <= ($r['stock_minimo'] ?? 1) ? 'bajo_stock' : 'ok');

// Reescribo stock_actual con el valor que realmente querés mostrar
$r['stock_actual'] = $exhibido;

$out[] = $r;

}

/* ==========================
   SALIDA JSON
========================== */
header('Content-Type: application/json; charset=utf-8');
echo json_encode($out);
