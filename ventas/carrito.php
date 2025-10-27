<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';
?>
<link rel="stylesheet" href="ventas.css">
<link rel="stylesheet" href="carrito.css">

<div class="content-header d-flex justify-content-between align-items-center">
  <h2><i class="fa-solid fa-cart-shopping text-warning"></i> Carrito de Venta</h2>
  <button class="btn btn-outline-warning btn-sm" id="btnVaciar">
    <i class="fa-solid fa-trash"></i> Vaciar carrito
  </button>
</div>

<div class="content-body mt-3">
  <div class="card shadow-sm p-3 modulo">
    <table id="tablaCarrito" class="table table-dark table-hover table-sm align-middle mb-0">
      <thead>
        <tr>
          <th style="width:60px">Img</th>
          <th>Producto</th>
          <th style="width:100px" class="text-center">Cant.</th>
          <th style="width:120px" class="text-end">Precio</th>
          <th style="width:120px" class="text-end">Subtotal</th>
          <th style="width:60px"></th>
        </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
        <tr>
          <th colspan="4" class="text-end">Total:</th>
          <th id="totalCarrito" class="text-glow text-end">$ 0,00</th>
          <th></th>
        </tr>
      </tfoot>
    </table>

    <div class="mt-3 text-end">
      <button class="btn btn-success btnConfirmar">
        <i class="fa-solid fa-check"></i> Confirmar Venta
      </button>
    </div>
  </div>
</div>

<script>
/* ==== Helpers ==== */
const money = v => Number(v || 0).toLocaleString('es-AR', {
  minimumFractionDigits: 2, maximumFractionDigits: 2
});

/* ==== Renderizado ==== */
function renderCarrito() {
  const key = 'carrito';
  const carrito = JSON.parse(localStorage.getItem(key) || '[]');
  const tbody = document.querySelector('#tablaCarrito tbody');
  tbody.innerHTML = '';
  let total = 0;

  if (carrito.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="empty-cart">
          <i class="fa-solid fa-cart-arrow-down"></i><br>
          <span>Tu carrito está vacío</span>
        </td>
      </tr>`;
  }

  carrito.forEach((p, i) => {
    const subtotal = p.precio_expuesto * p.cantidad;
    total += subtotal;
    tbody.innerHTML += `
      <tr>
        <td><img src="${p.imagen || '/motoshoppy/imagenes/noimg.png'}"
                 class="img-fluid rounded-circle mini-img"></td>
        <td>${p.nombre}</td>
        <td class="text-center">
          <input type="number" min="1" value="${p.cantidad}" class="form-control form-control-sm text-center qtyInput"
                 data-index="${i}">
        </td>
        <td class="text-end">$ ${money(p.precio_expuesto)}</td>
        <td class="text-end">$ ${money(subtotal)}</td>
        <td class="text-center">
          <button class="btn btn-sm btn-outline-danger" onclick="eliminarDelCarrito(${i})">
            <i class="fa-solid fa-trash"></i>
          </button>
        </td>
      </tr>`;
  });

  document.getElementById('totalCarrito').textContent = `$ ${money(total)}`;

  document.querySelectorAll('.qtyInput').forEach(inp => {
    inp.addEventListener('change', e => {
      const idx = parseInt(e.target.dataset.index);
      const cant = Math.max(1, parseInt(e.target.value) || 1);
      carrito[idx].cantidad = cant;
      localStorage.setItem(key, JSON.stringify(carrito));
      renderCarrito();
    });
  });
}

/* ==== Funciones CRUD ==== */
function eliminarDelCarrito(index) {
  const key = 'carrito';
  const carrito = JSON.parse(localStorage.getItem(key) || '[]');
  carrito.splice(index, 1);
  localStorage.setItem(key, JSON.stringify(carrito));
  renderCarrito();
}

document.getElementById('btnVaciar').addEventListener('click', () => {
  if (confirm('¿Seguro que querés vaciar el carrito?')) {
    localStorage.removeItem('carrito');
    renderCarrito();
  }
});

document.querySelector('.btnConfirmar').addEventListener('click', () => {
  const carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
  if (carrito.length === 0) {
    Swal.fire({ icon: 'warning', title: 'Carrito vacío', text: 'Agregá productos antes de confirmar.' });
    return;
  }

  fetch('api_confirmar_venta.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(carrito)
  })
    .then(r => r.json())
    .then(resp => {
      if (resp.ok) {
        Swal.fire({ icon: 'success', title: 'Venta confirmada', text: 'La venta se registró correctamente.' });
        localStorage.removeItem('carrito');
        renderCarrito();
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: resp.msg || 'No se pudo confirmar la venta.' });
      }
    });
});

document.addEventListener('DOMContentLoaded', renderCarrito);
</script>

<?php include '../dashboard/footer.php'; ?>
