<?php
include '../dashboard/nav.php';
requerirRol('Administrador', 'Ventas');
require_once '../conexion/conexion.php';
?>
<link rel="stylesheet" href="ventas.css">

<!-- HEADER -->
<div class="content-header d-flex justify-content-between align-items-center">
  <h2><i class="fa-solid fa-bolt me-2" style="color:#f59e0b"></i>Punto de Venta</h2>
  <div class="d-flex align-items-center gap-2">
    <div class="pv-leyenda">
      <span><b style="color:#34d399">G</b> = Depósito</span>
      <span><b style="color:#60a5fa">E</b> = Exhibido</span>
    </div>
    <input id="buscarRapido" class="form-control form-control-sm"
           placeholder="🔍 Buscar... (Ctrl+K)">
    <button class="btn btn-outline-warning btn-sm" data-bs-toggle="offcanvas" data-bs-target="#panelSettings">
      <i class="fa-solid fa-sliders"></i> Filtros
    </button>
  </div>
</div>

<div class="content-body">
<div class="pv-wrap">

  <!-- ===== TABLA ===== -->
  <div class="pv-tabla">
    <div class="modulo">
      <table id="tablaVentas" class="table table-dark table-hover table-sm w-100 align-middle">
        <thead>
          <tr>
            <th>Categoría</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Modelo</th>
            <th>Marca</th>
            <th>Precio</th>
            <th>Stock <span class="text-secondary">(G/E)</span></th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <!-- ===== PANEL DETALLE ===== -->
  <div class="pv-detalle">
    <div id="panelDetalle" class="modulo">

      <div id="detalleVacio">
        <i class="fa-solid fa-box-open"></i>
        <p>Seleccioná un producto para ver sus detalles y agregarlo al carrito.</p>
      </div>

      <div id="detalleContenido" class="d-none">
        <div class="text-center">
          <img id="detImagen" src="" alt="imagen">
        </div>
        <h5 id="detNombre"></h5>
        <div class="d-flex justify-content-between mb-2">
          <span id="detCodigo"></span>
          <span id="detMarca"></span>
        </div>
        <div id="detDesc"></div>
        <div id="detPrecio"></div>
        <hr class="border-secondary my-2">
        <div class="d-flex align-items-center justify-content-between gap-2">
          <div class="input-group" style="width:130px">
            <button class="btn btn-outline-warning btn-sm" id="menosCant">−</button>
            <input type="number" min="1" value="1" id="detCantidad" class="text-center">
            <button class="btn btn-outline-warning btn-sm" id="masCant">+</button>
          </div>
        </div>
        <button id="btnAgregar" class="btn btn-success w-100 mt-2">
          <i class="fa-solid fa-cart-plus me-1"></i> Agregar al carrito
        </button>
        <button id="btnComprarAhora" class="btn btn-primary w-100 mt-2">
          <i class="fa-solid fa-bolt me-1"></i> Comprar ahora
        </button>
      </div>

    </div>
  </div>

</div><!-- /pv-wrap -->
</div><!-- /content-body -->


<!-- ===== OFFCANVAS FILTROS ===== -->
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

    <!-- Categoría -->
<div class="mb-3">
  <label class="form-label text-warning">
    <i class="fa-solid fa-layer-group"></i> Categoría
  </label>

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

<!-- Marca -->
<div class="mb-3">
  <label class="form-label text-warning">
    <i class="fa-solid fa-tags"></i> Marca
  </label>

  <select id="filtroMarca" class="form-select bg-dark text-light border-secondary">
    <option value="">Todas</option>
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
let METADATA = {
  comprobantes: [],
  metodos_pago: [],
  monedas: []
};


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

  const filtroCategoria = document.getElementById("filtroCategoria");
const filtroMarca = document.getElementById("filtroMarca");

filtroCategoria.addEventListener("change", async () => {

  const categoria = filtroCategoria.value;

  filtroMarca.innerHTML = `<option value="">Todas</option>`;

  if (!categoria) return;

  const res = await fetch(`/motoshoppy/ventas/get_marcas_por_categoria.php?categoria=${categoria}`);
  const marcas = await res.json();

  marcas.forEach(m => {
    const opt = document.createElement("option");
    opt.value = m.idmarcas;
    opt.textContent = m.nombre_marca;
    filtroMarca.appendChild(opt);
  });

});

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

  scrollX: false,
  autoWidth: true,
  responsive: false,

  columnDefs: [
    { width: "14%",  targets: 0 },  // categoría
    { width: "9%",   targets: 1 },  // código
    { width: "22%",  targets: 2 },  // nombre
    { width: "10%",  targets: 3 },  // modelo
    { width: "10%",  targets: 4 },  // marca
    { width: "10%",  targets: 5 },  // precio
    { width: "8%",   targets: 6 },  // stock
    { width: "17%",  targets: 7 },  // acciones
  ],

  columns: [
    { data: 'nombre_categoria' },
    { data: 'codigo' },
    { data: 'nombre' },
    { data: 'modelo' },
    { data: 'nombre_marca' },
    {
      data: 'precio_expuesto',
      render: v => `<span class="text-success fw-semibold">$ ${money(v)}</span>`
    },
    {
      data: null,
      render: (_, __, row) => {
        const g = row.stock_general;
        const e = row.stock_exhibido;

        if (row.stock_estado === 'sin_stock') {
          return `<span class="text-danger fw-bold">Sin stock</span>`;
        }

        let colorG = 'text-success';
        let colorE = 'text-info';

        if (row.stock_estado === 'bajo_stock') {
          colorG = 'text-warning';
        }

        return `
          <span class="${colorG} fw-bold">${g}</span>
          <span class="text-secondary"> / </span>
          <span class="${colorE} fw-bold">${e}</span>
        `;
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


// 🔥 REAJUSTE AUTOMÁTICO (ZOOM / RESIZE)
function ajustarTabla() {
  setTimeout(() => {
    tabla.columns.adjust().draw(false);
  }, 200);
}

// cuando cambia tamaño (zoom incluido)
window.addEventListener('resize', ajustarTabla);

// cuando volvés a la pestaña
window.addEventListener('focus', ajustarTabla);

// opcional: cada cierto tiempo (ultra estable)
setInterval(ajustarTabla, 3000);
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

window.COT = null;

async function inicializarCotizacion() {
  try {
    const res = await fetch('/motoshoppy/api/get_cotizacion.php');
    const data = await res.json();

    if (!data || data.error) {
      console.warn("⚠ No se pudo obtener la cotización desde la BD.");
      return;
    }

    // Guardamos la última cotización en memoria global
    window.COT = data;
    console.log("💱 Cotización (BD):", window.COT);

  } catch (e) {
    console.error("Error al cargar cotización:", e);
  }
}

inicializarCotizacion();


async function cargarMetadataVentas() {
    try {
        const r = await fetch('/motoshoppy/api/get_metadata_ventas.php');
        const d = await r.json();

        METADATA.comprobantes = d.comprobantes;
        METADATA.metodos_pago = d.metodos_pago;
        METADATA.monedas = d.monedas;

        console.log("Metadata cargada:", METADATA);

    } catch (err) {
        console.error("Error cargando metadata:", err);
    }
}

cargarMetadataVentas();


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

  // === Cotizaciones (desde BD) ===
  const usd_pyg = Number(window.COT?.usd_pyg || 6000);
  const ars_pyg = Number(window.COT?.ars_pyg || 4.5);
  const usd_ars = Number(window.COT?.usd_ars || 1500);


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
            <span class="fw-semibold fs-5 moneda-py-label">
              <i class="fa-solid fa-sack-dollar me-1"></i>Precio base:
            </span>
            <span id="precioBase" class="fw-bold fs-5 moneda-pyg">
              ₲ ${money(basePYG)}
            </span>
          </div>


          <!-- Conversión base -->
          <div class="bg-dark bg-opacity-25 p-2 rounded small text-light mb-3">
            <div class="d-flex justify-content-between align-items-center moneda-usd">

              <div><i class="fa-solid fa-dollar-sign text-warning me-1"></i><span class="fw-semibold">USD:</span></div>
              <div id="usdBase" class="fw-semibold">-</div>
            </div>
            <div class="d-flex justify-content-between align-items-center moneda-ars mt-1">

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


  // Armar atributos JSON en grid 2 columnas
  let attrsGrid = '';
  if (p.descripcion) {
    try {
      const json = JSON.parse(p.descripcion);
      const entries = Object.entries(json);
      if (entries.length) {
        attrsGrid = `
          <div class="pv-ficha">
            <div class="pv-ficha-titulo">Atributos del producto</div>
            <div class="pv-attrs">
              ${entries.map(([k, v]) => `
                <div class="pv-attr">
                  <span class="pv-attr-key">${k}</span>
                  <span class="pv-attr-val" title="${v}">${v ?? '—'}</span>
                </div>
              `).join('')}
            </div>
          </div>`;
      }
    } catch {}
  }

  // Ficha técnica (modelo, categoría, peso, ubicación)
  let fichaItems = [];
  if (p.modelo)           fichaItems.push(['Modelo',    p.modelo]);
  if (p.nombre_categoria) fichaItems.push(['Categoría', p.nombre_categoria]);
  if (p.peso_ml)          fichaItems.push(['Vol. (ml)', p.peso_ml]);
  if (p.peso_g)           fichaItems.push(['Peso (g)',  p.peso_g]);
  if (p.aro)              fichaItems.push(['Aro',       p.aro]);
  if (p.ancho)            fichaItems.push(['Ancho',     p.ancho]);
  if (p.perfil_cubierta)  fichaItems.push(['Perfil',    p.perfil_cubierta]);
  if (p.tipo)             fichaItems.push(['Tipo',      p.tipo]);

  let fichaGrid = '';
  if (fichaItems.length) {
    fichaGrid = `
      <div class="pv-ficha">
        <div class="pv-ficha-titulo">Ficha técnica</div>
        <div class="pv-attrs">
          ${fichaItems.map(([k, v]) => `
            <div class="pv-attr">
              <span class="pv-attr-key">${k}</span>
              <span class="pv-attr-val" title="${v}">${v}</span>
            </div>
          `).join('')}
        </div>
      </div>`;
  }

  document.getElementById('detDesc').innerHTML = attrsGrid + fichaGrid;
}





/* ==== Zoom ==== */
function abrirZoom(src) {
  const overlay = document.createElement('div');
  overlay.className = 'zoomOverlayCustom';
  overlay.innerHTML = `<div class="zoomInnerCustom"><img src="${src}" class="zoomImgCustom"></div>`;
  document.body.appendChild(overlay);
  overlay.addEventListener('click', () => overlay.remove());
}

/* ============================================================
   ==== Carrito ====
============================================================ */
function agregarAlCarrito(prod, cantidad = 1) {

  // 🔒 Normalización de precios
  const precioBase = Number(prod.precio_lista ?? prod.precio_expuesto ?? 0);
  const precioUnit = Number(prod.precio_expuesto ?? 0);

  // 🧮 Cálculo de descuento real
  const porcentajeDesc = precioBase > 0
    ? ((precioBase - precioUnit) / precioBase) * 100
    : 0;

  const key = 'carrito';
  const carrito = JSON.parse(localStorage.getItem(key) || '[]');

  // 🔎 Buscar si ya existe en carrito
  const idx = carrito.findIndex(it => it.idProducto == prod.idProducto);

  if (idx >= 0) {
    // ➕ Sumar cantidad si ya existe
    carrito[idx].cantidad += Number(cantidad);
  } else {
    // ➕ Agregar nuevo producto
    carrito.push({
      idProducto: prod.idProducto,
      nombre: prod.nombre,

      // 💰 PRECIOS DEFINITIVOS (contrato del sistema)
      precio_base: Number(precioBase.toFixed(2)),
      precio_unitario: Number(precioUnit.toFixed(2)),
      porcentaje_descuento: Number(porcentajeDesc.toFixed(2)),

      cantidad: Number(cantidad),

      // 🖼️ Datos UI / informativos
      imagen: prod.imagen ?? null,
      codigo: prod.codigo ?? null,
      nombre_marca: prod.nombre_marca ?? null,
      modelo: prod.modelo ?? null,
      nombre_categoria: prod.nombre_categoria ?? null,
      stock_actual: prod.stock_actual ?? null,
      stock_estado: prod.stock_estado ?? null
    });
  }

  // 💾 Persistir carrito
  localStorage.setItem(key, JSON.stringify(carrito));

  // 🔔 Toast visual
  if (window.Swal) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      timer: 1200,
      showConfirmButton: false,
      icon: 'success',
      title: `Agregado: ${prod.nombre}`
    });
  }

  // 🔢 Actualizar contadores
  ['cartCountTop', 'cartCountSide', 'cartCount'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.textContent = carrito.reduce(
        (a, b) => a + (Number(b.cantidad) || 1),
        0
      );
    }
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

  const precioSeleccionado = parseFloat(
    document.querySelector('#selectPrecioLista')?.value || 
    p.precio_expuesto || 
    0
  );

  const qty = parseInt(document.getElementById('detCantidad')?.value || 1);

 const precioUnit = parseFloat(
  document.querySelector('#selectPrecioLista')?.value || 0
);


// total por ítem
const totalPYG = precioUnit * qty;

const precioBase = Number(p.precio_expuesto || 0);
const precioFinal = Number(precioUnit);

const porcentajeDesc = precioBase > 0
  ? ((precioBase - precioFinal) / precioBase) * 100
  : 0;

const productoConPrecio = { 
  ...p,
  precio_base: precioBase,
  precio_unitario: precioFinal,
  porcentaje_descuento: Number(porcentajeDesc.toFixed(2)),
  cantidad: qty,
  total_item: totalPYG
};

  // 🔥 ESTA ES LA LÍNEA QUE FALTABA
  await seleccionarComprobanteYConfirmarVenta(productoConPrecio);

});



// ======================================
// SELECCIONAR COMPROBANTE Y CONFIRMAR
// ======================================
async function seleccionarComprobanteYConfirmarVenta(productoConPrecio) {

    // Si metadata aún no cargó
    if (!METADATA || METADATA.comprobantes.length === 0) {
        await cargarMetadataVentas();
    }

    // Construir radios de comprobantes
    let opts = {};
    METADATA.comprobantes.forEach(c => {
        opts[c.id] = c.nombre;   // ✔ USAMOS ID, NO TEXTO
    });

    // ===============================
    // 1) SELECCIÓN DE COMPROBANTE
    // ===============================
    const { value: comprobanteID } = await Swal.fire({
        title: 'Tipo de comprobante',
        input: 'radio',
        inputOptions: opts,
        inputValue: Object.keys(opts)[0],
        confirmButtonText: 'Continuar',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        inputValidator: v => !v && 'Seleccioná una opción'
    });

    if (!comprobanteID) return;

    // Obtenemos el nombre real
    const comprobanteNombre = METADATA.comprobantes.find(c => c.id == comprobanteID)?.nombre.toLowerCase();


    // 2) MÉTODOS DE PAGO (con required lógico)
let optionsMetodo = `
    <option value="" disabled selected>Selecciona un método de pago</option>
`;
METADATA.metodos_pago.forEach(m => {
    optionsMetodo += `<option value="${m.id}">${m.nombre}</option>`;
});

   // ===============================
// 3) MONEDAS
// ===============================
let optionsMoneda = `
    <option value="" disabled selected>Selecciona la moneda</option>
`;

METADATA.monedas.forEach(m => {
    optionsMoneda += `
        <option value="${m.id}">
            ${m.codigo} – ${m.nombre}
        </option>
    `;
});



    // ===============================
    // 4) ARMAR HTML DEL MODAL
    // ===============================
    htmlPago = `
  <div class="text-start">

    <label class="form-label fw-bold mt-2">Método de pago</label>
    <select id="metodoPago" class="form-select">
      ${optionsMetodo}
    </select>

    <div id="otroMetodo" class="mt-3 d-none">
      <input id="otroTexto" class="form-control" placeholder="Describí el método de pago...">
    </div>

    <hr class="my-3">

    <label class="form-label fw-bold">Moneda</label>
    <select id="monedaPago" class="form-select d-none">
      ${optionsMoneda}
    </select>

    ${
      comprobanteNombre === "factura"
      ? `
        <hr class="my-3">
        <label class="form-label fw-bold">Datos del cliente (Factura)</label>

        <input id="cliNombreFactura" class="form-control mb-2" placeholder="Nombre">
        <input id="cliApellidoFactura" class="form-control mb-2" placeholder="Apellido">
        <input id="cliDniFactura" class="form-control mb-2" placeholder="CI/RUC">
        <input id="cliCelularFactura" class="form-control mb-2" placeholder="Celular">

        <!-- 🔥 NUEVO CAMPO -->
        <input id="cliDireccionFactura" class="form-control mb-2" placeholder="Dirección">
      `
      : `
        <hr class="my-3">
        <label class="form-label fw-bold">CI/RUC del cliente (Ticket)</label>
        <input id="cliDniTicket" class="form-control mb-2" placeholder="CI/RUC">
      `
    }
  </div>
`;
    // ===============================
    // 5) CONFIRMAR VENTA
    // ===============================
    const { value: confirmar } = await Swal.fire({
        title: 'Confirmar venta',
        html: `
            <div class="text-start">
                <p><strong>Producto:</strong> ${productoConPrecio.nombre}</p>
                <p><strong>Total:</strong> ₲ ${money(productoConPrecio.total_item)}</p>
                <p><strong>Comprobante:</strong> ${comprobanteNombre.toUpperCase()}</p>
                ${htmlPago}
            </div>
        `,
        width: 600,
        confirmButtonText: 'Finalizar venta',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',

        didOpen: () => {
            const selMetodo = document.getElementById('metodoPago');

            selMetodo.addEventListener('change', () => {

    // Mostrar caja de texto si "otro"
    document.getElementById('otroMetodo')
        .classList.toggle('d-none', selMetodo.value !== 'otro');

    // Mostrar moneda SOLO si método = EFECTIVO
    const selMoneda = document.getElementById('monedaPago');

    // Buscamos el nombre del método seleccionado
    const metodoSeleccionado = METADATA.metodos_pago.find(m => m.id == selMetodo.value);

    if (metodoSeleccionado && metodoSeleccionado.nombre.toLowerCase() === "efectivo") {
        selMoneda.classList.remove('d-none');
    } else {
        selMoneda.classList.add('d-none');
        selMoneda.value = ""; // limpiar para evitar errores
    }
});


            // Autocompletar cliente para factura
            if (comprobanteNombre === "factura") {
                const dniInput = document.getElementById('cliDniFactura');

                dniInput.addEventListener('input', async (e) => {
                    const dni = e.target.value.trim();
                    if (dni.length >= 6) {
                        const r = await fetch(`/motoshoppy/ventas/api_buscar_cliente.php?dni=${dni}`);
                        const d = await r.json();

                        if (d.ok && d.cliente) {
                            document.getElementById('cliNombreFactura').value = d.cliente.nombre;
                            document.getElementById('cliApellidoFactura').value = d.cliente.apellido;
                            document.getElementById('cliCelularFactura').value = d.cliente.celular;
                        }
                    }
                });
            }
        },

        preConfirm: () => {
let direccion = '';    const metodo = document.getElementById('metodoPago').value;
    const metodo_desc = metodo === '4'
        ? document.getElementById('otroTexto').value.trim()
        : metodo;

    const moneda = document.getElementById('monedaPago').value;

    // ============================================
    // VALIDACIONES REQUIRED
    // ============================================

    // 1) Método de pago obligatorio
    if (!metodo) {
        Swal.showValidationMessage("Debés seleccionar un método de pago");
        return false;
    }

    if (comprobanteNombre === "factura") {
    direccion = document.getElementById('cliDireccionFactura')?.value.trim();

    if (!direccion) {
        Swal.showValidationMessage("Ingresá la dirección del cliente");
        return false;
    }
}
    // Obtener método seleccionado desde METADATA
    const metodoSeleccionado = METADATA.metodos_pago.find(m => m.id == metodo);

    // 2) Si es EFECTIVO, moneda obligatoria
    if (metodoSeleccionado && metodoSeleccionado.nombre.toLowerCase() === "efectivo") {
        if (!moneda) {
            Swal.showValidationMessage("Debés seleccionar la moneda del pago en efectivo");
            return false;
        }
    }

    // ============================================
    // CLIENTE
    // ============================================
    let cliente = null;

    if (comprobanteNombre === "factura") {

        const nombre = document.getElementById('cliNombreFactura').value.trim();
        const apellido = document.getElementById('cliApellidoFactura').value.trim();
        const dni = document.getElementById('cliDniFactura').value.trim();
        const celular = document.getElementById('cliCelularFactura').value.trim();

        // Validaciones de factura
        if (!dni) {
            Swal.showValidationMessage("Ingresá el CI/RUC del cliente");
            return false;
        }
        if (!nombre || !apellido) {
            Swal.showValidationMessage("Ingresá nombre y apellido del cliente para la factura");
            return false;
        }

        cliente = { nombre, apellido, dni, celular, direccion };

    } else {
        const dni = document.getElementById('cliDniTicket').value.trim();
        if (!dni) {
            Swal.showValidationMessage("Debés ingresar el CI/RUC del cliente");
            return false;
        }
        cliente = { dni };
    }

    return { metodo, metodo_desc, moneda, cliente };
}
});

if (!confirmar) return;


    // ===============================
    // 6) PAYLOAD
    // ===============================
    const payload = {
        tipo_comprobante: comprobanteID,
        metodo_pago: confirmar.metodo_desc,
        moneda: confirmar.moneda,
        productos: [productoConPrecio],
        total: productoConPrecio.total_item,
        cliente: confirmar.cliente
    };

    // ===============================
    // 7) ENVIAR AL BACKEND
    // ===============================
    try {
        const res = await fetch('/motoshoppy/ventas/api_comprar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (!data.ok) {
            return Swal.fire('Error', data.msg || 'No se pudo registrar la venta.', 'error');
        }

        Swal.fire({
            icon: 'success',
            title: 'Venta completada',
            timer: 1500,
            showConfirmButton: false
        });

        const direccion = confirmar.cliente?.direccion || '';

if (comprobanteNombre === "ticket") {
    window.open(
        `/motoshoppy/ventas/generar_ticket.php?id=${data.venta_id}`,
        '_blank'
    );
} else {
    window.open(
        `/motoshoppy/ventas/generar_factura.php?id=${data.venta_id}&dir=${encodeURIComponent(direccion)}`,
        '_blank'
    );
}

    } catch (err) {
        console.error(err);
        Swal.fire('Error de conexión', 'No se pudo contactar con el servidor.', 'error');
    }
}



</script>






<?php include '../dashboard/footer.php'; ?>
