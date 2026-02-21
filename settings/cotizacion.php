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
                <h6>Última actualización</h6>
                <small id="fechaCotizacion">-</small>
            </div>
        </div>

        <button class="btn btn-warning fw-bold"
                data-bs-toggle="modal"
                data-bs-target="#modalCotizacion">
            ✏ Actualizar cotización
        </button>


        <div class="modal fade" id="modalCotizacion" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-cotizacion">

      <div class="modal-header border-0">
        <h5 class="modal-title">
          <i class="fa-solid fa-pen-to-square me-2"></i>
          Actualizar cotización
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="formCotizacion">
        <div class="modal-body">

          <label class="cotizacion-label">
            <i class="fa-solid fa-dollar-sign"></i>
            1 USD en ARS
          </label>
          <input type="number" step="0.01" name="usd_ars" id="usd_ars"
                 class="cotizacion-input" required>

          <label class="cotizacion-label mt-4">
            <i class="fa-solid fa-coins"></i>
            1 USD en PYG
          </label>
          <input type="number" step="0.01" name="usd_pyg" id="usd_pyg"
                 class="cotizacion-input" required>

          <label class="cotizacion-label mt-4">
            <i class="fa-solid fa-money-bill"></i>
            1 ARS en PYG
          </label>
          <input type="number" step="0.0001" name="ars_pyg" id="ars_pyg"
                 class="cotizacion-input" readonly>

        </div>

        <div class="modal-footer border-0">
          <button type="submit" class="btn-guardar-cotizacion">
            <i class="fa-solid fa-floppy-disk me-2"></i>
            Guardar
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

    </div>

</div>


<script>
async function cargarCotizacion() {
  try {
    const res = await fetch('/motoshoppy/api/get_cotizacion.php');
    const data = await res.json();

    if (!data || !data.usd_ars) return;

    document.getElementById('usdArs').innerText =
        parseFloat(data.usd_ars).toFixed(2);

    document.getElementById('usdPyg').innerText =
        parseFloat(data.usd_pyg).toLocaleString('es-PY');

    document.getElementById('fechaCotizacion').innerText =
        new Date(data.fecha_actualizacion).toLocaleString();

    document.getElementById('usd_ars').value = data.usd_ars;
    document.getElementById('usd_pyg').value = data.usd_pyg;

  } catch (e) {
    console.error(e);
  }
}

document.getElementById('formCotizacion')
.addEventListener('submit', async (e) => {

  e.preventDefault();

  const formData = new FormData(e.target);

  const res = await fetch('/motoshoppy/api/update_cotizacion.php', {
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

});

cargarCotizacion();
</script>

<?php include '../dashboard/footer.php'; ?>
