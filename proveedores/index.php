<?php
include '../dashboard/nav.php';
requerirRol('Administrador', 'Reponedor');
require_once '../conexion/conexion.php';

$proveedores = $conexion
    ->query("SELECT * FROM proveedores ORDER BY empresa ASC")
    ->fetchAll();

$totalActivos   = array_sum(array_column($proveedores, 'activo'));
$totalInactivos = count($proveedores) - $totalActivos;
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<style>
.prov-header { padding: 1.4rem 2rem .6rem; }
.stat-mini {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 10px;
    padding: .8rem 1.4rem;
    display: flex;
    align-items: center;
    gap: .8rem;
    color: #fff;
    min-width: 160px;
}
.stat-mini .num { font-size: 1.7rem; font-weight: 700; }
.stat-mini .lbl { font-size: .72rem; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; }

.filtros-bar {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 10px;
    padding: .8rem 1.2rem;
    display: flex;
    flex-wrap: wrap;
    gap: .8rem;
    align-items: center;
    margin-bottom: 1rem;
}
.filtros-bar label { font-size: .78rem; color: #94a3b8; margin-bottom: 0; }
.filtros-bar .form-control,
.filtros-bar .form-select {
    background: #0f172a;
    color: #fff;
    border: 1px solid #475569;
    border-radius: 7px;
    font-size: .85rem;
}
.filtros-bar .form-control::placeholder { color: #64748b; }

.table-prov { background: #0f172a; border-radius: 10px; overflow: hidden; border: 1px solid #1e293b; }
.table-prov th {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .07em;
    background: #1e293b !important;
    color: #94a3b8;
    border-bottom: 1px solid #334155;
}
.table-prov td { font-size: .9rem; vertical-align: middle; border-color: #1e293b; }
.table-prov tbody tr:hover { background: rgba(255,255,255,.03); }

.btn-accion-sm { width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }

/* DataTable overrides */
.dataTables_wrapper .dataTables_filter input {
    background: #1e293b; color: #fff; border: 1px solid #475569; border-radius: 7px; padding: 4px 10px;
}
.dataTables_wrapper .dataTables_length select {
    background: #1e293b; color: #fff; border: 1px solid #475569; border-radius: 7px;
}
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate { color: #94a3b8; }
.dataTables_wrapper .page-link { background: #1e293b; color: #fff; border-color: #334155; }
.dataTables_wrapper .page-item.active .page-link { background: #3b82f6; border-color: #3b82f6; }
</style>

<!-- TOAST -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1100;">
    <div id="toastProv" class="toast border-0" role="alert">
        <div class="d-flex">
            <div id="toastProvBody" class="toast-body fw-semibold"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="prov-header">

    <!-- Título + botón -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="text-white mb-0"><i class="fa-solid fa-truck-field me-2"></i>Proveedores</h2>
        <button class="btn btn-success fw-semibold" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="fa-solid fa-plus me-1"></i> Nuevo Proveedor
        </button>
    </div>

    <!-- Stats -->
    <div class="d-flex gap-3 flex-wrap mb-3">
        <div class="stat-mini">
            <i class="fa-solid fa-building fa-lg text-info"></i>
            <div>
                <div class="num"><?= count($proveedores) ?></div>
                <div class="lbl">Total</div>
            </div>
        </div>
        <div class="stat-mini">
            <i class="fa-solid fa-circle-check fa-lg text-success"></i>
            <div>
                <div class="num" style="color:#22c55e"><?= $totalActivos ?></div>
                <div class="lbl">Activos</div>
            </div>
        </div>
        <div class="stat-mini">
            <i class="fa-solid fa-circle-xmark fa-lg text-danger"></i>
            <div>
                <div class="num" style="color:#f87171"><?= $totalInactivos ?></div>
                <div class="lbl">Inactivos</div>
            </div>
        </div>
    </div>

    <!-- Filtros rápidos -->
    <div class="filtros-bar">
        <div class="d-flex flex-column">
            <label><i class="fa-solid fa-magnifying-glass me-1"></i>Buscar</label>
            <input type="text" id="filtroTexto" class="form-control" placeholder="Empresa, vendedor..." style="min-width:220px">
        </div>
        <div class="d-flex flex-column">
            <label><i class="fa-solid fa-toggle-on me-1"></i>Estado</label>
            <select id="filtroEstado" class="form-select" style="min-width:140px">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>
        <div class="d-flex align-items-end">
            <button class="btn btn-outline-secondary btn-sm" id="btnLimpiar">
                <i class="fa-solid fa-rotate-left me-1"></i>Limpiar
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="table-prov">
        <div class="table-responsive">
            <table id="tablaProveedores" class="table table-dark align-middle mb-0">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Ubicación</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Vendedor</th>
                        <th>Tel. Vendedor</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $p): ?>
                        <tr data-activo="<?= $p['activo'] ?>">
                            <td class="fw-semibold"><?= htmlspecialchars($p['empresa']) ?></td>
                            <td><?= htmlspecialchars($p['ubicacion'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($p['telefono'] ?: '-') ?></td>
                            <td>
                                <?php if ($p['email']): ?>
                                    <a href="mailto:<?= htmlspecialchars($p['email']) ?>" class="text-info">
                                        <?= htmlspecialchars($p['email']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p['vendedor'] ?: '-') ?></td>
                            <td>
                                <?php if ($p['numero_vendedor']): ?>
                                    <a href="tel:<?= htmlspecialchars($p['numero_vendedor']) ?>" class="text-success">
                                        <?= htmlspecialchars($p['numero_vendedor']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $p['activo']
                                    ? '<span class="badge bg-success">Activo</span>'
                                    : '<span class="badge bg-danger">Inactivo</span>' ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <!-- Editar -->
                                    <button class="btn btn-warning btn-accion-sm btn-editar" title="Editar"
                                        data-id="<?= $p['idproveedores'] ?>"
                                        data-empresa="<?= htmlspecialchars($p['empresa']) ?>"
                                        data-ubicacion="<?= htmlspecialchars($p['ubicacion']) ?>"
                                        data-telefono="<?= htmlspecialchars($p['telefono']) ?>"
                                        data-email="<?= htmlspecialchars($p['email']) ?>"
                                        data-vendedor="<?= htmlspecialchars($p['vendedor']) ?>"
                                        data-numero_vendedor="<?= htmlspecialchars($p['numero_vendedor']) ?>">
                                        <i class="fa-solid fa-pen fa-xs"></i>
                                    </button>
                                    <!-- Toggle estado -->
                                    <button class="btn btn-accion-sm <?= $p['activo'] ? 'btn-danger' : 'btn-success' ?> btn-toggle"
                                        title="<?= $p['activo'] ? 'Dar de baja' : 'Activar' ?>"
                                        data-id="<?= $p['idproveedores'] ?>"
                                        data-activo="<?= $p['activo'] ?>">
                                        <i class="fa-solid <?= $p['activo'] ? 'fa-ban' : 'fa-check' ?> fa-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===== MODAL CREAR ===== -->
<div class="modal fade" id="modalCrear" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark text-white border-secondary">
      <form action="insertar_provee.php" method="POST">
        <div class="modal-header border-secondary">
          <h5 class="modal-title"><i class="fa-solid fa-plus me-2 text-success"></i>Nuevo Proveedor</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small text-secondary">Empresa *</label>
              <input type="text" name="empresa" class="form-control bg-dark text-white border-secondary" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-secondary">Ubicación</label>
              <input type="text" name="ubicacion" class="form-control bg-dark text-white border-secondary">
            </div>
            <div class="col-md-6">
              <label class="form-label small text-secondary">Teléfono Empresa</label>
              <input type="text" name="telefono" class="form-control bg-dark text-white border-secondary">
            </div>
            <div class="col-md-6">
              <label class="form-label small text-secondary">Email</label>
              <input type="email" name="email" class="form-control bg-dark text-white border-secondary">
            </div>
            <div class="col-md-6">
              <label class="form-label small text-secondary">Vendedor</label>
              <input type="text" name="vendedor" class="form-control bg-dark text-white border-secondary">
            </div>
            <div class="col-md-6">
              <label class="form-label small text-secondary">Teléfono Vendedor</label>
              <input type="text" name="numero_vendedor" class="form-control bg-dark text-white border-secondary">
            </div>
          </div>
        </div>
        <div class="modal-footer border-secondary">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success fw-semibold">
            <i class="fa-solid fa-save me-1"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ===== MODAL EDITAR ===== -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark text-white border-secondary">
      <form action="actualizar_provee.php" method="POST">
        <input type="hidden" name="idproveedores" id="edit_id">
        <div class="modal-header border-secondary">
          <h5 class="modal-title"><i class="fa-solid fa-pen me-2 text-warning"></i>Editar Proveedor</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small text-secondary">Empresa *</label>
              <input type="text" name="empresa" id="edit_empresa" class="form-control bg-dark text-white border-secondary" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-secondary">Ubicación</label>
              <input type="text" name="ubicacion" id="edit_ubicacion" class="form-control bg-dark text-white border-secondary">
            </div>
            <div class="col-md-6">
              <label class="form-label small text-secondary">Teléfono Empresa</label>
              <input type="text" name="telefono" id="edit_telefono" class="form-control bg-dark text-white border-secondary">
            </div>
            <div class="col-md-6">
              <label class="form-label small text-secondary">Email</label>
              <input type="email" name="email" id="edit_email" class="form-control bg-dark text-white border-secondary">
            </div>
            <div class="col-md-6">
              <label class="form-label small text-secondary">Vendedor</label>
              <input type="text" name="vendedor" id="edit_vendedor" class="form-control bg-dark text-white border-secondary">
            </div>
            <div class="col-md-6">
              <label class="form-label small text-secondary">Teléfono Vendedor</label>
              <input type="text" name="numero_vendedor" id="edit_numero_vendedor" class="form-control bg-dark text-white border-secondary">
            </div>
          </div>
        </div>
        <div class="modal-footer border-secondary">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning fw-semibold">
            <i class="fa-solid fa-save me-1"></i> Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ===== MODAL CONFIRMAR TOGGLE ===== -->
<div class="modal fade" id="modalToggle" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title" id="toggleTitulo">Confirmar acción</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="fs-1 mb-3" id="toggleIcono">⚠️</div>
        <p class="fw-semibold mb-0" id="toggleTexto"></p>
      </div>
      <div class="modal-footer border-secondary justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a href="#" id="btnConfirmarToggle" class="btn btn-danger fw-semibold">Confirmar</a>
      </div>
    </div>
  </div>
</div>

<?php include '../dashboard/footer.php'; ?>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function () {

    // === DATATABLE ===
    const tabla = $('#tablaProveedores').DataTable({
        responsive: true,
        pageLength: 15,
        lengthMenu: [10, 15, 25, 50],
        columnDefs: [{ targets: [7], orderable: false }],
        language: {
            search:       "Buscar en tabla:",
            lengthMenu:   "Mostrar _MENU_ proveedores",
            info:         "Mostrando _START_ a _END_ de _TOTAL_ proveedores",
            infoFiltered: "(filtrado de _MAX_)",
            zeroRecords:  "No se encontraron proveedores",
            emptyTable:   "No hay proveedores cargados",
            paginate: { previous: "Anterior", next: "Siguiente" }
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa-solid fa-file-excel me-1"></i>Excel',
                className: 'btn btn-success btn-sm',
                filename: 'Proveedores_Motoshoppy',
                title: 'Motoshoppy — Proveedores',
                exportOptions: { columns: [0,1,2,3,4,5,6] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa-solid fa-file-pdf me-1"></i>PDF',
                className: 'btn btn-danger btn-sm',
                filename: 'Proveedores_Motoshoppy',
                title: 'Motoshoppy — Listado de Proveedores',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0,1,2,3,4,5,6] },
                customize: function(doc) {
                    // Título
                    doc.content[0].fontSize  = 16;
                    doc.content[0].bold      = true;
                    doc.content[0].color     = '#1e3a5f';
                    doc.content[0].alignment = 'center';
                    doc.content[0].margin    = [0, 0, 0, 4];

                    // Fecha
                    doc.content.splice(1, 0, {
                        text: 'Generado: ' + new Date().toLocaleDateString('es-AR'),
                        alignment: 'right', fontSize: 8,
                        color: '#666666', margin: [0, 0, 0, 10]
                    });

                    // Encabezado de tabla
                    doc.styles.tableHeader = {
                        bold: true, fontSize: 9,
                        color: '#ffffff', fillColor: '#1e3a5f',
                        alignment: 'center'
                    };

                    // Filas alternadas
                    const body = doc.content[2].table.body;
                    for (let i = 1; i < body.length; i++) {
                        body[i].forEach(cell => {
                            if (typeof cell === 'object') {
                                cell.fillColor = i % 2 === 0 ? '#eef2f7' : '#ffffff';
                                cell.fontSize  = 8;
                                cell.color     = '#222222';
                            }
                        });
                    }

                    // Anchos iguales
                    doc.content[2].table.widths =
                        Array(doc.content[2].table.body[0].length).fill('*');
                }
            }
        ]
    });

    // === FILTRO RÁPIDO POR TEXTO ===
    let filtroEstadoFn = null;

    function aplicarFiltros() {
        const texto  = $('#filtroTexto').val().toLowerCase().trim();
        const estado = $('#filtroEstado').val();

        // Remover filtro anterior
        if (filtroEstadoFn !== null) {
            const idx = $.fn.dataTable.ext.search.indexOf(filtroEstadoFn);
            if (idx > -1) $.fn.dataTable.ext.search.splice(idx, 1);
            filtroEstadoFn = null;
        }

        if (estado !== '') {
            filtroEstadoFn = function (settings, data, dataIndex) {
                if (settings.nTable.id !== 'tablaProveedores') return true;
                const fila = settings.aoData[dataIndex].nTr;
                return fila && fila.getAttribute('data-activo') === estado;
            };
            $.fn.dataTable.ext.search.push(filtroEstadoFn);
        }

        tabla.search(texto).draw();
    }

    $('#filtroTexto').on('input', aplicarFiltros);
    $('#filtroEstado').on('change', aplicarFiltros);

    $('#btnLimpiar').on('click', function () {
        $('#filtroTexto').val('');
        $('#filtroEstado').val('');
        if (filtroEstadoFn !== null) {
            const idx = $.fn.dataTable.ext.search.indexOf(filtroEstadoFn);
            if (idx > -1) $.fn.dataTable.ext.search.splice(idx, 1);
            filtroEstadoFn = null;
        }
        tabla.search('').draw();
    });

    // === ABRIR MODAL EDITAR ===
    $(document).on('click', '.btn-editar', function () {
        const b = $(this);
        $('#edit_id').val(b.data('id'));
        $('#edit_empresa').val(b.data('empresa'));
        $('#edit_ubicacion').val(b.data('ubicacion'));
        $('#edit_telefono').val(b.data('telefono'));
        $('#edit_email').val(b.data('email'));
        $('#edit_vendedor').val(b.data('vendedor'));
        $('#edit_numero_vendedor').val(b.data('numero_vendedor'));
        new bootstrap.Modal('#modalEditar').show();
    });

    // === TOGGLE ESTADO ===
    $(document).on('click', '.btn-toggle', function () {
        const id     = $(this).data('id');
        const activo = $(this).data('activo');
        const modal  = new bootstrap.Modal('#modalToggle');
        const btn    = document.getElementById('btnConfirmarToggle');

        if (activo == 1) {
            document.getElementById('toggleTitulo').textContent = 'Dar de baja proveedor';
            document.getElementById('toggleTexto').textContent  = '¿Seguro que querés dar de baja este proveedor?';
            document.getElementById('toggleIcono').textContent  = '⛔';
            btn.className = 'btn btn-danger fw-semibold';
            btn.textContent = 'Sí, dar de baja';
        } else {
            document.getElementById('toggleTitulo').textContent = 'Activar proveedor';
            document.getElementById('toggleTexto').textContent  = '¿Seguro que querés activar este proveedor?';
            document.getElementById('toggleIcono').textContent  = '✅';
            btn.className = 'btn btn-success fw-semibold';
            btn.textContent = 'Sí, activar';
        }

        btn.href = `baja_provee.php?toggle=${id}`;
        modal.show();
    });

    // === TOAST POR MSG EN URL ===
    const params = new URLSearchParams(window.location.search);
    const msg    = params.get('msg');
    if (msg) {
        const toastEl   = document.getElementById('toastProv');
        const toastBody = document.getElementById('toastProvBody');
        let texto = '', clase = 'bg-success text-white';

        if (msg === 'insertado')   { texto = '✅ Proveedor agregado correctamente'; }
        else if (msg === 'actualizado') { texto = '✏️ Proveedor actualizado'; clase = 'bg-primary text-white'; }
        else if (msg === 'estado') { texto = '🔄 Estado actualizado'; clase = 'bg-warning text-dark'; }

        if (texto) {
            toastEl.className = `toast border-0 ${clase}`;
            toastBody.textContent = texto;
            new bootstrap.Toast(toastEl, { delay: 3000 }).show();
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
});
</script>
