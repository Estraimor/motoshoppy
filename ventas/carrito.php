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
          <th id="totalCarrito" class="text-glow text-end">₲ 0,00</th>
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
  minimumFractionDigits: 2,
  maximumFractionDigits: 2
});

/* ==== Renderizado del carrito ==== */
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
    const imgSrc = p.imagen
      ? `/motoshoppy/${String(p.imagen).replace(/\\\\/g, '/').replace(/^\/+/, '')}`
      : '/motoshoppy/imagenes/noimg.png';
    tbody.innerHTML += `
      <tr>
        <td><img src="${imgSrc}" class="img-fluid rounded-circle mini-img"></td>
        <td>
          <div class="fw-semibold">${p.nombre}</div>
          <div class="small text-secondary">
            ${p.codigo ? `Cod: ${p.codigo}` : ''}
            ${p.nombre_marca ? `${p.codigo ? ' · ' : ''}Marca: ${p.nombre_marca}` : ''}
            ${p.modelo ? `${(p.codigo || p.nombre_marca) ? ' · ' : ''}Modelo: ${p.modelo}` : ''}
            ${p.nombre_categoria ? `${(p.codigo || p.nombre_marca || p.modelo) ? ' · ' : ''}Cat: ${p.nombre_categoria}` : ''}
          </div>
          ${p.stock_estado ? `<div class="small">
            <span class="badge ${p.stock_estado === 'ok' ? 'bg-success' : (p.stock_estado === 'bajo_stock' ? 'bg-warning text-dark' : 'bg-danger')}">
              Stock: ${typeof p.stock_actual !== 'undefined' ? p.stock_actual : '-'}
            </span>
          </div>` : ''}
        </td>
        <td class="text-center">
          <input type="number" min="1" value="${p.cantidad}" class="form-control form-control-sm text-center qtyInput" data-index="${i}">
        </td>
        <td class="text-end">₲ ${money(p.precio_expuesto)}</td>
        <td class="text-end text-warning fw-bold">₲ ${money(subtotal)}</td>
        <td class="text-center">
          <button class="btn btn-sm btn-outline-danger" onclick="eliminarDelCarrito(${i})">
            <i class="fa-solid fa-trash"></i>
          </button>
        </td>
      </tr>`;
  });

  document.getElementById('totalCarrito').textContent = `₲ ${money(total)}`;

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
  Swal.fire({
    icon: 'warning',
    title: '¿Vaciar carrito?',
    text: 'Se eliminarán todos los productos.',
    showCancelButton: true,
    confirmButtonText: 'Sí, vaciar',
    cancelButtonText: 'Cancelar'
  }).then(r => {
    if (r.isConfirmed) {
      localStorage.removeItem('carrito');
      renderCarrito();
    }
  });
});

/* ==== Confirmar venta ==== */
document.querySelector('.btnConfirmar').addEventListener('click', async () => {
  const carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
  if (carrito.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Carrito vacío',
      text: 'Agregá productos antes de confirmar.'
    });
    return;
  }

  const total = carrito.reduce((a, b) => a + b.precio_expuesto * b.cantidad, 0);

  // Paso 1: elegir tipo de comprobante
  const { value: comprobante } = await Swal.fire({
    title: 'Tipo de comprobante',
    input: 'radio',
    inputOptions: {
      'ticket': 'Ticket',
      'factura': 'Factura',
      'ninguno': 'Ninguno'
    },
    inputValue: 'ticket',
    confirmButtonText: 'Continuar',
    showCancelButton: true,
    cancelButtonText: 'Cancelar',
    inputValidator: v => !v && 'Seleccioná una opción'
  });
  if (!comprobante) return;

  // Paso 2: ingresar método de pago y datos del cliente
  const htmlPago = `
  <div class="text-start">
    <label class="form-label fw-bold mt-2">Método de pago</label>
    <select id="metodoPago" class="form-select">
      <option value="efectivo">Efectivo</option>
      <option value="transferencia">Transferencia</option>
      <option value="tarjeta">Tarjeta</option>
      <option value="otro">Otro</option>
    </select>
    <div id="otroMetodo" class="mt-2 d-none">
      <input id="otroTexto" class="form-control" placeholder="Describí el método de pago...">
    </div>

    ${
      comprobante === 'factura'
        ? `
          <hr class="my-3">
          <label class="form-label fw-bold">Datos del cliente (Factura)</label>
          <input id="cliNombreFactura" class="form-control mb-2" placeholder="Nombre">
          <input id="cliApellidoFactura" class="form-control mb-2" placeholder="Apellido">
          <input id="cliDniFactura" class="form-control mb-2" placeholder="DNI">
          <input id="cliCelularFactura" class="form-control mb-2" placeholder="Celular">`
        : `
          <hr class="my-3">
          <label class="form-label fw-bold">DNI del cliente (Ticket)</label>
          <input id="cliDniTicket" class="form-control mb-2" placeholder="DNI">`
    }
  </div>`;

  const { value: confirmar } = await Swal.fire({
    title: 'Confirmar venta',
    html: `
      <div class="text-start">
        <p><strong>Productos:</strong> ${carrito.length}</p>
        <p><strong>Total:</strong> ₲ ${money(total)}</p>
        <p><strong>Comprobante:</strong> ${comprobante.toUpperCase()}</p>
        ${htmlPago}
      </div>`,
    width: 600,
    confirmButtonText: 'Finalizar venta',
    showCancelButton: true,
    cancelButtonText: 'Cancelar',
    didOpen: () => {
      const sel = document.getElementById('metodoPago');
      sel.addEventListener('change', () => {
        document.getElementById('otroMetodo').classList.toggle('d-none', sel.value !== 'otro');
      });
    },
    preConfirm: () => {
      const metodo = document.getElementById('metodoPago').value;
      const metodo_desc = metodo === 'otro' ? document.getElementById('otroTexto').value.trim() : metodo;

      let cliente = null;
      if (comprobante === 'factura') {
        cliente = {
          nombre: document.getElementById('cliNombreFactura').value.trim(),
          apellido: document.getElementById('cliApellidoFactura').value.trim(),
          dni: document.getElementById('cliDniFactura').value.trim(),
          celular: document.getElementById('cliCelularFactura').value.trim()
        };
      } else if (comprobante === 'ticket') {
        cliente = { dni: document.getElementById('cliDniTicket').value.trim() };
      }
      return { metodo, metodo_desc, cliente };
    }
  });

  if (!confirmar) return;

  // Paso 3: envío al backend
  const payload = {
    tipo_comprobante: comprobante,
    metodo_pago: confirmar.metodo_desc,
    productos: carrito,
    total,
    cliente: confirmar.cliente
  };

  try {
    const res = await fetch('/motoshoppy/ventas/api_comprar.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();

    if (data.ok) {
      Swal.fire({
        icon: 'success',
        title: '✅ Venta completada',
        text: `Comprobante: ${comprobante.toUpperCase()} - ${payload.metodo_pago}`,
        timer: 1800,
        showConfirmButton: false
      });

      localStorage.removeItem('carrito');
      renderCarrito();

      // 🧾 Abrir PDF correspondiente
      const dni = payload.cliente?.dni || '';
      if (data.tipo_comprobante === 'ticket') {
        window.open(`/motoshoppy/ventas/generar_ticket.php?id=${data.venta_id}&dni=${encodeURIComponent(dni)}`, '_blank');
      } else if (data.tipo_comprobante === 'factura') {
        window.open(`/motoshoppy/ventas/generar_factura.php?id=${data.venta_id}`, '_blank');
      }
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.msg || 'No se pudo confirmar la venta.'
      });
    }
  } catch (err) {
    console.error(err);
    Swal.fire({
      icon: 'error',
      title: 'Error de conexión',
      text: 'No se pudo contactar con el servidor.'
    });
  }
});

document.addEventListener('DOMContentLoaded', renderCarrito);
</script>


<?php include '../dashboard/footer.php'; ?>
