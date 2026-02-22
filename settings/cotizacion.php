<?php
require_once '../settings/auditoria.php';
include '../dashboard/nav.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="cotizacion.css">

<div class="container py-5">

    <h2 class="fw-bold mb-4">💱 Cotización</h2>

    <div class="card bg-dark text-white border-secondary shadow-lg p-4">

        <div class="row text-center mb-4">
            <div class="col">
                <h6>USD → ARS</h6>
                <h3 id="usdArs">-</h3>
            </div>
            <div class="col">
                <h6>USD → PYG</h6>
                <h3 id="usdPyg">-</h3>
            </div>
            <div class="col">
                <h6>ARS → PYG</h6>
                <h3 id="arsPygMostrar">-</h3>
            </div>
            <div class="col">
                <h6>Última actualización</h6>
                <small id="fechaCotizacion">-</small>
            </div>
        </div>

        <button class="btn btn-warning fw-bold"
                data-bs-toggle="modal"
                data-bs-target="#modalCotizacion">
            ✏ Actualizar cotización
        </button>

    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalCotizacion" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white">

      <div class="modal-header border-0">
        <h5 class="modal-title">
          <i class="fa-solid fa-pen-to-square me-2"></i>
          Actualizar cotización
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="formCotizacion">
        <div class="modal-body">

          <label class="form-label">1 USD en ARS</label>
          <input type="number" step="0.01" name="usd_ars" id="usd_ars"
                 class="form-control bg-dark text-white border-secondary mb-3" required>

          <label class="form-label">1 USD en PYG</label>
          <input type="number" step="0.01" name="usd_pyg" id="usd_pyg"
                 class="form-control bg-dark text-white border-secondary mb-3" required>

          <label class="form-label">1 ARS en PYG (manual)</label>
          <input type="number" step="0.0001" name="ars_pyg" id="ars_pyg"
                 class="form-control bg-dark text-white border-secondary" required>

        </div>

        <div class="modal-footer border-0">
          <button type="submit" class="btn btn-warning w-100 fw-bold">
            Guardar
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<script>
// ===============================
// CARGAR ÚLTIMA COTIZACIÓN
// ===============================
async function cargarCotizacion() {
  try {

    const res = await fetch('/motoshoppy/settings/api/get_cotizacion.php');

    if (!res.ok) throw new Error("Error HTTP");

    const data = await res.json();

    if (!data || Object.keys(data).length === 0) return;

    // ====== FORMATEO VISUAL ======

document.getElementById('usdArs').innerText =
  Number(data.usd_ars).toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

document.getElementById('usdPyg').innerText =
  Number(data.usd_pyg).toLocaleString('es-PY', {
    maximumFractionDigits: 0
  });

document.getElementById('arsPygMostrar').innerText =
  Number(data.ars_pyg).toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

    // ====== FECHA ======

    document.getElementById('fechaCotizacion').innerText =
      new Date(data.fecha_actualizacion).toLocaleString('es-AR');

    // ====== CARGAR VALORES EN MODAL ======

    document.getElementById('usd_ars').value = data.usd_ars ?? '';
    document.getElementById('usd_pyg').value = data.usd_pyg ?? '';
    document.getElementById('ars_pyg').value = data.ars_pyg ?? '';

  } catch (e) {
    console.error("Error cargando cotización:", e);
  }
}

// ===============================
// GUARDAR COTIZACIÓN
// ===============================
document.getElementById('formCotizacion')
.addEventListener('submit', async (e) => {

  e.preventDefault();

  const formData = new FormData(e.target);

  try {

    const res = await fetch('/motoshoppy/settings/api/update_cotizacion.php', {
      method: 'POST',
      body: formData
    });

    const r = await res.json();

    if (r.ok) {

      Swal.fire({
        icon: 'success',
        title: 'Cotización actualizada',
        timer: 1500,
        showConfirmButton: false
      });

      bootstrap.Modal
        .getInstance(document.getElementById('modalCotizacion'))
        .hide();

      cargarCotizacion();

    } else {
      Swal.fire('Error', r.msg || 'No se pudo actualizar', 'error');
    }

  } catch (err) {
    console.error("Error guardando:", err);
  }

});

// Inicializar
cargarCotizacion();
</script>

<?php include '../dashboard/footer.php'; ?>