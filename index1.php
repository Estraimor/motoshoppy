<?php
include './dashboard/nav.php';
require_once './conexion/conexion.php';

// === Obtener métricas del sistema ===
$totalCategorias = $conexion->query("SELECT COUNT(*) FROM categoria")->fetchColumn();
$totalMarcas = $conexion->query("SELECT COUNT(*) FROM marcas")->fetchColumn();
$totalProductos = $conexion->query("SELECT COUNT(*) FROM producto")->fetchColumn();
// $totalVentasHoy = $conexion->query("SELECT COUNT(*) FROM ventas WHERE DATE(fecha) = CURDATE()")->fetchColumn();

// === Detectar productos en alerta de stock ===
$alertasStock = $conexion->query("
  SELECT nombre, codigo, cantidad_actual, stock_minimo 
  FROM stock_producto 
  INNER JOIN producto ON producto.idProducto = stock_producto.producto_idProducto
  WHERE cantidad_actual <= stock_minimo
  ORDER BY cantidad_actual ASC
  LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header d-flex justify-content-between align-items-center mb-3">
  <h2>Bienvenido, <?= htmlspecialchars($_SESSION['nombre']); ?> 👋</h2>
  <small class="text-muted">Hoy es <?= date('d/m/Y'); ?></small>
</div>

<div class="content-body">

  <!-- === TARJETAS DE RESUMEN === -->
  <div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
      <div class="card bg-gradient-dark text-warning text-center shadow-sm h-100">
        <div class="card-body">
          <i class="fa-solid fa-layer-group fa-2x mb-2"></i>
          <h4><?= $totalCategorias ?></h4>
          <small>Categorías</small>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card bg-gradient-dark text-success text-center shadow-sm h-100">
        <div class="card-body">
          <i class="fa-solid fa-tags fa-2x mb-2"></i>
          <h4><?= $totalMarcas ?></h4>
          <small>Marcas registradas</small>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card bg-gradient-dark text-info text-center shadow-sm h-100">
        <div class="card-body">
          <i class="fa-solid fa-boxes-stacked fa-2x mb-2"></i>
          <h4><?= $totalProductos ?></h4>
          <small>Productos activos</small>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card bg-gradient-dark text-danger text-center shadow-sm h-100">
        <div class="card-body">
          <i class="fa-solid fa-chart-line fa-2x mb-2"></i>
          <h4><?= $totalVentasHoy ?></h4>
          <small>Ventas hoy</small>
        </div>
      </div>
    </div>
  </div>

  <!-- === BLOQUE DE COTIZACIONES === -->
  <div class="card bg-dark text-white shadow-sm mb-4 border-secondary">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
      <div>
        <h5 class="fw-bold text-warning mb-2"><i class="fa-solid fa-coins me-2"></i>Cotizaciones del día</h5>
        <div class="small text-white-50">
          <div><strong>1 USD</strong> = <span id="usdArs">-</span> ARS | <span id="usdPyg">-</span> PYG</div>
          <div><strong>1 PYG</strong> = <span id="pygUsd">-</span> USD | <span id="pygArs">-</span> ARS</div>
          <div class="text-warning mt-1" style="font-size:0.75rem;">
            <i class="fa-regular fa-clock me-1"></i>Última actualización: <span id="fechaCotizacion">-</span>
          </div>
        </div>
      </div>
      <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalCotizacion">
        <i class="fa-solid fa-pen-to-square"></i> Actualizar
      </button>
    </div>
  </div>

  <!-- === ALERTAS DE STOCK === -->
  <div class="card bg-dark text-white shadow-sm mb-4 border-0">
    <div class="card-body">
      <h5 class="text-danger fw-bold mb-3"><i class="fa-solid fa-triangle-exclamation me-2"></i>Alertas de stock</h5>
      <?php if (count($alertasStock) === 0): ?>
        <div class="text-success small">✅ Todos los productos tienen stock suficiente.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm table-dark align-middle mb-0">
            <thead class="text-warning">
              <tr>
                <th>Producto</th>
                <th>Código</th>
                <th>Stock actual</th>
                <th>Mínimo</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($alertasStock as $p): ?>
                <tr>
                  <td><?= htmlspecialchars($p['nombre']) ?></td>
                  <td><?= htmlspecialchars($p['codigo']) ?></td>
                  <td class="<?= $p['cantidad_actual'] == 0 ? 'text-danger' : 'text-warning' ?>">
                    <?= $p['cantidad_actual'] ?>
                  </td>
                  <td><?= $p['stock_minimo'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- === PANEL INFORMATIVO === -->
  <div class="card bg-gradient-dark text-white border-0 shadow-sm">
    <div class="card-body">
      <h5 class="text-info fw-bold mb-2"><i class="fa-solid fa-chart-pie me-2"></i>Resumen general</h5>
      <p class="text-white-50 mb-0">Revisa las métricas de desempeño del sistema, analiza ventas, niveles de stock y cotizaciones para mantener la operación óptima del negocio.</p>
    </div>
  </div>
</div>

<!-- === MODAL ACTUALIZAR COTIZACIÓN === -->
<div class="modal fade" id="modalCotizacion" tabindex="-1" aria-labelledby="modalCotizacionLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content bg-dark text-white border-secondary shadow-lg">
      <div class="modal-header border-secondary">
        <h5 class="modal-title" id="modalCotizacionLabel">
          <i class="fa-solid fa-pen-to-square text-warning me-2"></i>Actualizar cotización
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formCotizacion" autocomplete="off">
        <div class="modal-body">
          <div class="mb-3">
            <label for="usd_ars" class="form-label text-warning fw-semibold">
              <i class="fa-solid fa-dollar-sign me-1"></i>1 USD en ARS
            </label>
            <input type="number" step="0.01" min="0" name="usd_ars" id="usd_ars" class="form-control bg-dark text-white border-secondary" required>
          </div>
          <div class="mb-3">
            <label for="usd_pyg" class="form-label text-warning fw-semibold">
              <i class="fa-solid fa-coins me-1"></i>1 USD en PYG
            </label>
            <input type="number" step="0.01" min="0" name="usd_pyg" id="usd_pyg" class="form-control bg-dark text-white border-secondary" required>
          </div>
          <div class="mb-2">
            <label for="ars_pyg" class="form-label text-warning fw-semibold">
              <i class="fa-solid fa-money-bill-wave me-1"></i>1 ARS en PYG
            </label>
            <input type="number" step="0.01" min="0" name="ars_pyg" id="ars_pyg" class="form-control bg-dark text-white border-secondary" required>
          </div>
        </div>
        <div class="modal-footer border-secondary">
          <button type="submit" class="btn btn-warning w-100 fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// === COTIZACIÓN ===
async function cargarCotizacion() {
  try {
    const res = await fetch('/motoshoppy/api/get_cotizacion.php');
    const data = await res.json();

    document.getElementById('usdArs').innerText = parseFloat(data.usd_ars).toFixed(2);
    document.getElementById('usdPyg').innerText = parseFloat(data.usd_pyg).toLocaleString('es-PY');
    document.getElementById('pygUsd').innerText = (1 / data.usd_pyg).toFixed(6);
    document.getElementById('pygArs').innerText = (data.usd_ars / data.usd_pyg).toFixed(4);
    document.getElementById('fechaCotizacion').innerText = new Date(data.fecha_actualizacion).toLocaleString();

    document.getElementById('usd_ars').value = data.usd_ars;
    document.getElementById('usd_pyg').value = data.usd_pyg;
    document.getElementById('ars_pyg').value = data.ars_pyg;

  } catch (e) {
    document.getElementById('usdArs').innerText = '-';
    document.getElementById('usdPyg').innerText = '-';
  }
}

document.getElementById('formCotizacion').addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  const res = await fetch('/motoshoppy/api/update_cotizacion.php', { method: 'POST', body: formData });
  const r = await res.json();
  if (r.ok) {
    Swal.fire({ icon: 'success', title: 'Cotización actualizada', timer: 1500, showConfirmButton: false });
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalCotizacion'));
    modal.hide();
    cargarCotizacion();
  } else {
    Swal.fire({ icon: 'error', title: 'Error', text: r.msg || 'No se pudo actualizar.' });
  }
});

cargarCotizacion();
</script>

<?php include './dashboard/footer.php'; ?>
