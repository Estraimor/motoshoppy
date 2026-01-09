<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';
?>
<link rel="stylesheet" href="./analisis.css">

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

<!-- ⭐⭐⭐ KPIs + FILTRO DE PERÍODO ⭐⭐⭐ -->
<div class="card bg-dark text-white shadow-sm border-secondary mb-3">
  <div class="card-body d-flex flex-wrap align-items-end gap-3">

    <div>
      <label class="form-label small mb-1">Período</label>
      <select id="kpiPeriodo" class="form-select form-select-sm bg-dark text-white border-secondary">
        <option value="dia">Hoy</option>
        <option value="semana">Esta semana</option>
        <option value="mes" selected>Este mes</option>
        <option value="rango">Rango personalizado</option>
      </select>
    </div>

    <div id="kpiWrapDesde" class="d-none">
      <label class="form-label small mb-1">Desde</label>
      <input type="date" id="kpiDesde" class="form-control form-control-sm bg-dark text-white border-secondary">
    </div>

    <div id="kpiWrapHasta" class="d-none">
      <label class="form-label small mb-1">Hasta</label>
      <input type="date" id="kpiHasta" class="form-control form-control-sm bg-dark text-white border-secondary">
    </div>

    <div>
      <button class="btn btn-outline-warning btn-sm" id="btnKpiFiltro">
        <i class="fa-solid fa-filter me-1"></i>Aplicar
      </button>
    </div>

  </div>
</div>

<div class="row g-3 mb-4" id="kpisComerciales"></div>


<!-- ⭐⭐⭐ CIERRE DE CAJA + FILTRO ⭐⭐⭐ -->
<div class="card bg-dark text-white shadow-sm border-secondary mb-4">
  <div class="card-body">

    <h5 class="text-warning fw-bold mb-2">
      <i class="fa-solid fa-cash-register me-2"></i>
      Cierre de caja
    </h5>

    <div class="d-flex flex-wrap align-items-end gap-3 mb-3">

      <div>
        <label class="form-label small mb-1">Período</label>
        <select id="cajaPeriodo" class="form-select form-select-sm bg-dark text-white border-secondary">
          <option value="dia">Hoy</option>
          <option value="semana">Esta semana</option>
          <option value="mes" selected>Este mes</option>
          <option value="rango">Rango personalizado</option>
        </select>
      </div>

      <div id="cajaWrapDesde" class="d-none">
        <label class="form-label small mb-1">Desde</label>
        <input type="date" id="cajaDesde" class="form-control form-control-sm bg-dark text-white border-secondary">
      </div>

      <div id="cajaWrapHasta" class="d-none">
        <label class="form-label small mb-1">Hasta</label>
        <input type="date" id="cajaHasta" class="form-control form-control-sm bg-dark text-white border-secondary">
      </div>

      <div>
        <button class="btn btn-outline-warning btn-sm" id="btnCajaFiltro">
          <i class="fa-solid fa-filter me-1"></i>Aplicar
        </button>
      </div>

    </div>

    <div class="modal fade" id="modalPDF" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white border-secondary">

      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa-solid fa-file-pdf me-2 text-warning"></i>
          Generar documento
        </h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <p class="mb-2 text-white-50">
          Período seleccionado:<br>
          <strong id="rangoTexto"></strong>
        </p>

        <div class="d-flex flex-column gap-2">

          <button class="btn btn-outline-success w-100" id="btnGenCierre">
            <i class="fa-solid fa-cash-register me-1"></i>
            Cierre de caja
          </button>

          <button class="btn btn-outline-info w-100" id="btnGenLibro">
            <i class="fa-solid fa-book me-1"></i>
            Libro diario
          </button>

        </div>
      </div>

    </div>
  </div>
</div>
</div>


<!-- ⭐⭐⭐ ROTACIÓN + FILTRO ⭐⭐⭐ -->
<div class="card bg-dark text-white shadow-sm border-secondary mb-4">
  <div class="card-body">

    <div class="d-flex align-items-center gap-3 mb-2">
      <h5 class="text-warning fw-bold mb-0">
        <i class="fa-solid fa-arrows-spin me-2"></i>
        Rotación de productos
      </h5>
    </div>

    <div class="d-flex flex-wrap align-items-end gap-3 mb-3">

      <div>
        <label class="form-label small mb-1">Período</label>
        <select id="rotPeriodo" class="form-select form-select-sm bg-dark text-white border-secondary">
          <option value="dia">Hoy</option>
          <option value="semana">Esta semana</option>
          <option value="mes" selected>Este mes</option>
          <option value="rango">Rango personalizado</option>
        </select>
      </div>

      <div id="rotWrapDesde" class="d-none">
        <label class="form-label small mb-1">Desde</label>
        <input type="date" id="rotDesde" class="form-control form-control-sm bg-dark text-white border-secondary">
      </div>

      <div id="rotWrapHasta" class="d-none">
        <label class="form-label small mb-1">Hasta</label>
        <input type="date" id="rotHasta" class="form-control form-control-sm bg-dark text-white border-secondary">
      </div>

      <div>
        <button class="btn btn-outline-warning btn-sm" id="btnRotFiltro">
          <i class="fa-solid fa-filter me-1"></i>Aplicar
        </button>
      </div>

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
/* ------------------ FILTRO REUTILIZABLE POR SECCIÓN ------------------ */
function initPeriodoFilter(base){
  const periodo = document.getElementById(base+'Periodo');
  const desde   = document.getElementById(base+'Desde');
  const hasta   = document.getElementById(base+'Hasta');
  const wrapD   = document.getElementById(base+'WrapDesde');
  const wrapH   = document.getElementById(base+'WrapHasta');

  const formatDate = d => d.toISOString().split('T')[0];

  function setFechas(){
    const hoy = new Date();
    let d = '', h = formatDate(hoy);

    switch(periodo.value){
      case 'dia': d = h; break;

      case 'semana':
        const dow = hoy.getDay(); // 0 = Domingo
        const lunes = new Date(hoy);
        lunes.setDate(hoy.getDate() - ((dow + 6) % 7));
        d = formatDate(lunes);
        break;

      case 'mes':
        d = formatDate(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
        break;

      case 'rango': return;
    }

    desde.value = d;
    hasta.value = h;
  }

  periodo.addEventListener('change',()=>{
    const rango = periodo.value === 'rango';
    wrapD.classList.toggle('d-none',!rango);
    wrapH.classList.toggle('d-none',!rango);
    setFechas();
  });

  setFechas();

  return () => ({
    tipo: periodo.value,
    desde: desde.value,
    hasta: hasta.value
  });
}


/* ------------------ INIT FILTROS ------------------ */
const getKpiParams  = initPeriodoFilter('kpi');
const getCajaParams = initPeriodoFilter('caja');
const getRotParams  = initPeriodoFilter('rot');


/* ------------------ KPIs ------------------ */
async function cargarKPIs(){
  const p = new URLSearchParams(getKpiParams());
  const r = await fetch('/motoshoppy/analisis_comercial/api/get_kpis.php?'+p);
  const d = await r.json();

  const cont = document.getElementById('kpisComerciales');
  if (!cont) return;

  cont.innerHTML = `
    <div class="col-md-3 col-6">
      <div class="card bg-dark text-warning text-center">
        <div class="card-body">
          <small>Facturación</small>
          <h5>$${Number(d.facturacion || 0).toLocaleString('es-AR')}</h5>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-6">
      <div class="card bg-dark text-info text-center">
        <div class="card-body">
          <small>Ventas</small>
          <h5>${d.ventas || 0}</h5>
        </div>
      </div>
    </div>`;
}

document.getElementById("btnKpiFiltro")
  .addEventListener('click', cargarKPIs);


/* ---------- MODAL PDF ---------- */
const modalPDF   = new bootstrap.Modal(document.getElementById('modalPDF'));
const rangoTexto = document.getElementById('rangoTexto');

function buildQueryFromCaja(){
  return new URLSearchParams(getCajaParams()).toString();
}

/* 👍 SOLO abre el modal (no consume API, no genera PDF todavía) */
document.getElementById("btnCajaFiltro")
  .addEventListener('click', () => {
    const p = getCajaParams();
    rangoTexto.textContent = `${p.desde} → ${p.hasta}`;
    modalPDF.show();
  });

/* Botón — generar Cierre de Caja */
document.getElementById('btnGenCierre')
  .addEventListener('click', () => {
    window.open(
      '/motoshoppy/analisis_comercial/api/get_cierre_caja.php?' + buildQueryFromCaja(),
      '_blank'
    );
  });

/* Botón — generar Libro Diario */
document.getElementById('btnGenLibro')
  .addEventListener('click', () => {
    window.open(
      '/motoshoppy/analisis_comercial/api/libro_diario.php?' + buildQueryFromCaja(),
      '_blank'
    );
  });


/* ------------------ ROTACIÓN ------------------ */
let tablaRotacion = null;

async function cargarRotacion(){

  const p = new URLSearchParams(getRotParams());
  const r = await fetch('/motoshoppy/analisis_comercial/api/get_rotacion_productos.php?'+p);
  const text = await r.text();

  let data;
  try { data = JSON.parse(text); }
  catch(e){
    console.error('Respuesta NO JSON en rotación:', text);
    return;
  }

  if(!data.ok) return;

  if(!tablaRotacion){
    tablaRotacion = $('#tablaRotacion').DataTable({
      data: data.data,
      columns:[
        {data:'producto'},
        {data:'unidades', className:'text-center'},
        {
          data:'rotacion',
          className:'text-center',
          render:r=>{
            const map = {Alta:'success', Baja:'danger', Media:'warning'};
            return `<span class="badge bg-${map[r]||'secondary'}">${r}</span>`;
          }
        }
      ],
      info:false,
      language:{ emptyTable:'No hay datos para el período seleccionado' }
    });

  } else {
    tablaRotacion.clear().rows.add(data.data).draw();
  }
}

document.getElementById("btnRotFiltro")
  .addEventListener('click', cargarRotacion);


/* ------------------ INIT LOAD ------------------ */
cargarKPIs();
cargarRotacion();
</script>


<?php include '../dashboard/footer.php'; ?>
