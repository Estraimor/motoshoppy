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

  -- CAMPOS QUE FALTABAN
  p.precio_expuesto,
  p.precio_costo,
  p.peso_ml,
  p.peso_g,
  p.ubicacion_producto_idubicacion_producto,

  p.imagen,
  p.descripcion,

  m.nombre_marca,
  c.nombre_categoria,
  c.idCategoria,

  -- STOCK
  s.cantidad_actual AS stock_deposito,
  s.stock_minimo,
  s.cantidad_exhibida,

  -- ATRIBUTOS DE CUBIERTAS
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
   LÍMITE
========================== */
$sql .= " LIMIT 200 ";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==========================
   PROCESAMIENTO DE RESULTADOS
========================== */
$out = [];

foreach ($rows as $r) {

  /* MINI DESCRIPCIÓN */
  $mini = '';
  if (!empty($r['descripcion'])) {
    $json = json_decode($r['descripcion'], true);

    if (is_array($json)) {
      $pairs = array_slice($json, 0, 3, true);
      $tmp = [];

      foreach ($pairs as $k => $v) {
        if (is_array($v)) $v = implode('/', $v);
        $tmp[] = "<span class='text-warning'>$k:</span> " . htmlspecialchars((string)$v);
      }

      $mini = implode(' · ', $tmp);
    }
  }

  $r['descripcion_res'] = $mini;

  /* --- STOCK --- */
  $exhibido = (int)$r['cantidad_exhibida'];
  $deposito = (int)$r['stock_deposito'];
  $minimo   = (int)$r['stock_minimo'];

  // Stock visible
  if ($exhibido > 0)       $visible = $exhibido;
  elseif ($deposito > 0)  $visible = $deposito;
  else                    $visible = 0;

  // Estado de stock
  if ($exhibido <= 0 && $deposito <= 0) {
    $estado = "sin_stock";
  } elseif ($visible <= $minimo) {
    $estado = "bajo_stock";
  } else {
    $estado = "ok";
  }

  /* AGREGAR CAMPOS CALCULADOS */
  $r['stock_visible']  = $visible;
  $r['stock_general']  = $deposito;
  $r['stock_exhibido'] = $exhibido;
  $r['stock_estado']   = $estado;

  /* JSON PROCESADO COMPLETO */
  if (!empty($r['descripcion']) && $r['descripcion'] !== "null") {
    $r['descripcion_json'] = json_decode($r['descripcion'], true);
} else {
    $r['descripcion_json'] = [];
}


  $out[] = $r;
}

/* ==========================
   SALIDA JSON
========================== */
header('Content-Type: application/json; charset=utf-8');
echo json_encode($out);
