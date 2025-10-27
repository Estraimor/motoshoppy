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
        </div>
      </div>
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

function paramsActuales() {
  return {
    q: document.getElementById('buscarRapido').value.trim() || document.getElementById('filtroBusqueda').value.trim(),
    marca: document.getElementById('filtroMarca').value,
    categoria: document.getElementById('filtroCategoria').value,
    pmin: document.getElementById('precioMin').value,
    pmax: document.getElementById('precioMax').value,
    ordenar: document.getElementById('ordenarPor').value
  };
}

document.addEventListener('DOMContentLoaded', () => {
  // hotkey Ctrl+K para foco de búsqueda rápida
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      document.getElementById('buscarRapido').focus();
      document.getElementById('buscarRapido').select();
    }
  });

  // Inicializar DataTable
  tabla = new DataTable('#tablaVentas', {
    ajax: (d, cb) => {
      const p = paramsActuales();
      const qs = new URLSearchParams(p).toString();
      const url = `/motoshoppy/ventas/api_buscar_productos.php?${qs}`;

      console.log('📡 Llamando API:', url);

      fetch(url)
        .then(r => {
          if (!r.ok) throw new Error(`Error HTTP ${r.status}`);
          return r.json();
        })
        .then(rows => {
          console.log('✅ Productos recibidos:', rows);
          cb({ data: rows });
        })
        .catch(err => {
          console.error('❌ Error al cargar productos:', err);
          Swal.fire({
            icon: 'error',
            title: 'Error al cargar productos',
            text: err.message || 'No se pudo obtener la lista de productos.'
          });
          cb({ data: [] });
        });
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
        render: v => `<span class="text-success fw-semibold">$ ${money(v)}</span>`,
        className: 'text-success fw-semibold'
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
      // Si no hay stock → desactivar fila visualmente
      if (data.stock_estado === 'sin_stock') {
        row.style.opacity = '0.6';
        row.style.pointerEvents = 'none';
      } else {
        // click fila -> vista previa
        row.addEventListener('click', (e) => {
          // evitar conflicto si clic en controles
          if (e.target.closest('.input-group')) return;
          mostrarDetalle(data);
        });
      }

      // controles de cantidad/añadir
      const menos = row.querySelector('.btnMenos');
      const mas = row.querySelector('.btnMas');
      const agregar = row.querySelector('.btnAgregar');

      if (menos && mas && agregar && data.stock_estado !== 'sin_stock') {
        menos.addEventListener('click', (e) => {
          e.stopPropagation();
          const input = row.querySelector('.qtyInput');
          input.value = Math.max(1, (parseInt(input.value || 1) - 1));
        });
        mas.addEventListener('click', (e) => {
          e.stopPropagation();
          const input = row.querySelector('.qtyInput');
          input.value = (parseInt(input.value || 1) + 1);
        });
        agregar.addEventListener('click', (e) => {
          e.stopPropagation();
          const qty = parseInt(row.querySelector('.qtyInput').value || 1);
          agregarAlCarrito(data, qty);
        });
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

  // Búsqueda rápida debounced
  document.getElementById('buscarRapido').addEventListener('input', debounce(() => tabla.ajax.reload(), 250));

  // Filtros
  document.getElementById('btnAplicarFiltros').addEventListener('click', () => { tabla.ajax.reload(); });
  document.getElementById('btnLimpiarFiltros').addEventListener('click', () => {
    ['filtroBusqueda', 'filtroMarca', 'filtroCategoria', 'precioMin', 'precioMax', 'ordenarPor'].forEach(id => {
      const el = document.getElementById(id);
      if (el.tagName === 'SELECT') el.selectedIndex = 0; else el.value = '';
    });
    tabla.ajax.reload();
  });
});

/* ==== Vista previa y agregado rápido ==== */
function mostrarDetalle(p) {
  productoSeleccionado = p;
  document.getElementById('detalleVacio').classList.add('d-none');
  document.getElementById('detalleContenido').classList.remove('d-none');

  // === Imagen del producto ===
  const img = document.getElementById('detImagen');
  img.src = p.imagen
    ? `/motoshoppy/${p.imagen.replace(/\\/g, '/')}`
    : '/motoshoppy/imagenes/noimg.png';
  img.onclick = () => abrirZoom(img.src);

  // === Datos básicos ===
  document.getElementById('detNombre').textContent = p.nombre;
  document.getElementById('detCodigo').textContent = p.codigo ? `Código: ${p.codigo}` : '';
  document.getElementById('detMarca').textContent = p.nombre_marca ? `Marca: ${p.nombre_marca}` : '';

  // === Cálculo de precios de lista ===
  const base = Number(p.precio_expuesto || 0);
  const lista1 = base * 1.05;
  const lista2 = base * 1.07;
  const lista3 = base * 1.10;

  // === Select de precio de lista ===
  const precioHtml = `
    <div class="mb-2">
      <div class="d-flex justify-content-between align-items-center">
        <span class="fw-semibold text-success fs-5">Precio base:</span>
        <span class="text-success fw-bold fs-5">$ ${money(base)}</span>
      </div>
      <div class="mt-2">
        <label for="selectPrecioLista" class="form-label text-warning fw-semibold mb-1">Precio de lista:</label>
        <select id="selectPrecioLista" class="form-select form-select-sm w-auto d-inline-block">
          <option value="${base}">Base</option>
          <option value="${lista1}">Lista 1 (+5%) - $ ${money(lista1)}</option>
          <option value="${lista2}">Lista 2 (+7%) - $ ${money(lista2)}</option>
          <option value="${lista3}">Lista 3 (+10%) - $ ${money(lista3)}</option>
        </select>
      </div>
    </div>
  `;
  document.getElementById('detPrecio').innerHTML = precioHtml;

  // === Descripción (puede venir como JSON o texto plano) ===
  let descHtml = '';
  if (p.descripcion) {
    try {
      const json = JSON.parse(p.descripcion);
      descHtml = Object.entries(json).map(([k, v]) =>
        `<div><span class='text-warning'>${k}:</span> ${Array.isArray(v) ? v.join('/') : v}</div>`
      ).join('');
    } catch {
      descHtml = p.descripcion;
    }
  }

  // === Atributos de cubierta (si aplica) ===
  let attrHtml = '';
  if (p.nombre_categoria && p.nombre_categoria.toLowerCase().includes('cubierta')) {
    attrHtml = `
      <hr class="border-secondary">
      <h6 class="text-warning mb-2">Atributos de cubierta</h6>
      <div><b>Aro:</b> ${p.aro ?? '-'}</div>
      <div><b>Ancho:</b> ${p.ancho ?? '-'}</div>
      <div><b>Perfil:</b> ${p.perfil_cubierta ?? '-'}</div>
      <div><b>Tipo:</b> ${p.tipo ?? '-'}</div>
      <div><b>Varias aplicaciones:</b> ${p.varias_aplicaciones ?? '-'}</div>
    `;
  }

  // === Descripción + atributos + stock ===
  document.getElementById('detDesc').innerHTML = `
    ${descHtml || '<em class="text-muted">Sin descripción</em>'}
    ${attrHtml}
    <hr class="border-secondary">
    <div>Stock actual: 
      <span class="fw-bold text-${
        p.stock_estado === 'ok'
          ? 'success'
          : p.stock_estado === 'bajo_stock'
          ? 'warning'
          : 'danger'
      }">${p.stock_actual}</span>
      / Mínimo ${p.stock_minimo || 0}
    </div>`;

  // === Control de cantidad ===
  const inputCantidad = document.getElementById('detCantidad');
  inputCantidad.value = 1;

  document.getElementById('menosCant').onclick = () => {
    inputCantidad.value = Math.max(1, parseInt(inputCantidad.value || 1) - 1);
  };
  document.getElementById('masCant').onclick = () => {
    inputCantidad.value = parseInt(inputCantidad.value || 1) + 1;
  };

  // === Botón de agregar ===
  const btnAgregar = document.getElementById('btnAgregar');
  btnAgregar.disabled = p.stock_estado === 'sin_stock';
  btnAgregar.onclick = () => {
    const qty = parseInt(inputCantidad.value || 1);
    const precioSeleccionado = parseFloat(document.getElementById('selectPrecioLista').value);
    const productoConPrecio = { ...p, precio_expuesto: precioSeleccionado };
    agregarAlCarrito(productoConPrecio, qty);
  };

  // === Enter para agregar ===
  inputCantidad.onkeydown = (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      if (!btnAgregar.disabled) btnAgregar.click();
    }
  };
}



/* ==== Zoom imagen ==== */
function abrirZoom(src) {
  const zoom = document.createElement('div');
  zoom.className = 'zoomOverlay';
  zoom.innerHTML = `<div class="zoomInner"><img src="${src}" class="zoomImg"></div>`;
  document.body.appendChild(zoom);
  zoom.addEventListener('click', () => zoom.remove());
}

/* ==== Carrito (localStorage) ==== */
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
    cantidad: cantidad
  });
  localStorage.setItem(key, JSON.stringify(carrito));

  // feedback rápido
  if (window.Swal) {
    Swal.fire({
      toast: true, position: 'top-end', timer: 1200, showConfirmButton: false,
      icon: 'success', title: `Agregado: ${prod.nombre}`
    });
  }

  // actualizar contadores (si existen)
  ['cartCountTop', 'cartCountSide', 'cartCount'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      const total = carrito.reduce((a, b) => a + (b.cantidad || 1), 0);
      el.textContent = total;
    }
  });
}

// Enter en buscar rápido -> si hay fila seleccionada en detalle, agregar
document.addEventListener('keydown', (e) => {
  if (e.key === 'Enter' && document.activeElement === document.getElementById('buscarRapido') && productoSeleccionado) {
    e.preventDefault();
    document.getElementById('btnAgregar').click();
  }
});
</script>




<?php include '../dashboard/footer.php'; ?>
