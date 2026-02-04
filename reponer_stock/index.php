<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';
?>

<style>
    select { max-height: 200px; overflow-y: auto; }
    .prov-title { background: #f1f1f1; font-weight: bold; }

    /* Tabla */
    .table th, .table td { text-align: center; vertical-align: middle; }

    /* Botones */
    .btn-custom { width: 100%; padding: 12px; }

    /* Card */
    .card-header { background-color: #343a40; color: white; }

    /* === TABLA DETALLE PEDIDO - RESPONSIVE FIX === */
#modalDetalle table td {
    vertical-align: middle !important;
    padding: 6px 8px;
}

/* centrar contenido del td */
#modalDetalle td > input,
#modalDetalle td > .btn,
#modalDetalle td > div {
    display: block;
    margin: auto;
}

/* inputs sin altura fija */
#modalDetalle input.form-control-sm {
    height: auto;              /* 🔑 clave */
    min-height: 30px;          /* base Bootstrap */
    padding: 4px 8px;
    line-height: 1.3;
    box-sizing: border-box;
}



    /* ✔ check animado */
.check-ok {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%) scale(0);
    color: #198754;
    font-size: 1.3rem;
    animation: checkPop 0.5s ease forwards;
}

@keyframes checkPop {
    0%   { transform: translateY(-50%) scale(0); opacity: 0; }
    60%  { transform: translateY(-50%) scale(1.3); opacity: 1; }
    100% { transform: translateY(-50%) scale(1); opacity: 1; }
}


/* ============================
   MODAL IMPACTO – LEGIBILIDAD
============================ */

#modalImpacto .modal-content {
    background: linear-gradient(180deg, #0f0f0f 0%, #1b1b1b 100%);
    color: #f1f1f1;
}

/* encabezado */
#modalImpacto .modal-title {
    font-weight: 600;
    color: #ffc107;
}

/* badges */
#modalImpacto .badge {
    font-size: 0.85rem;
    padding: 6px 10px;
}

/* productos */
#modalImpacto #productosPedido > div {
    background: rgba(255,255,255,0.04);
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 10px;
}

/* nombre producto */
#modalImpacto #productosPedido strong {
    color: #ffffff;
    font-size: 1rem;
}

/* marca */
#modalImpacto #productosPedido .text-muted {
    color: rgba(255,255,255,0.55) !important;
}

/* cantidad */
#modalImpacto #productosPedido .badge.bg-secondary {
    background: rgba(255,255,255,0.15);
    color: #fff;
}

/* inputs */
#modalImpacto input,
#modalImpacto textarea {
    background: rgba(30,30,30,0.95);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.15);
}

#modalImpacto input::placeholder {
    color: rgba(255,255,255,0.4);
}

#modalImpacto input:focus,
#modalImpacto textarea:focus {
    border-color: #ffc107;
    box-shadow: 0 0 8px rgba(255,193,7,0.35);
}

/* botón confirmar */
#modalImpacto .btn-success {
    background: linear-gradient(135deg, #198754, #20c997);
    border: none;
    font-weight: 600;
    padding: 10px 18px;
}

#modalImpacto .btn-success:hover {
    filter: brightness(1.1);
}


/* =====================================
   ZOOM IMAGEN (LIGHTBOX GLOBAL)
===================================== */

.zoom-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;

    opacity: 0;
    transition: opacity .25s ease;
}

.zoom-overlay.show {
    opacity: 1;
}

.zoom-overlay img {
    max-width: 90vw;
    max-height: 90vh;
    border-radius: 10px;
    box-shadow: 0 0 40px rgba(0,0,0,.8);

    transform: scale(.85);
    transition: transform .25s ease;
}

.zoom-overlay.show img {
    transform: scale(1);
}


/* =====================================
   MINIATURAS TABLAS (MUY IMPORTANTE)
   ← esto evita que Eren sea tamaño póster 😂
===================================== */

.zoomable-thumb,
.tabla-thumb {
    width: 50px !important;
    height: 50px !important;
    object-fit: contain !important;
    border-radius: 6px;
    cursor: pointer;
    display: block;
}


/* nunca permitir imágenes gigantes dentro de tablas */
#tablaPedido img,
#modalDetalle img {
    max-width: 50px;
    max-height: 50px;
}


/* =====================================
   TABLAS PROLIJAS
===================================== */

#tablaPedido td,
#modalDetalle td {
    vertical-align: middle !important;
}


/* pequeño hover moderno */
.zoomable-thumb:hover,
.tabla-thumb:hover {
    transform: scale(1.1);
    transition: .15s;
}

</style>

<div class="container mt-5">
    <h3 class="mb-4 text-center">🛒 Reposición de Stock</h3>

    <!-- Crear nuevo pedido -->
    <div class="card mb-4">
        <div class="card-header text-center">
            <strong>Crear nuevo pedido de reposición</strong>
        </div>

        <div class="card-body">
            <!-- Fila de filtros -->
            <div class="row mb-3">

                    <!-- PROVEEDOR -->
                    <div class="col-md-3">
                        <label class="form-label">Proveedor</label>
                        <select id="proveedor" class="form-select">
                            <option value="">Seleccionar...</option>
                            <?php
                            foreach ($conexion->query("SELECT * FROM proveedores ORDER BY empresa") as $p) {
                                echo "<option value='{$p['idproveedores']}'>{$p['empresa']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- CATEGORIA -->
                    <div class="col-md-3">
                        <label class="form-label">Categoría</label>
                        <select id="categoria" class="form-select">
                            <option value="">Seleccionar...</option>
                            <?php
                            foreach ($conexion->query("SELECT * FROM categoria ORDER BY nombre_categoria") as $c) {
                                echo "<option value='{$c['idCategoria']}'>{$c['nombre_categoria']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- ⭐ NUEVO: MARCA -->
                    <div class="col-md-3">
                        <label class="form-label">Marca</label>
                        <select id="marca" class="form-select" disabled>
                            <option value="">Seleccione categoría</option>
                        </select>
                    </div>

                    <!-- PRODUCTO -->
                    <div class="col-md-3">
                        <label class="form-label">Producto</label>
                        <select id="producto" class="form-select" disabled>
                            <option value="">Seleccione marca</option>
                        </select>
                    </div>

            </div>


            <!-- Cantidad -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Cantidad</label>
                    <input type="number" id="cantidad" class="form-control" min="1">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Código proveedor</label>
                    <input type="text" id="codigoProveedor" class="form-control"
                        placeholder="Ej: YMH-AX100">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-success btn-custom" id="btnAgregar">
                        ➕ Agregar
                    </button>
                </div>
            </div>

                    <div class="row mb-3">
    <div class="col-md-4">
        <img id="previewProducto"
            src=""
            class="img-fluid border rounded d-none zoomable"
            style="max-height:120px; object-fit:contain; cursor:pointer;">
    </div>
</div>


            <!-- Tabla items del pedido -->
            <div class="table-responsive">
                <table class="table table-bordered" id="tablaPedido">
                    <thead class="table-light">
                        <tr>
                            <th>Proveedor</th>
<th>Imagen</th>
<th>Producto</th>
<th>Marca</th>
<th>Código proveedor</th>
<th>Cantidad</th>
<th>Acción</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <button type="button" class="btn btn-primary w-100" id="btnGuardar">
                💾 Guardar Pedido
            </button>
        </div>
    </div>

    <!-- Pedidos existentes -->
    <div class="card mb-4">
        <div class="card-header text-center">
            <strong>Pedidos de reposición</strong>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaReposiciones" class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Proveedor</th>
                            <th>Fecha Pedido</th>
                            <th>Fecha Impacto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $pedidos = $conexion->query("
                            SELECT 
                                r.idreposicion,
                                r.estado,
                                r.fecha_pedido,
                                r.fecha_llegada,
                                p.empresa
                            FROM reposicion r
                            JOIN proveedores p 
                                ON p.idproveedores = r.proveedores_idproveedores
                            ORDER BY r.idreposicion DESC
                        ");

                        foreach ($pedidos as $r):
                        ?>
                            <tr>
                                <td><?= $r['idreposicion'] ?></td>
                                <td><?= htmlspecialchars($r['empresa']) ?></td>

                                <td><?= date('d/m/Y H:i', strtotime($r['fecha_pedido'])) ?></td>

                                <td>
                                    <?= $r['fecha_llegada']
                                        ? date('d/m/Y H:i', strtotime($r['fecha_llegada']))
                                        : '<span class="text-muted">—</span>' ?>
                                </td>

                                <td>
                                    <?php
$estado = $r['estado'];

$clase = match ($estado) {
    'pedido'    => 'warning',
    'impactado' => 'success',
    'cancelado' => 'danger',
    default     => 'secondary'
};
?>

<span class="badge bg-<?= $clase ?>">
    <?= strtoupper($estado) ?>
</span>

                                </td>

                                <td>
                                    <button type="button" class="btn btn-primary btn-sm"
                                        onclick="verDetalle(<?= (int)$r['idreposicion'] ?>)">
                                        Ver detalle
                                    </button>

                                    <?php if ($r['estado'] === 'pedido'): ?>
                                        <button type="button" class="btn btn-success btn-sm"
                                            onclick="abrirImpacto(<?= (int)$r['idreposicion'] ?>)">
                                            Impactar
                                        </button>

                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="cancelarPedido(<?= (int)$r['idreposicion'] ?>)">
                                            Cancelar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div><!-- /table-responsive -->
        </div><!-- /card-body -->
    </div><!-- /card -->
</div><!-- /container -->

<!-- Modal Ver Detalle Pedido -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="detallePedido">
                <!-- Se carga por JS -->
            </div>
        </div>
    </div>
</div>

<!-- Modal para Impactar pedido -->
<div class="modal fade" id="modalImpacto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formImpacto" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Impactar pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="idreposicion" id="idreposicion">

                    <!-- Productos del pedido -->
                    <div id="productosPedido" class="mb-3"></div>

                    <!-- ✅ COSTO TOTAL -->
                    <div class="mb-3">
                        <label class="form-label">Costo total del pedido</label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="costo_total"
                               id="costo_total"
                               class="form-control"
                               placeholder="Ej: 125000.00"
                               required>
                        <small class="text-muted">
                            Monto total según remito / factura.
                        </small>
                    </div>
                    
                    <!-- NÚMERO DE FACTURA -->
                    <div class="mb-3">
                        <label class="form-label">Número de factura</label>
                        <input type="text"
                            name="numero_factura"
                            class="form-control"
                            placeholder="Ej: A-0001-00001234"
                            required>
                        <small class="text-muted">
                            Número que figura en la factura del proveedor.
                        </small>
                    </div>

                    <label class="form-label">Remito / Factura</label>
                    <input type="file" name="remito" class="form-control" required>

                    <label class="form-label mt-2">Observación</label>
                    <textarea name="observacion" class="form-control"></textarea>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success" type="submit">
                        Confirmar impacto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
let pedidos = {};

/* =========================
   ELEMENTOS
========================= */
const proveedor = document.getElementById('proveedor');
const categoria = document.getElementById('categoria');
const producto  = document.getElementById('producto');
const cantidad  = document.getElementById('cantidad');
const tbody     = document.querySelector('#tablaPedido tbody');
const codigoProveedor = document.getElementById('codigoProveedor');
const marca     = document.getElementById('marca');
const preview   = document.getElementById('previewProducto');


categoria.addEventListener('change', () => {

    marca.innerHTML = '<option>Cargando...</option>';
    producto.innerHTML = '<option>Seleccione marca</option>';
    producto.disabled = true;

    fetch(`get_marcas.php?idCategoria=${categoria.value}`)
        .then(r => r.json())
        .then(data => {

            marca.innerHTML = '<option value="">Seleccionar...</option>';

            data.forEach(m => {
                marca.innerHTML += `
                    <option value="${m.idmarcas}">
                        ${m.nombre_marca}
                    </option>`;
            });

            marca.disabled = false;
        });
});
marca.addEventListener('change', () => {

    producto.innerHTML = '<option>Cargando...</option>';

    fetch(`api_get_productos.php?idMarca=${marca.value}`)
        .then(r => r.json())
        .then(data => {

            producto.innerHTML = '<option value="">Seleccionar...</option>';

            data.forEach(p => {
                producto.innerHTML += `
                    <option value="${p.idProducto}" data-img="${p.imagen}">
                        ${p.nombre}
                    </option>`;
            });

            producto.disabled = false;
        });
});

producto.addEventListener('change', () => {

    const img = producto.selectedOptions[0].dataset.img;

    if(img){
        preview.src = '/motoshoppy/' + img;
        preview.classList.remove('d-none');
    }
});

/* =========================
   AGREGAR PRODUCTO AL PEDIDO
========================= */
document.getElementById('btnAgregar').addEventListener('click', () => {
    if (!proveedor.value || !producto.value || cantidad.value <= 0) {
        alert('Datos incompletos');
        return;
    }

    if (!pedidos[proveedor.value]) {
        pedidos[proveedor.value] = {
            nombre: proveedor.options[proveedor.selectedIndex].text,
            items: []
        };
    }

    pedidos[proveedor.value].items.push({
    id: producto.value,
    producto: producto.options[producto.selectedIndex].text,
    marca: marca.options[marca.selectedIndex].text, // ⭐ NUEVO
    cantidad: parseInt(cantidad.value),
    codigo_proveedor: codigoProveedor.value.trim(),
    imagen: producto.selectedOptions[0].dataset.img
});




    render();
    cantidad.value = '';
});

/* =========================
   RENDER TABLA PEDIDO
========================= */
function render() {

    tbody.innerHTML = '';

    Object.keys(pedidos).forEach(p => {

        pedidos[p].items.forEach((i, idx) => {

            tbody.innerHTML += `
            <tr>

                <!-- PROVEEDOR -->
                <td>
                    ${pedidos[p].nombre}
                </td>

                <!-- IMAGEN -->
                <td class="text-center">
                    ${i.imagen
                        ? `<img src="/motoshoppy/${i.imagen}" class="tabla-thumb zoomable-thumb">`
                        : '-'}
                </td>

                <!-- PRODUCTO -->
                <td class="fw-semibold">
                    ${i.producto}
                </td>

                <!-- MARCA -->
                <td class="text-muted">
                    ${i.marca}
                </td>

                <!-- CODIGO -->
                <td>
                    ${i.codigo_proveedor || '-'}
                </td>

                <!-- CANTIDAD -->
                <td class="text-center">
                    ${i.cantidad}
                </td>

                <!-- ACCION -->
                <td class="text-center">
                    <button
                        class="btn btn-danger btn-sm"
                        onclick="quitar('${p}', ${idx})">
                        ❌
                    </button>
                </td>

            </tr>
            `;
        });

    });
}


/* =========================
   QUITAR ITEM
========================= */
function quitar(p, idx) {
    pedidos[p].items.splice(idx, 1);
    if (pedidos[p].items.length === 0) delete pedidos[p];
    render();
}

/* =========================
   GUARDAR PEDIDO
========================= */
document.getElementById('btnGuardar').addEventListener('click', () => {
    if (!Object.keys(pedidos).length) {
        alert('No hay productos en el pedido');
        return;
    }

    fetch('api_guardar_pedido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(pedidos)
    })
    .then(r => r.json())
    .then(r => {
        if (r.ok) {
            alert('Pedido guardado correctamente');
            pedidos = {};
            render();
            location.reload();
        } else {
            alert('Error al guardar pedido');
        }
    });
});

/* =========================
   ABRIR MODAL IMPACTO
========================= */
function abrirImpacto(id) {
    document.getElementById('idreposicion').value = id;
    document.getElementById('productosPedido').innerHTML = 'Cargando...';
    document.getElementById('costo_total').value = ''; // reset costo

    // Abrimos el modal
    const modal = new bootstrap.Modal(
        document.getElementById('modalImpacto')
    );
    modal.show();

    // Cargamos los productos del pedido
    fetch('./api_ver_detalle_pedido.php?id=' + encodeURIComponent(id))
        .then(r => r.json())
        .then(r => {
            if (!r.ok) {
                document.getElementById('productosPedido').innerHTML =
                    '<div class="text-danger">Error al cargar productos</div>';
                return;
            }

            const totalUnidades = r.productos.reduce(
                (acc, p) => acc + parseInt(p.cantidad || 0), 0
            );

            document.getElementById('productosPedido').innerHTML = `
                <div class="mb-3">
                    <span class="badge bg-primary">
                        Ítems: ${r.productos.length}
                    </span>
                    <span class="badge bg-secondary ms-2">
                        Unidades totales: ${totalUnidades}
                    </span>
                </div>

                ${r.productos.map(p => `
                    <div class="mb-2 border-bottom pb-2">
                        <div>
                            <strong>${p.producto}</strong>
                            <span class="text-muted ms-1">
                                (${p.marca ?? 'Sin marca'})
                            </span>
                        </div>

                        <div class="small text-muted">
                            Código proveedor: ${p.codigo_proveedor ?? '-'}
                        </div>

                        <div class="mt-1">
                            Cantidad:
                            <span class="badge bg-secondary">
                                ${p.cantidad}
                            </span>
                        </div>
                    </div>
                `).join('')}
            `;
        })
        .catch(() => {
            document.getElementById('productosPedido').innerHTML =
                '<div class="text-danger">Error de conexión</div>';
        });
}




/* =========================
   CONFIRMAR IMPACTO (SUBMIT)
========================= */
document.getElementById('formImpacto').addEventListener('submit', e => {
    e.preventDefault();

    const formData = new FormData(e.target);

    fetch('api_impactar_pedido.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(r => {
        if (r.ok) {
            alert('Pedido impactado correctamente');
            location.reload();
        } else {
            alert('Error al impactar pedido');
        }
    });
});

/* =========================
   CANCELAR PEDIDO
========================= */
function cancelarPedido(id) {
    if (!confirm('¿Cancelar pedido?')) return;

    fetch('api_cancelar_pedido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id)
    })
    .then(() => location.reload());
}

/* =========================
   VER DETALLE
========================= */
/* =========================
   VER DETALLE (IMAGEN + MARCA SEPARADAS)
========================= */
function verDetalle(id) {

    fetch('api_ver_detalle_pedido.php?id=' + encodeURIComponent(id))
        .then(r => r.json())
        .then(r => {

            if (!r.ok) {
                alert('Error al cargar el detalle');
                return;
            }

            /* =========================
               INFO GENERAL
            ========================= */
            let html = `
                <p><strong>Proveedor:</strong> ${r.proveedor}</p>
                <p><strong>Fecha pedido:</strong> ${r.fecha_pedido}</p>
                <p><strong>Fecha impacto:</strong> ${r.fecha_llegada ?? '—'}</p>
            `;


         /* =========================
   WHATSAPP FORMATO EMPRESARIAL
========================= */

const numero = r.numero_vendedor || '';

const fecha = new Date().toLocaleDateString('es-AR');

let mensaje = '';

mensaje += `ORDEN DE PEDIDO - MOTOSHOPPY\n\n`;
mensaje += `Proveedor: ${r.vendedor || ''}\n`;

mensaje += `DETALLE DEL PEDIDO\n\n`;

r.productos.forEach(p => {
    mensaje += `Producto: ${p.producto}\n`;
    mensaje += `Marca: ${p.marca || '-'}\n`;
    mensaje += `Código proveedor: ${p.codigo_proveedor || '-'}\n`;
    mensaje += `Cantidad solicitada: ${p.cantidad}\n`;
    mensaje += `\n`;
});

mensaje += `Solicitamos confirmar disponibilidad y plazo de entrega.\n\n`;
mensaje += `Saludos cordiales.\n`;
mensaje += `Luciano Barros\n`;
mensaje += `Área de Compras\n`;
mensaje += `MotoShoppy`;

if (numero) {
    const urlWA = `https://wa.me/${numero}?text=${encodeURIComponent(mensaje)}`;

    html += `
        <a href="${urlWA}"
           target="_blank"
           class="btn btn-success mb-3">
           Enviar orden por WhatsApp
        </a>
    `;
}




            /* =========================
               COSTO / FACTURA
            ========================= */

            if (r.costo_total !== null) {
                html += `
                    <p><strong>Costo total:</strong>
                        $${parseFloat(r.costo_total).toFixed(2)}
                    </p>
                `;
            }

            if (r.numero_factura) {
                html += `
                    <p><strong>Número de factura:</strong>
                        ${r.numero_factura}
                    </p>
                `;
            }


            /* =========================
               REMITO
            ========================= */

            if (r.remito) {

                const url = 'remitos/' + r.remito;
                const ext = r.remito.split('.').pop().toLowerCase();

                html += `<p><strong>Remito / Factura:</strong><br>`;

                if (ext === 'pdf') {
                    html += `
                        <a href="${url}" target="_blank"
                           class="btn btn-outline-primary btn-sm">
                           📄 Ver PDF
                        </a>
                    `;
                } else {
                    html += `
                        <img src="${url}"
                             class="tabla-thumb zoomable-thumb border rounded">
                    `;
                }

                html += `</p>`;
            }


            /* =========================
               TABLA PRODUCTOS
            ========================= */

            html += `
                <table class="table table-bordered table-hover mt-3 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:70px">Imagen</th>
                            <th>Producto</th>
                            <th>Marca</th>
                            <th>Código proveedor</th>
                            <th style="width:120px">Cantidad</th>
                            ${r.estado === 'pedido' ? '<th style="width:120px">Acción</th>' : ''}
                        </tr>
                    </thead>
                    <tbody>
            `;

            r.productos.forEach(p => {

                const img = p.imagen
                    ? `<img src="/motoshoppy/${p.imagen}" class="tabla-thumb zoomable-thumb">`
                    : `<span class="text-muted">—</span>`;

                if (r.estado === 'pedido') {

                    html += `
                        <tr id="fila-${p.idreposicion_detalle}">

                            <td class="text-center">${img}</td>

                            <td class="fw-semibold">${p.producto}</td>

                            <td class="text-muted">${p.marca ?? 'Sin marca'}</td>

                            <td>
                                <input type="text"
                                    class="form-control form-control-sm"
                                    value="${p.codigo_proveedor ?? ''}"
                                    readonly
                                    id="codigo-${p.idreposicion_detalle}">
                            </td>

                            <td>
                                <input type="number"
                                    class="form-control form-control-sm"
                                    min="1"
                                    value="${p.cantidad}"
                                    readonly
                                    id="cantidad-${p.idreposicion_detalle}">
                            </td>

                            <td class="text-center">

                                <button class="btn btn-warning btn-sm"
                                    onclick="habilitarEdicion(${p.idreposicion_detalle})"
                                    id="btn-edit-${p.idreposicion_detalle}">
                                    ✏️
                                </button>

                                <button class="btn btn-success btn-sm d-none"
                                    onclick="confirmarEdicion(${p.idreposicion_detalle})"
                                    id="btn-ok-${p.idreposicion_detalle}">
                                    ✔
                                </button>

                                <button class="btn btn-danger btn-sm"
                                    onclick="eliminarItemDetalle(${p.idreposicion_detalle}, ${id})">
                                    ❌
                                </button>

                            </td>
                        </tr>
                    `;
                }
                else {

                    html += `
                        <tr>
                            <td class="text-center">${img}</td>
                            <td class="fw-semibold">${p.producto}</td>
                            <td class="text-muted">${p.marca ?? '-'}</td>
                            <td>${p.codigo_proveedor ?? '-'}</td>
                            <td>${p.cantidad}</td>
                        </tr>
                    `;
                }
            });

            html += `
                    </tbody>
                </table>
            `;


            document.getElementById('detallePedido').innerHTML = html;

            new bootstrap.Modal(
                document.getElementById('modalDetalle')
            ).show();

        })
        .catch(() => alert('Error de conexión'));
}





function actualizarCantidad(idDetalle, cantidad) {
    if (cantidad <= 0) {
        alert('Cantidad inválida');
        return;
    }

    fetch('api_actualizar_detalle_reposicion.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_detalle=${idDetalle}&cantidad=${cantidad}`
    })
    .then(r => r.json())
    .then(r => {
        if (!r.ok) alert('No se pudo actualizar');
    });
}

function eliminarItemDetalle(idDetalle, idReposicion) {
    if (!confirm('¿Eliminar este producto del pedido?')) return;

    fetch('api_eliminar_detalle_reposicion.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_detalle=${idDetalle}`
    })
    .then(r => r.json())
    .then(r => {
        if (!r.ok) {
            Swal.fire("Error", "No se pudo eliminar", "error");
            return;
        }

        /* 🟥 PEDIDO CANCELADO */
        if (r.pedido_cancelado) {

            Swal.fire({
                icon: "info",
                title: "Pedido cancelado",
                text: "No quedaron productos en el pedido",
                timer: 1500,
                showConfirmButton: false,
                didClose: () => {
                    cerrarModalDetalleSeguro();
                }
            });

            return;
        }

        /* 🔁 refresca modal si sigue habiendo productos */
        verDetalle(idReposicion);
    });
}





function habilitarEdicion(id) {
    const cant = document.getElementById(`cantidad-${id}`);
    const cod  = document.getElementById(`codigo-${id}`);
    const fila = document.getElementById(`fila-${id}`);

    cant.readOnly = false;
    cod.readOnly  = false;

    cant.classList.add('editando');
    cod.classList.add('editando');
    fila.classList.add('editando');

    document.getElementById(`btn-edit-${id}`).classList.add('d-none');
    document.getElementById(`btn-ok-${id}`).classList.remove('d-none');
}


function confirmarEdicion(id) {

    const cant  = document.getElementById(`cantidad-${id}`);
    const cod   = document.getElementById(`codigo-${id}`);
    const fila  = document.getElementById(`fila-${id}`);

    const cantidad = cant.value;
    const codigo   = cod.value;

    if (cantidad <= 0) {
        Swal.fire("Atención", "La cantidad debe ser mayor a 0", "warning");
        return;
    }

    fetch('api_actualizar_detalle_reposicion.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_detalle=${id}&cantidad=${cantidad}&codigo_proveedor=${encodeURIComponent(codigo)}`
    })
    .then(r => r.json())
    .then(r => {

        if (!r.ok) {
            Swal.fire("Error", "No se pudieron guardar los cambios", "error");
            return;
        }

        /* 🔒 volver a modo lectura */
        cant.readOnly = true;
        cod.readOnly  = true;

        cant.classList.remove('editando');
        cod.classList.remove('editando');

        /* 🎨 animación visual */
        fila.classList.add('guardado');
        setTimeout(() => fila.classList.remove('guardado'), 700);

        document.getElementById(`btn-edit-${id}`).classList.remove('d-none');
        document.getElementById(`btn-ok-${id}`).classList.add('d-none');

        /* ✅ FEEDBACK CLARO */
        Swal.fire({
            icon: "success",
            title: "Correcto",
            text: "El producto fue actualizado",
            timer: 1400,
            showConfirmButton: false
        });

    })
    .catch(() => {
        Swal.fire("Error", "Error de conexión", "error");
    });
}


function cerrarModalDetalleSeguro() {

    const modalEl = document.getElementById('modalDetalle');
    const modal   = bootstrap.Modal.getInstance(modalEl);

    if (modal) modal.hide();

    /* limpiar clases y backdrop */
    document.body.classList.remove('modal-open');

    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());

    /* reload seguro */
    setTimeout(() => {
        location.reload();
    }, 100);
}



</script>


<script>
$(document).ready(function () {
    $('#tablaReposiciones').DataTable({
        order: [[0, 'desc']],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        }
    });
});
</script>

<script>
   /* =====================================
   ZOOM UNIVERSAL (preview + tabla + modal)
===================================== */

document.addEventListener('click', function (e) {

    const img = e.target.closest('.zoomable, .zoomable-thumb');

    if (!img) return;

    const overlay = document.createElement('div');
    overlay.className = 'zoom-overlay';

    const big = document.createElement('img');
    big.src = img.src;

    overlay.appendChild(big);
    document.body.appendChild(overlay);

    setTimeout(() => overlay.classList.add('show'), 10);

    overlay.addEventListener('click', () => {
        overlay.classList.remove('show');
        setTimeout(() => overlay.remove(), 200);
    });

});

</script>

<?php include '../dashboard/footer.php'; ?>
