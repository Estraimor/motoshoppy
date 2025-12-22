<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';
?>

<!-- ================= HEADER ================= -->
<div class="content-header mb-4">
  <h2 class="text-info fw-bold">
    <i class="fa-solid fa-chart-line me-2"></i>
    Análisis Comercial
  </h2>
  <p class="text-white-50 mb-0">
    Control operativo y contable del negocio.
  </p>
</div>

<!-- ================= FILTRO DE PERÍODO ================= -->
<div class="card bg-dark text-white shadow-sm border-secondary mb-4">
  <div class="card-body d-flex flex-wrap align-items-end gap-3">

    <div>
      <label class="form-label small mb-1">Período</label>
      <select id="filtroPeriodo" class="form-select form-select-sm bg-dark text-white border-secondary">
        <option value="dia">Hoy</option>
        <option value="semana">Esta semana</option>
        <option value="mes" selected>Este mes</option>
        <option value="rango">Rango personalizado</option>
      </select>
    </div>

    <div id="wrapDesde" class="d-none">
      <label class="form-label small mb-1">Desde</label>
      <input type="date" id="fechaDesde" class="form-control form-control-sm bg-dark text-white border-secondary">
    </div>

    <div id="wrapHasta" class="d-none">
      <label class="form-label small mb-1">Hasta</label>
      <input type="date" id="fechaHasta" class="form-control form-control-sm bg-dark text-white border-secondary">
    </div>

    <div>
      <button class="btn btn-outline-warning btn-sm" id="btnAplicarFiltro">
        <i class="fa-solid fa-filter me-1"></i>Aplicar
      </button>
    </div>

  </div>
</div>

<!-- ================= KPIs ================= -->
<div class="row g-3 mb-4" id="kpisComerciales"></div>

<!-- ================= CIERRE DE CAJA ================= -->
<div class="card bg-dark text-white shadow-sm border-secondary mb-4">
  <div class="card-body">

    <h5 class="text-warning fw-bold mb-3">
      <i class="fa-solid fa-cash-register me-2"></i>
      Cierre de caja
    </h5>

    <div class="row g-3 mb-3" id="resumenCaja"></div>

    <div class="d-flex gap-2">
      <a id="btnCierreCajaPDF" class="btn btn-outline-success btn-sm" target="_blank">
        <i class="fa-solid fa-file-pdf me-1"></i>
        Cierre de caja (PDF)
      </a>

      <a id="btnLibroDiarioPDF" class="btn btn-outline-info btn-sm" target="_blank">
        <i class="fa-solid fa-book me-1"></i>
        Libro diario (PDF)
      </a>
    </div>

  </div>
</div>

<!-- ================= ROTACIÓN ================= -->
<div class="card bg-dark text-white shadow-sm border-secondary mb-4">
  <div class="card-body">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="text-warning fw-bold mb-0">
        <i class="fa-solid fa-arrows-spin me-2"></i>
        Rotación de productos
      </h5>

      <select id="filtroRotacion" class="form-select form-select-sm w-auto bg-dark text-white border-secondary">
        <option value="todos">Todos</option>
        <option value="alta">Alta rotación</option>
        <option value="baja">Baja rotación</option>
      </select>
    </div>

    <div class="table-responsive">
      <table id="tablaRotacion" class="table table-dark table-striped align-middle w-100">
        <thead class="text-info">
          <tr>
            <th>Producto</th>
            <th class="text-center">Unidades</th>
            <th class="text-center">Rotación</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

  </div>
</div>

<script>
/* ================= ELEMENTOS ================= */
const filtroPeriodo = document.getElementById('filtroPeriodo');
const fechaDesde   = document.getElementById('fechaDesde');
const fechaHasta   = document.getElementById('fechaHasta');
const wrapDesde    = document.getElementById('wrapDesde');
const wrapHasta    = document.getElementById('wrapHasta');
const btnAplicar   = document.getElementById('btnAplicarFiltro');

/* ================= MOSTRAR / OCULTAR FECHAS ================= */
filtroPeriodo.addEventListener('change', () => {
  const esRango = filtroPeriodo.value === 'rango';
  wrapDesde.classList.toggle('d-none', !esRango);
  wrapHasta.classList.toggle('d-none', !esRango);
});

/* ================= PARAMS UNIFICADOS ================= */
function getParams() {
  return new URLSearchParams({
    tipo: filtroPeriodo.value,
    desde: fechaDesde.value,
    hasta: fechaHasta.value
  });
}

/* ================= KPIs ================= */
async function cargarKPIs() {
  const res = await fetch('/motoshoppy/analisis_comercial/api/get_kpis.php?' + getParams());
  const data = await res.json();

  document.getElementById('kpisComerciales').innerHTML = `
    <div class="col-md-3 col-6">
      <div class="card bg-dark text-warning text-center">
        <div class="card-body">
          <small>Facturación</small>
          <h5>$${Number(data.facturacion).toLocaleString('es-AR')}</h5>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-6">
      <div class="card bg-dark text-info text-center">
        <div class="card-body">
          <small>Ventas</small>
          <h5>${data.ventas}</h5>
        </div>
      </div>
    </div>

  `;
}

/* ================= CIERRE DE CAJA ================= */
async function cargarCierreCaja() {
  const res = await fetch('/motoshoppy/analisis_comercial/api/get_cierre_caja.php?' + getParams());
  const data = await res.json();

  document.getElementById('resumenCaja').innerHTML = `
    <div class="col-md-3 col-6 text-center">
      <small>Ventas</small>
      <h5>${data.ventas}</h5>
    </div>
    <div class="col-md-3 col-6 text-center">
      <small>Efectivo</small>
      <h5>$${Number(data.efectivo).toLocaleString('es-AR')}</h5>
    </div>
    <div class="col-md-3 col-6 text-center">
      <small>Tarjeta</small>
      <h5>$${Number(data.tarjeta).toLocaleString('es-AR')}</h5>
    </div>
    <div class="col-md-3 col-6 text-center text-success">
      <small>Total</small>
      <h5>$${Number(data.total).toLocaleString('es-AR')}</h5>
    </div>
  `;

  document.getElementById('btnCierreCajaPDF').href =
    '/motoshoppy/analisis_comercial/pdf/cierre_caja.php?' + getParams();

  document.getElementById('btnLibroDiarioPDF').href =
    '/motoshoppy/analisis_comercial/pdf/libro_diario.php?' + getParams();
}

/* ================= ROTACIÓN ================= */
let tablaRotacion = null;

async function cargarRotacion() {
  const res = await fetch('/motoshoppy/analisis_comercial/api/get_rotacion_productos.php?' + getParams());
  const data = await res.json();

  if (tablaRotacion) {
    tablaRotacion.clear().rows.add(data.data).draw();
    return;
  }

  tablaRotacion = $('#tablaRotacion').DataTable({
    data: data.data,
    columns: [
      { data: 'producto' },
      { data: 'unidades', className: 'text-center' },
      {
        data: 'rotacion',
        className: 'text-center',
        render: r =>
          r === 'Alta'
            ? '<span class="badge bg-success">Alta</span>'
            : '<span class="badge bg-danger">Baja</span>'
      }
    ],
    info: false
  });
}

/* ================= FILTRO ROTACIÓN ================= */
$('#filtroRotacion').on('change', function () {
  tablaRotacion.column(2)
    .search(this.value === 'todos' ? '' : this.value === 'alta' ? 'Alta' : 'Baja')
    .draw();
});

/* ================= APLICAR ================= */
btnAplicar.addEventListener('click', () => {
  cargarKPIs();
  cargarCierreCaja();
  cargarRotacion();
});

/* ================= INIT ================= */
cargarKPIs();
cargarCierreCaja();
cargarRotacion();
</script>

<?php include '../dashboard/footer.php'; ?>
