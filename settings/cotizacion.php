<?php
require_once '../settings/bootstrap.php';
include '../dashboard/nav.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
<?php include '../dashboard/footer.php'; ?>
