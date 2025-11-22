<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';
?>
<link rel="stylesheet" href="ventas.css">

<div class="content-header d-flex justify-content-between align-items-center">
  <h2><i class="fa-solid fa-bolt"></i> Punto de Venta</h2>
  <div class="d-flex align-items-center gap-2">
    <input id="buscarRapido" class="form-control form-control-sm bg-dark text-light border-secondary"
           placeholder="Buscar nombre o código (Ctrl+K)">
    <button class="btn btn-outline-warning btn-sm" data-bs-toggle="offcanvas" data-bs-target="#panelSettings">
      <i class="fa-solid fa-sliders"></i> Filtros
    </button>
  </div>
</div>

<div class="content-body">
  <div class="row g-3">
    <!-- Tabla de productos -->
    <div class="col-lg-8">
      <div class="card shadow-sm p-2 modulo">
        <table id="tablaVentas" class="table table-dark table-hover table-sm w-100 align-middle">
          <thead>
          <tr>
            
            <th style="width:100px">Código</th>
            <th>Nombre</th>
            <th>Modelo</th>
            <th>Marca</th>
            <th>Precio</th>
            <th>Stock</th>
            <th style="width:130px">Acciones</th>
          </tr>
        </thead>

          <tbody></tbody>
        </table>
      </div>
    </div>

    <!-- Panel de detalle / agregado rápido -->
<div class="col-lg-4">
  <div id="panelDetalle" class="card shadow-sm p-2 modulo" style="min-height: 360px;">
    <div class="text-center text-secondary" id="detalleVacio">
      <i class="fa-solid fa-image fa-2x mb-2"></i>
      <p>Seleccioná un producto para ver detalles y agregar al carrito.</p>
    </div>

    <div id="detalleContenido" class="d-none">
      <div class="text-center mb-2">
        <img id="detImagen" src="" alt="imagen" class="img-fluid rounded"
             style="max-height: 220px; object-fit: contain;">
      </div>
      <h5 id="detNombre" class="mb-1"></h5>
      <div class="d-flex justify-content-between small text-secondary mb-2">
        <span id="detCodigo"></span>
        <span id="detMarca"></span>
      </div>
      <div class="small" id="detDesc"></div>
      <hr class="border-secondary my-2">
      <div class="d-flex align-items-center justify-content-between">
        <div class="h5 m-0 text-success" id="detPrecio"></div>
        <div class="input-group" style="width: 140px;">
          <button class="btn btn-outline-warning btn-sm" id="menosCant">-</button>
          <input type="number" min="1" value="1" id="detCantidad"
                 class="form-control form-control-sm bg-dark text-light border-secondary text-center">
          <button class="btn btn-outline-warning btn-sm" id="masCant">+</button>
        </div>
      </div>
      <button id="btnAgregar" class="btn btn-success w-100 mt-2">
        <i class="fa-solid fa-cart-plus"></i> Agregar al carrito (Enter)
      </button>
       <!-- 🔵 Botón de compra directa  -->
  <button id="btnComprarAhora" class="btn btn-primary w-100 fw-bold mt-3 py-2">
    <i class="fa-solid fa-bolt me-1"></i> Comprar ahora
  </button>
    </div>
  </div>

 
</div>


<!-- ===== OFFCANVAS FILTROS (tu bloque) ===== -->
<div class="offcanvas offcanvas-end bg-dark text-light" tabindex="-1" id="panelSettings">
  <div class="offcanvas-header border-bottom border-secondary">
    <h5 class="offcanvas-title"><i class="fa-solid fa-sliders"></i> Filtros y Configuración</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>

  <div class="offcanvas-body">
    <!-- Buscar -->
    <div class="mb-3">
      <label class="form-label text-warning"><i class="fa-solid fa-magnifying-glass"></i> Buscar por nombre o código</label>
      <input type="text" id="filtroBusqueda" class="form-control bg-dark text-light border-secondary" placeholder="Ej: Motul, 10W40...">
    </div>

    <!-- Marca -->
    <div class="mb-3">
      <label class="form-label text-warning"><i class="fa-solid fa-tags"></i> Marca</label>
      <select id="filtroMarca" class="form-select bg-dark text-light border-secondary">
        <option value="">Todas</option>
        <?php
          $marcas = $conexion->query("SELECT idmarcas, nombre_marca FROM marcas ORDER BY nombre_marca ASC");
          while($m = $marcas->fetch(PDO::FETCH_ASSOC)){
              echo "<option value='{$m['idmarcas']}'>{$m['nombre_marca']}</option>";
          }
        ?>
      </select>
    </div>

    <!-- Categoría -->
    <div class="mb-3">
      <label class="form-label text-warning"><i class="fa-solid fa-layer-group"></i> Categoría</label>
      <select id="filtroCategoria" class="form-select bg-dark text-light border-secondary">
        <option value="">Todas</option>
        <?php
          $categorias = $conexion->query("SELECT idCategoria, nombre_categoria FROM categoria ORDER BY nombre_categoria ASC");
          while($c = $categorias->fetch(PDO::FETCH_ASSOC)){
              echo "<option value='{$c['idCategoria']}'>{$c['nombre_categoria']}</option>";
          }
        ?>
      </select>
    </div>

    <!-- Rango de precios -->
    <div class="mb-3">
      <label class="form-label text-warning d-flex align-items-center gap-2">
        <i class="fa-solid fa-dollar-sign"></i> Rango de precios
      </label>
      <div class="input-group">
        <span class="input-group-text bg-dark text-light border-secondary">$</span>
        <input type="number" id="precioMin" class="form-control bg-dark text-light border-secondary" placeholder="Mínimo" min="0">
        <span class="input-group-text bg-dark text-light border-secondary">–</span>
        <input type="number" id="precioMax" class="form-control bg-dark text-light border-secondary" placeholder="Máximo" min="0">
      </div>
    </div>

    <!-- Ordenar -->
    <div class="mb-3">
      <label class="form-label text-warning"><i class="fa-solid fa-arrow-down-a-z"></i> Ordenar por</label>
      <select id="ordenarPor" class="form-select bg-dark text-light border-secondary">
        <option value="">Predeterminado</option>
        <option value="precio_asc">Precio: más bajo a más alto</option>
        <option value="precio_desc">Precio: más alto a más bajo</option>
        <option value="nombre_asc">Nombre: A → Z</option>
        <option value="nombre_desc">Nombre: Z → A</option>
      </select>
    </div>

    <hr class="border-secondary">
    <div class="d-flex justify-content-between">
      <button class="btn btn-outline-warning" id="btnAplicarFiltros"><i class="fa-solid fa-check"></i> Aplicar</button>
      <button class="btn btn-outline-light" id="btnLimpiarFiltros"><i class="fa-solid fa-rotate-left"></i> Limpiar</button>
    </div>
  </div>
</div>

<script>
/* ==== Helpers ==== */
const money = v => {
  const n = Number(v || 0);
  return n.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
const debounce = (fn, ms = 250) => {
  let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
};

/* ==== DataTable ==== */
let tabla;
let productoSeleccionado = null;

/* === función que arma los parámetros === */
function paramsActuales() {
  return {
    q: document.getElementById('buscarRapido')?.value.trim() || '',
    marca: document.getElementById('filtroMarca')?.value || '',
    categoria: document.getElementById('filtroCategoria')?.value || '',
    pmin: document.getElementById('precioMin')?.value || '',
    pmax: document.getElementById('precioMax')?.value || '',
    ordenar: document.getElementById('ordenarPor')?.value || ''
  };
}

/* === iniciar al cargar === */
document.addEventListener('DOMContentLoaded', () => {
  // hotkey Ctrl+K para búsqueda rápida
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      const b = document.getElementById('buscarRapido');
      if (b) { b.focus(); b.select(); }
    }
  });

  // Inicializar DataTable
  tabla = new DataTable('#tablaVentas', {
    ajax: (d, cb) => {
      const p = paramsActuales();
      const qs = new URLSearchParams(p).toString();
      const url = `/motoshoppy/ventas/api_buscar_productos.php?${qs}`;
      fetch(url)
        .then(r => r.json())
        .then(rows => cb({ data: rows }))
        .catch(() => cb({ data: [] }));
    },
    deferRender: true,
    pageLength: 10,
    lengthChange: false,
    ordering: false,
    columns: [
      { data: 'codigo' },
      { data: 'nombre' },
      { data: 'modelo' },
      { data: 'nombre_marca' },
      {
        data: 'precio_expuesto',
        render: v => `<span class="text-success fw-semibold">$ ${money(v)}</span>`
      },
      {
        data: 'stock_actual',
        render: (v, _, row) => {
          let color = 'text-success', txt = v;
          if (row.stock_estado === 'bajo_stock') color = 'text-warning';
          if (row.stock_estado === 'sin_stock') color = 'text-danger', txt = 'Sin stock';
          return `<span class="${color} fw-bold">${txt}</span>`;
        }
      },
      {
        data: null,
        render: (_, __, row) => `
          <div class="input-group input-group-sm">
            <button class="btn btn-outline-warning btnMenos" ${row.stock_estado === 'sin_stock' ? 'disabled' : ''}>-</button>
            <input type="number" value="1" min="1" class="form-control text-center qtyInput" ${row.stock_estado === 'sin_stock' ? 'disabled' : ''}>
            <button class="btn btn-outline-warning btnMas" ${row.stock_estado === 'sin_stock' ? 'disabled' : ''}>+</button>
            <button class="btn btn-success btnAgregar ms-2" ${row.stock_estado === 'sin_stock' ? 'disabled' : ''}>
              <i class="fa-solid fa-cart-plus"></i>
            </button>
          </div>`
      }
    ],
    rowCallback: (row, data) => {
      if (data.stock_estado === 'sin_stock') {
        row.style.opacity = '0.6';
        row.style.pointerEvents = 'none';
      } else {
        row.addEventListener('click', (e) => {
          if (e.target.closest('.input-group')) return;
          mostrarDetalle(data);
        });
      }

      const menos = row.querySelector('.btnMenos');
      const mas = row.querySelector('.btnMas');
      const agregar = row.querySelector('.btnAgregar');
      if (menos && mas && agregar && data.stock_estado !== 'sin_stock') {
        menos.onclick = e => {
          e.stopPropagation();
          const input = row.querySelector('.qtyInput');
          input.value = Math.max(1, parseInt(input.value || 1) - 1);
        };
        mas.onclick = e => {
          e.stopPropagation();
          const input = row.querySelector('.qtyInput');
          input.value = parseInt(input.value || 1) + 1;
        };
        agregar.onclick = e => {
          e.stopPropagation();
          const qty = parseInt(row.querySelector('.qtyInput').value || 1);
          agregarAlCarrito(data, qty);
        };
      }
    },
    language: {
      search: 'Buscar:',
      zeroRecords: 'Sin resultados',
      info: 'Mostrando _START_ a _END_ de _TOTAL_',
      infoEmpty: 'Sin registros',
      paginate: { previous: '‹', next: '›' }
    }
  });

  // 🔹 Búsqueda rápida
  const buscarInput = document.getElementById('buscarRapido');
  if (buscarInput) buscarInput.addEventListener('input', debounce(() => tabla.ajax.reload(), 300));

  // 🔹 Aplicar filtros
  const aplicarBtn = document.getElementById('btnAplicarFiltros');
  if (aplicarBtn) {
    aplicarBtn.addEventListener('click', (e) => {
      e.preventDefault();
      tabla.ajax.reload();
    });
  }

  // 🔹 Limpiar filtros
  const limpiarBtn = document.getElementById('btnLimpiarFiltros');
  if (limpiarBtn) {
    limpiarBtn.addEventListener('click', (e) => {
      e.preventDefault();
      ['filtroMarca', 'filtroCategoria', 'precioMin', 'precioMax', 'ordenarPor'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        else el.value = '';
      });
      tabla.ajax.reload();
    });
  }
});

async function inicializarCotizacion() {
  try {
    const res = await fetch('/motoshoppy/api/get_cotizacion.php');
    const data = await res.json();
    if (data && data.usd_pyg && data.ars_pyg) {
      localStorage.setItem('cotizacion', JSON.stringify(data));
      console.log("💱 Cotización actualizada:", data);
    } else {
      console.warn("⚠ No se recibieron datos válidos de cotización.");
    }
  } catch (e) {
    console.error("Error al cargar cotización:", e);
  }
}
inicializarCotizacion();

/* ==== Detalle ==== */
function mostrarDetalle(p) {
  productoSeleccionado = p;
  document.getElementById('detalleVacio').classList.add('d-none');
  document.getElementById('detalleContenido').classList.remove('d-none');

  // Imagen
  const img = document.getElementById('detImagen');
  img.src = p.imagen ? `/motoshoppy/${p.imagen.replace(/\\/g, '/')}` : '/motoshoppy/imagenes/noimg.png';
  img.onclick = () => abrirZoom(img.src);

  // Datos generales
  document.getElementById('detNombre').textContent = p.nombre;
  document.getElementById('detCodigo').textContent = p.codigo ? `Código: ${p.codigo}` : '';
  document.getElementById('detMarca').textContent = p.nombre_marca ? `Marca: ${p.nombre_marca}` : '';

  // === Cotizaciones ===
  const cot = JSON.parse(localStorage.getItem('cotizacion') || '{}');
  const usd_pyg = parseFloat(cot.usd_pyg || 6000);
  const ars_pyg = parseFloat(cot.ars_pyg || 4.5);

  // === Precio base ===
  const basePYG = Number(p.precio_expuesto || 0);

  // ✅ Traer listas desde API y recién ahí armar el bloque HTML
  fetch('/motoshoppy/ventas/get_listas_precios.php')
    .then(res => res.json())
    .then(data => {
      const listas = data.listas || [];

     const opcionesListas = listas.map(l => {
  const precioLista = basePYG * (1 - (l.porcentaje_descuento / 100));
  return `<option value="${precioLista}">
    ${l.nombre_lista} (-${l.porcentaje_descuento}%)
  </option>`;
}).join('');


      const precioHtml = `
        <div class="p-2">
          <!-- Precio base -->
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold text-success fs-5">
              <i class="fa-solid fa-sack-dollar me-1"></i>Precio base:
            </span>
            <span id="precioBase" class="text-success fw-bold fs-5">₲ ${money(basePYG)}</span>
          </div>

          <!-- Conversión base -->
          <div class="bg-dark bg-opacity-25 p-2 rounded small text-light mb-3">
            <div class="d-flex justify-content-between align-items-center">
              <div><i class="fa-solid fa-dollar-sign text-warning me-1"></i><span class="fw-semibold">USD:</span></div>
              <div id="usdBase" class="fw-semibold">-</div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
              <div><i class="fa-solid fa-money-bill-wave text-info me-1"></i><span class="fw-semibold">ARS:</span></div>
              <div id="arsBase" class="fw-semibold">-</div>
            </div>
          </div>

          <!-- Select dinámico de listas -->
          <label for="selectPrecioLista" class="form-label text-warning fw-semibold mb-1">
            <i class="fa-solid fa-list me-1"></i>Precio de lista:
          </label>
          <select id="selectPrecioLista" class="form-select form-select-sm w-100 mt-1">
            <option value="${basePYG}">Base - ₲ ${money(basePYG)}</option>
            ${opcionesListas}
          </select>

          <!-- Total final -->
          <div class="text-center mt-3 bg-black bg-opacity-25 rounded py-3 px-2">
            <span class="fw-semibold text-info fs-5 d-block mb-1">
              <i class="fa-solid fa-cart-shopping me-1"></i>Total final:
            </span>
            <div id="precioTotal" class="fw-bold fs-4 text-info">₲ ${money(basePYG)}</div>
            <div class="small text-white-50 mt-1">
              ≈ <span id="usdVal">-</span> USD | <span id="arsVal">-</span> ARS
            </div>
          </div>
        </div>`;

      document.getElementById('detPrecio').innerHTML = precioHtml;
      // Ajustar etiqueta de Base y asegurar Select2 con reintento
      try {
        const baseOpt = document.querySelector(`#selectPrecioLista option[value="${basePYG}"]`);
        if (baseOpt) baseOpt.textContent = 'Base (0%)';
      } catch (e) {}
      (function initSelect2Retry(){
        if (window.jQuery && $.fn && $.fn.select2) {
          const el = document.querySelector('#selectPrecioLista');
          if (el && !el.classList.contains('select2-hidden-accessible')) {
            $('#selectPrecioLista').select2({
              dropdownParent: $('#panelDetalle'),
              width: '100%',
              minimumResultsForSearch: Infinity,
              dropdownAutoWidth: true
            });
          }
        } else { setTimeout(initSelect2Retry, 200); }
      })();
      // ✅ Convertir a Select2 con scroll *después* de insertar el select
  // Inicializar Select2 después de renderizar el HTML
requestAnimationFrame(() => {
  $('#selectPrecioLista').select2({
    dropdownParent: $('#panelDetalle'), // ESTE es el contenedor correcto
    width: '100%',
    minimumResultsForSearch: Infinity
  });
});



      // === Actualización dinámica ===
      const inputCantidad = document.getElementById('detCantidad');
      inputCantidad.value = 1;

      const actualizarTotal = () => {
        const precioSel = parseFloat(document.getElementById('selectPrecioLista').value);
        const cantidad = parseInt(inputCantidad.value || 1);
        const totalPYG = precioSel * cantidad;

        document.getElementById('precioTotal').textContent = `₲ ${money(totalPYG)}`;
        document.getElementById('usdVal').textContent = (totalPYG / usd_pyg).toFixed(2);
        document.getElementById('arsVal').textContent = money((totalPYG / ars_pyg).toFixed(0));
        document.getElementById('usdBase').textContent = (basePYG / usd_pyg).toFixed(2);
        document.getElementById('arsBase').textContent = money((basePYG / ars_pyg).toFixed(0));
      };

      ['input', 'change'].forEach(ev => inputCantidad.addEventListener(ev, actualizarTotal));
      document.getElementById('selectPrecioLista').addEventListener('change', actualizarTotal);
      document.getElementById('menosCant').onclick = () => { inputCantidad.value = Math.max(1, parseInt(inputCantidad.value) - 1); actualizarTotal(); };
      document.getElementById('masCant').onclick = () => { inputCantidad.value = parseInt(inputCantidad.value) + 1; actualizarTotal(); };

      document.getElementById('btnAgregar').onclick = () => {
        const qty = parseInt(inputCantidad.value || 1);
        const precioSeleccionado = parseFloat(document.getElementById('selectPrecioLista').value);
        agregarAlCarrito({ ...p, precio_expuesto: precioSeleccionado }, qty);
      };

      actualizarTotal();
    });

  // === Descripción ===
  let descHtml = '';
  if (p.descripcion) {
    try {
      const json = JSON.parse(p.descripcion);
      descHtml = Object.entries(json).map(([k, v]) =>
        `<div><span class='text-warning fw-semibold'>${k}:</span> ${Array.isArray(v) ? v.join('/') : v}</div>`).join('');
    } catch {
      descHtml = `<div class="text-light">${p.descripcion}</div>`;
    }
  }

  document.getElementById('detDesc').innerHTML = `
    <div class="bg-dark bg-opacity-25 rounded p-2 mb-2">
      ${descHtml || '<em class="text-muted">Sin descripción</em>'}
    </div>
    <hr class="border-secondary">
    <div>Stock actual:
      <span class="fw-bold text-${
        p.stock_estado === 'ok' ? 'success' : p.stock_estado === 'bajo_stock' ? 'warning' : 'danger'
      }">${p.stock_actual}</span> / Mínimo ${p.stock_minimo || 0}
    </div>`;
}





/* ==== Zoom ==== */
function abrirZoom(src) {
  const overlay = document.createElement('div');
  overlay.className = 'zoomOverlayCustom';
  overlay.innerHTML = `<div class="zoomInnerCustom"><img src="${src}" class="zoomImgCustom"></div>`;
  document.body.appendChild(overlay);
  overlay.addEventListener('click', () => overlay.remove());
}

/* ==== Carrito ==== */
function agregarAlCarrito(prod, cantidad = 1) {
  const key = 'carrito';
  const carrito = JSON.parse(localStorage.getItem(key) || '[]');
  const idx = carrito.findIndex(it => it.idProducto == prod.idProducto);
  if (idx >= 0) carrito[idx].cantidad += cantidad;
  else carrito.push({
    idProducto: prod.idProducto,
    nombre: prod.nombre,
    precio_expuesto: prod.precio_expuesto,
    imagen: prod.imagen,
    cantidad,
    // Detalles adicionales para mostrar en el carrito
    codigo: prod.codigo,
    nombre_marca: prod.nombre_marca,
    modelo: prod.modelo,
    nombre_categoria: prod.nombre_categoria,
    stock_actual: prod.stock_actual,
    stock_estado: prod.stock_estado
  });
  localStorage.setItem(key, JSON.stringify(carrito));

  if (window.Swal) Swal.fire({ toast: true, position: 'top-end', timer: 1200, showConfirmButton: false, icon: 'success', title: `Agregado: ${prod.nombre}` });

  ['cartCountTop', 'cartCountSide', 'cartCount'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = carrito.reduce((a, b) => a + (b.cantidad || 1), 0);
  });
}

/* ==== Compra directa con modal completo ==== */
document.getElementById('btnComprarAhora').addEventListener('click', async () => {
  const p = productoSeleccionado;
  if (!p) {
    Swal.fire({
      icon: 'info',
      title: 'Seleccioná un producto primero',
      timer: 1300,
      showConfirmButton: false
    });
    return;
  }

  const precioSeleccionado = parseFloat(document.querySelector('#selectPrecioLista')?.value || p.precio_expuesto || 0);
const qty = parseInt(document.getElementById('detCantidad')?.value || 1);

const productoConPrecio = { 
  ...p, 
  precio_expuesto: precioSeleccionado, 
  cantidad: qty 
};


  // Modal inicial: tipo de comprobante
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
    inputValidator: (v) => !v && 'Seleccioná una opción'
  });
  if (!comprobante) return;

  // --- Construimos contenido HTML dinámico ---
  // --- Construimos contenido HTML dinámico ---
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
  </div>
`;



  const { value: confirmar } = await Swal.fire({
    title: 'Confirmar venta',
    html: `
      <div class="text-start">
        <p><strong>Producto:</strong> ${productoConPrecio.nombre}</p>
        <p><strong>Total:</strong> ₲ ${money(productoConPrecio.precio_expuesto)}</p>
        <p><strong>Comprobante:</strong> ${comprobante.toUpperCase()}</p>
        ${htmlPago}
      </div>
    `,
    width: 600,
    confirmButtonText: 'Finalizar venta',
    showCancelButton: true,
    cancelButtonText: 'Cancelar',
    didOpen: () => {
      const sel = document.getElementById('metodoPago');
      sel.addEventListener('change', () => {
        document.getElementById('otroMetodo').classList.toggle('d-none', sel.value !== 'otro');
      });

      // Autocompletar cliente si el comprobante es factura
      if (comprobante === 'factura') {
        const dniInput = document.getElementById('cliDni');
        dniInput.addEventListener('input', async (e) => {
          const dni = e.target.value.trim();
          if (dni.length >= 6) {
            try {
              const r = await fetch(`/motoshoppy/ventas/api_buscar_cliente.php?dni=${dni}`);
              const d = await r.json();
              if (d.ok && d.cliente) {
                document.getElementById('cliNombre').value = d.cliente.nombre;
                document.getElementById('cliApellido').value = d.cliente.apellido;
                document.getElementById('cliCelular').value = d.cliente.celular;
              }
            } catch (err) {
              console.warn('No se pudo autocompletar cliente', err);
            }
          }
        });
      }
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

  // --- Datos listos para enviar ---
  const payload = {
    tipo_comprobante: comprobante,
    metodo_pago: confirmar.metodo_desc,
    productos: [productoConPrecio],
    total: productoConPrecio.precio_expuesto,
    cliente: confirmar.cliente
  };

  // --- Envío al backend ---
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

  // 🔄 Recargar tabla de productos
  if (tabla) tabla.ajax.reload(null, false);

// 🧾 Obtener DNI desde el payload, no desde el DOM
const dni = payload.cliente?.dni || '';

// 🧾 Abrir PDF según tipo de comprobante
if (data.tipo_comprobante === 'ticket') {
  window.open(`/motoshoppy/ventas/generar_ticket.php?id=${data.venta_id}&dni=${encodeURIComponent(dni)}`, '_blank');
} else if (data.tipo_comprobante === 'factura') {
  window.open(`/motoshoppy/ventas/generar_factura.php?id=${data.venta_id}`, '_blank');
} else {
  console.warn('Tipo de comprobante no reconocido:', data.tipo_comprobante);
}


} else {
  Swal.fire({
    icon: 'error',
    title: 'Error',
    text: data.msg || 'No se pudo registrar la venta.'
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


</script>






<?php include '../dashboard/footer.php'; ?>
