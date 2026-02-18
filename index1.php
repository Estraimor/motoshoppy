<?php
include './dashboard/nav.php';
require_once './conexion/conexion.php';

// === Obtener métricas del sistema ===
$totalCategorias = $conexion->query("SELECT COUNT(*) FROM categoria")->fetchColumn();
$totalMarcas = $conexion->query("SELECT COUNT(*) FROM marcas")->fetchColumn();
$totalProductos = $conexion->query("SELECT COUNT(*) FROM producto")->fetchColumn();
$totalVentasHoy = $conexion->query("SELECT COUNT(*) FROM ventas WHERE DATE(fecha) = CURDATE()")->fetchColumn() ?? 0;

// === Detectar productos en alerta de stock ===
$alertasStock = $conexion->query("
 SELECT
  p.idProducto,
  p.nombre,
  p.codigo,

  COALESCE(sp.cantidad_exhibida, 0) AS cantidad_exhibida,
  COALESCE(sp.cantidad_actual, 0)   AS cantidad_actual,
  COALESCE(sp.stock_minimo, 0)       AS stock_minimo

FROM producto p
LEFT JOIN stock_producto sp
  ON sp.producto_idProducto = p.idProducto

WHERE
  COALESCE(sp.cantidad_exhibida, 0) <= COALESCE(sp.stock_minimo, 0)
  OR sp.idstock_producto IS NULL   -- productos sin fila de stock

ORDER BY
  cantidad_exhibida ASC,
  cantidad_actual ASC

LIMIT 10;


")->fetchAll(PDO::FETCH_ASSOC);

?>
<link rel="stylesheet" href="./stock.css">
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
  <div class="card bg-gradient-dark text-danger text-center shadow-sm h-100"
       role="button"
       data-bs-toggle="modal"
       data-bs-target="#modalVentasHoy"
       style="cursor:pointer;">
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

  <!-- === VENTAS HOY === -->
   <!-- === MODAL: Ventas de hoy === -->
<div class="modal fade" id="modalVentasHoy" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title">
          <i class="fa-solid fa-receipt text-info me-2"></i>Ventas de hoy
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-dark table-sm align-middle mb-0" id="tablaVentasHoy">
            <thead class="text-info">
              <tr>
                <th style="width:90px">ID Venta</th>
                <th style="width:150px">Fecha/Hora</th>
                <th>Vendedor</th>
                <th>Cliente</th>
                <th>Producto</th>
                <th class="text-end" style="width:80px">Cant.</th>
                <th class="text-end" style="width:120px">P. Unit.</th>
                <th class="text-end" style="width:120px">Subtotal</th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              <tr>
                <th colspan="7" class="text-end">Total día:</th>
                <th id="totalDia" class="text-end">₲ 0</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <div class="modal-footer border-secondary">
        <button class="btn btn-outline-light" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


 <!-- === ALERTAS DE STOCK === -->
<div class="card bg-dark text-white shadow-sm mb-4 border-0 fade-in">
  <div class="card-body">

    <h5 class="text-danger fw-bold mb-3">
      <i class="fa-solid fa-triangle-exclamation me-2"></i>
      Alertas de stock
    </h5>

    <?php if (count($alertasStock) === 0): ?>
      
      <div class="alert alert-success py-2">
        ✔️ Todos los productos tienen stock suficiente.
      </div>

    <?php else: ?>

      <div class="table-responsive">
        <table id="tablaStockAlertas" class="table table-dark table-striped align-middle mb-0">
          <thead class="text-warning">
            <tr>
              <th>Producto</th>
              <th>Código</th>
              <th class="text-center">Stock exhibido</th>
              <th class="text-center">Stock depósito</th>
              <th class="text-center">Stock total</th>
              <th class="text-center">Estado</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($alertasStock as $p): ?>

              <?php
                $exhibido = (int)$p['cantidad_exhibida'];
                $deposito = (int)$p['cantidad_actual'];
                $minimo   = (int)$p['stock_minimo'];
                $total    = $exhibido + $deposito;

                /*
                 ─────────────────────────────────────────
                 DETERMINAR ESTADO (orden IMPORTANTE)
                 ─────────────────────────────────────────
                */

                // ================================
// 🧠 LÓGICA DE ESTADO DE STOCK
// ================================

// ⚫ Producto sin stock inicializado
if ($minimo === 0 && $exhibido === 0 && $deposito === 0) {
  $estado = '<span class="badge bg-secondary px-3 py-2">Stock no inicializado</span>';
  $accion = 'configurar';
  $btn    = 'btn-secondary';
  $texto  = 'Configurar stock';
  $icono  = 'fa-gear';
}

// 🔴 Sin stock total (no hay nada en ningún lado)
elseif ($exhibido === 0 && $deposito === 0) {
  $estado = '<span class="badge bg-danger px-3 py-2">Sin stock</span>';
  $accion = 'pedir';
  $btn    = 'btn-danger';
  $texto  = 'Pedir';
  $icono  = 'fa-truck';
}

// 🔴 Stock CRÍTICO (ambos por debajo del mínimo)
elseif ($exhibido < $minimo && $deposito < $minimo) {
  $estado = '<span class="badge badge-critical px-3 py-2">Stock crítico</span>';
  $accion = 'pedir';
  $btn    = 'btn-danger';
  $texto  = 'Pedir';
  $icono  = 'fa-truck';
}

// 🟠 Sin stock exhibido (hay depósito suficiente)
elseif ($exhibido === 0 && $deposito > 0) {
  $estado = '<span class="badge badge-move px-3 py-2">Sin stock exhibido</span>';
  $accion = 'mover';
  $btn    = 'btn-move';
  $texto  = 'Mover a exhibición';
  $icono  = 'fa-arrows-rotate';
}

// 🟡 Stock bajo (uno de los dos por debajo del mínimo, hay depósito)
elseif (
  ($exhibido < $minimo || $deposito < $minimo)
  && $deposito > 0
) {
  $estado = '<span class="badge bg-warning text-dark px-3 py-2">Stock bajo</span>';
  $accion = 'mover';
  $btn    = 'btn-warning';
  $texto  = 'Revisar / Reponer';
  $icono  = 'fa-arrows-rotate';
}



              ?>

              <tr>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['codigo']) ?></td>

                <!-- Stock exhibido -->
                <td class="text-center <?= $exhibido === 0 ? 'text-danger fw-bold' : 'text-warning' ?>">
                  <?= $exhibido ?>
                </td>

                <!-- Stock depósito -->
                <td class="text-center <?= $deposito === 0 ? 'text-danger fw-bold' : 'text-info' ?>">
                  <?= $deposito ?>
                </td>

                <!-- Stock total (informativo) -->
                <td class="text-center text-info fw-bold opacity-75">
                  <?= $total ?>
                </td>

                <td class="text-center"><?= $estado ?></td>

                <td class="text-center">

<?php
    // Definimos modo final
    if ($accion === 'mover') {
        $modo = 'mover';
    } elseif ($accion === 'configurar') {
        $modo = 'configurar';
    } else {
        $modo = 'pedir';
    }
?>

<a href="movimientos_stock/index.php?producto=<?= $p['idProducto'] ?>&modo=<?= $modo ?>"
   class="btn <?= $btn ?> btn-sm fw-bold">

    <i class="fa-solid <?= $icono ?>"></i>
    <?= $texto ?>

</a>

</td>
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


  <!-- === BLOQUE DE GRÁFICOS === -->
<div class="row mt-4">
  <div class="col-md-7 mb-4">
    <div class="card bg-dark text-white shadow-sm border-secondary">
      <div class="card-body">
        <h5 class="text-warning fw-bold mb-3"><i class="fa-solid fa-chart-column me-2"></i>Ventas por mes</h5>
        <canvas id="graficoVentas"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-5 mb-4">
    <div class="card bg-dark text-white shadow-sm border-secondary">
      <div class="card-body">
        <h5 class="text-info fw-bold mb-3"><i class="fa-solid fa-pie-chart me-2"></i>Estado de stock</h5>
        <canvas id="graficoStock"></canvas>
      </div>
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


<!-- === CHART.JS === -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// === GRÁFICO DE VENTAS POR MES ===
async function cargarGraficoVentas() {
  try {
    const res = await fetch('/motoshoppy/config_index/get_ventas_mes.php');
    const data = await res.json();

    const ctx = document.getElementById('graficoVentas');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.meses,
        datasets: [{
          label: 'Ventas',
          data: data.totales,
          backgroundColor: 'rgba(255, 193, 7, 0.6)',
          borderColor: 'rgba(255, 193, 7, 1)',
          borderWidth: 1,
          borderRadius: 6,
        }]
      },
      options: {
        scales: {
          x: {
            ticks: { color: '#ccc' },
            grid: { color: 'rgba(255,255,255,0.1)' }
          },
          y: {
            ticks: { color: '#ccc' },
            grid: { color: 'rgba(255,255,255,0.1)' },
            beginAtZero: true
          }
        },
        plugins: {
          legend: { labels: { color: '#fff' } },
          tooltip: {
            backgroundColor: '#222',
            borderColor: '#ffc107',
            borderWidth: 1
          }
        }
      }
    });
  } catch (e) {
    console.error('Error cargando gráfico de ventas', e);
  }
}

// === GRÁFICO DE STOCK ===
async function cargarGraficoStock() {
  try {
    const res = await fetch('/motoshoppy/config_index/get_stock_estado.php');
    const data = await res.json();

    // Datos del stock general
    const optimo = data.general.optimo;
    const bajo = data.general.bajo;
    const sin = data.general.sin;

    const ctx = document.getElementById('graficoStock');

    // Destruir gráfico previo si existe (evita superposición)
    if (ctx.chartInstance) {
      ctx.chartInstance.destroy();
    }

    ctx.chartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Stock óptimo', 'Bajo stock', 'Sin stock'],
        datasets: [{
          data: [optimo, bajo, sin],
          backgroundColor: [
            'rgba(40, 167, 69, 0.8)',   // verde
            'rgba(255, 193, 7, 0.8)',   // amarillo
            'rgba(220, 53, 69, 0.8)'    // rojo
          ],
          borderColor: ['#2a2a2a', '#2a2a2a', '#2a2a2a'],
          borderWidth: 2
        }]
      },
      options: {
        plugins: {
          legend: {
            labels: { color: '#fff', font: { size: 13 } },
            position: 'bottom'
          },
          tooltip: {
            backgroundColor: '#111',
            borderColor: '#00c8ff',
            borderWidth: 1
          }
        }
      }
    });

  } catch (e) {
    console.error('Error cargando gráfico de stock', e);
  }
}


// === INICIALIZAR ===
cargarGraficoVentas();
cargarGraficoStock();
</script>


<script>
document.getElementById('modalVentasHoy').addEventListener('show.bs.modal', async () => {
  const tbody = document.querySelector('#tablaVentasHoy tbody');
  const totalDia = document.getElementById('totalDia');
  tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Cargando...</td></tr>';
  totalDia.textContent = '$ 0';

  try {
    const res = await fetch('/motoshoppy/api/get_ventas_hoy.php');
    const data = await res.json();

    if (!data.ok) throw new Error(data.msg || 'Error al obtener ventas');

    if (data.ventas.length === 0) {
      tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">No hubo ventas hoy.</td></tr>';
      return;
    }

    let total = 0;
    tbody.innerHTML = '';
    data.ventas.forEach(v => {
      total += parseFloat(v.subtotal);
      tbody.innerHTML += `
        <tr>
          <td>${v.idVenta}</td>
          <td>${v.fecha}</td>
          <td>${v.vendedor || '-'}</td>
          <td>${v.cliente || '-'}</td>
          <td>${v.producto}</td>
          <td class="text-end">${v.cantidad}</td>
          <td class="text-end">$${Number(v.precio_unitario).toLocaleString('es-AR', {minimumFractionDigits:2})}</td>
          <td class="text-end">$${Number(v.subtotal).toLocaleString('es-AR', {minimumFractionDigits:2})}</td>
        </tr>`;
    });

    totalDia.textContent = `$ ${total.toLocaleString('es-AR', {minimumFractionDigits:2})}`;

  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-3">${err.message}</td></tr>`;
  }
});


</script>

<script>
$(document).ready(function () {
  $('#tablaStockAlertas').DataTable({
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50],
    ordering: true,
    order: [[5, 'asc']], // ordenar por ESTADO
    responsive: true,
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json"
    },
    columnDefs: [
      { orderable: false, targets: [6] } // desactiva orden en Acciones
    ]
  });
});
</script>


<script>
let tablaVentasHoyDT = null;

document.getElementById('modalVentasHoy')
  .addEventListener('shown.bs.modal', function () {

  if (tablaVentasHoyDT) {
    tablaVentasHoyDT.ajax.reload();
    return;
  }

  tablaVentasHoyDT = $('#tablaVentasHoy').DataTable({
    ajax: {
      url: '/motoshoppy/api/get_ventas_hoy.php',
      dataSrc: function (json) {
        if (!json.ok) {
          Swal.fire('Error', json.msg || 'No se pudieron cargar las ventas', 'error');
          return [];
        }

        // === TOTAL DEL DÍA ===
        let total = 0;
        json.ventas.forEach(v => total += parseFloat(v.subtotal));
        document.getElementById('totalDia').textContent =
          `$ ${total.toLocaleString('es-AR', { minimumFractionDigits: 2 })}`;

        return json.ventas;
      }
    },

    destroy: true,
    processing: true,
    responsive: true,
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50],

    order: [[1, 'desc']], // fecha

    columns: [
      { data: 'idVenta' },
      { data: 'fecha' },
      { data: 'vendedor', defaultContent: '-' },
      { data: 'cliente', defaultContent: '-' },
      { data: 'producto' },
      { data: 'cantidad', className: 'text-end' },
      {
        data: 'precio_unitario',
        className: 'text-end',
        render: d => `$${Number(d).toLocaleString('es-AR', { minimumFractionDigits: 2 })}`
      },
      {
        data: 'subtotal',
        className: 'text-end fw-bold text-warning',
        render: d => `$${Number(d).toLocaleString('es-AR', { minimumFractionDigits: 2 })}`
      }
    ],

    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json"
    },

    columnDefs: [
      { orderable: false, targets: [] }
    ]
  });
});
</script>



<?php include './dashboard/footer.php'; ?>
