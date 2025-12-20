<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';
?>

<div class="content-header mb-4">
  <h2 class="text-info fw-bold">
    <i class="fa-solid fa-chart-line me-2"></i>
    Análisis Comercial
  </h2>
  <p class="text-white-50 mb-0">
    Panorama general de ventas, productos y desempeño comercial.
  </p>
</div>

<!-- ================= KPIs ================= -->
<div class="row g-3 mb-4" id="kpisComerciales">
  <!-- se cargan por JS -->
</div>

<!-- ================= EVOLUCIÓN ================= -->
<div class="card bg-dark text-white shadow-sm border-secondary mb-4">
  <div class="card-body">
    <h5 class="text-warning fw-bold mb-3">
      <i class="fa-solid fa-chart-area me-2"></i>
      Evolución de ventas
    </h5>
    <canvas id="graficoEvolucionVentas"></canvas>
  </div>
</div>

<!-- ================= TOPS ================= -->
<div class="row">
  <div class="col-md-6 mb-4">
    <div class="card bg-dark text-white shadow-sm border-secondary h-100">
      <div class="card-body">
        <h5 class="text-info fw-bold mb-3">
          <i class="fa-solid fa-box me-2"></i>
          Productos más vendidos
        </h5>
        <div id="topProductos"></div>
      </div>
    </div>
  </div>

  <div class="col-md-6 mb-4">
    <div class="card bg-dark text-white shadow-sm border-secondary h-100">
      <div class="card-body">
        <h5 class="text-success fw-bold mb-3">
          <i class="fa-solid fa-user-tie me-2"></i>
          Mejores vendedores
        </h5>
        <div id="topVendedores"></div>
      </div>
    </div>
  </div>
</div>

<script>
async function cargarKPIs() {
  try {
    const res = await fetch('/analisis_comercial/api/get_kpis.php');
    const data = await res.json();

    if (!data.ok) throw new Error(data.msg || 'Error KPIs');

    const variacionClase = data.variacion >= 0 ? 'text-success' : 'text-danger';
    const variacionIcono = data.variacion >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

    document.getElementById('kpisComerciales').innerHTML = `
      <div class="col-md-3 col-6">
        <div class="card bg-dark text-warning text-center shadow-sm">
          <div class="card-body">
            <i class="fa-solid fa-dollar-sign fa-2x mb-2"></i>
            <h4>$${Number(data.facturacion_mes).toLocaleString('es-AR')}</h4>
            <small>Facturación del mes</small>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="card bg-dark text-info text-center shadow-sm">
          <div class="card-body">
            <i class="fa-solid fa-receipt fa-2x mb-2"></i>
            <h4>${data.ventas_mes}</h4>
            <small>Ventas del mes</small>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="card bg-dark text-success text-center shadow-sm">
          <div class="card-body">
            <i class="fa-solid fa-credit-card fa-2x mb-2"></i>
            <h4>$${Number(data.ticket_promedio).toLocaleString('es-AR')}</h4>
            <small>Ticket promedio</small>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="card bg-dark ${variacionClase} text-center shadow-sm">
          <div class="card-body">
            <i class="fa-solid ${variacionIcono} fa-2x mb-2"></i>
            <h4>${data.variacion}%</h4>
            <small>Variación mensual</small>
          </div>
        </div>
      </div>
    `;

  } catch (e) {
    console.error(e);
  }
}

cargarKPIs();
</script>



<?php include '../dashboard/footer.php'; ?>


