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

    /* Modal */
    .modal-header { background-color: #007bff; color: white; }
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
                <div class="col-md-4">
                    <label for="proveedor" class="form-label">Proveedor</label>
                    <select id="proveedor" class="form-select">
                        <option value="">Seleccionar...</option>
                        <?php
                        foreach ($conexion->query("SELECT * FROM proveedores ORDER BY empresa") as $p) {
                            echo "<option value='{$p['idproveedores']}'>{$p['empresa']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="categoria" class="form-label">Categoría</label>
                    <select id="categoria" class="form-select">
                        <option value="">Seleccionar...</option>
                        <?php
                        foreach ($conexion->query("SELECT * FROM categoria ORDER BY nombre_categoria") as $c) {
                            echo "<option value='{$c['idCategoria']}'>{$c['nombre_categoria']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="producto" class="form-label">Producto</label>
                    <select id="producto" class="form-select">
                        <option value="">Seleccione una categoría</option>
                    </select>
                </div>
            </div>

            <!-- Cantidad -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="cantidad" class="form-label">Cantidad</label>
                    <input type="number" id="cantidad" class="form-control" min="1">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-success btn-custom" id="btnAgregar">
                        ➕ Agregar
                    </button>
                </div>
            </div>

            <!-- Tabla items del pedido -->
            <div class="table-responsive">
                <table class="table table-bordered" id="tablaPedido">
                    <thead class="table-light">
                        <tr>
                            <th>Proveedor</th>
                            <th>Producto</th>
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

/* =========================
   FILTRAR PRODUCTOS X CATEGORIA
========================= */
categoria.addEventListener('change', () => {
    fetch(`api_get_productos.php?idCategoria=${categoria.value}`)
        .then(r => r.json())
        .then(data => {
            producto.innerHTML = '';

            if (!data.length) {
                producto.innerHTML = `<option value="">Sin productos</option>`;
                return;
            }

            data.forEach(p => {
                producto.innerHTML += `<option value="${p.idProducto}">${p.nombre}</option>`;
            });
        });
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
        cantidad: parseInt(cantidad.value)
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
                    <td>${pedidos[p].nombre}</td>
                    <td>${i.producto}</td>
                    <td>${i.cantidad}</td>
                    <td>
                        <button type="button"
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
    document.getElementById('costo_total').value = ''; // ✅ reset costo

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
                <div class="mb-2">
                    <span class="badge bg-primary">
                        Ítems: ${r.productos.length}
                    </span>
                    <span class="badge bg-secondary ms-2">
                        Unidades totales: ${totalUnidades}
                    </span>
                </div>
                ${r.productos.map(p => `
                    <div class="mb-2 border-bottom pb-2">
                        <strong>${p.nombre}</strong><br>
                        Cantidad:
                        <span class="badge bg-secondary">${p.cantidad}</span>
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
   VER DETALLE
========================= */
function verDetalle(id) {
    fetch('api_ver_detalle_pedido.php?id=' + encodeURIComponent(id))
        .then(r => r.json())
        .then(r => {
            if (!r.ok) {
                alert('Error al cargar el detalle');
                return;
            }

            let html = `
                <p><strong>Proveedor:</strong> ${r.proveedor}</p>
                <p><strong>Fecha pedido:</strong> ${r.fecha_pedido}</p>
                <p><strong>Fecha impacto:</strong> ${r.fecha_llegada ?? '—'}</p>
            `;

            /* =========================
               COSTO TOTAL
            ========================= */
            if (r.costo_total !== null) {
                html += `
                    <p>
                        <strong>Costo total:</strong>
                        $${parseFloat(r.costo_total).toFixed(2)}
                    </p>
                `;
            }

            /* =========================
               REMITO / FACTURA
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
                        <a href="${url}" target="_blank">
                            <img src="${url}"
                                 class="img-fluid rounded border"
                                 style="max-height:300px">
                        </a>
                    `;
                }

                html += `</p>`;
            }

            /* =========================
               TABLA PRODUCTOS
            ========================= */
            html += `
                <table class="table table-bordered mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            r.productos.forEach(p => {
                html += `
                    <tr>
                        <td>${p.nombre}</td>
                        <td>${p.cantidad}</td>
                    </tr>
                `;
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
        .catch(() => {
            alert('Error de conexión');
        });
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

<?php include '../dashboard/footer.php'; ?>
