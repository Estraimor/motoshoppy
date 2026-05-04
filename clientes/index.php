<?php
include '../dashboard/nav.php';
requerirRol('Administrador', 'Ventas');
require_once '../conexion/conexion.php';

$totalClientes = $conexion->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$totalVentas   = $conexion->query("SELECT COUNT(*) FROM ventas")->fetchColumn();
$totalGastado  = $conexion->query("SELECT IFNULL(SUM(total),0) FROM ventas")->fetchColumn();

$stmt = $conexion->prepare("
    SELECT
        c.idCliente,
        c.apellido,
        c.nombre,
        c.dni,
        c.celular,
        c.email,
        c.fecha_alta,
        c.estado,
        COUNT(v.idVenta) AS cantidad_compras,
        IFNULL(SUM(v.total),0) AS total_gastado
    FROM clientes c
    LEFT JOIN ventas v ON v.clientes_idCliente = c.idCliente
    GROUP BY c.idCliente
    ORDER BY c.apellido ASC
");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="clientes.css">

<style>
.clientes-wrapper { padding: 1.5rem 2rem; }

.clientes-stats { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }

.stat-card {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    border: 1px solid #334155;
    border-radius: 12px;
    padding: 1.2rem 1.8rem;
    flex: 1;
    min-width: 160px;
    color: #fff;
}
.stat-card h4 { font-size: .78rem; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; margin-bottom: .4rem; }
.stat-card span { font-size: 2rem; font-weight: 700; }
.stat-card.success span { color: #22c55e; }
.stat-card.info    span { color: #38bdf8; }
.stat-card.warning span { color: #f59e0b; }

.clientes-table-container { background: #0f172a; border-radius: 12px; border: 1px solid #334155; overflow: hidden; }

.btn-accion { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }

#tablaClientes th { font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; }

.badge-compras { background: #1d4ed8; font-size: .8rem; }
.badge-estado-activo  { background: #166534; color: #bbf7d0; font-size: .75rem; }
.badge-estado-inactivo { background: #7f1d1d; color: #fecaca; font-size: .75rem; }
</style>

<div class="clientes-wrapper fade-in">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="text-white mb-0"><i class="fa-solid fa-users me-2"></i>Gestión de Clientes</h2>
        <button class="btn btn-success fw-semibold" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="fa-solid fa-user-plus me-1"></i> Nuevo Cliente
        </button>
    </div>

    <!-- STATS -->
    <div class="clientes-stats">
        <div class="stat-card">
            <h4><i class="fa-solid fa-users me-1"></i> Clientes</h4>
            <span><?= $totalClientes ?></span>
        </div>
        <div class="stat-card success">
            <h4><i class="fa-solid fa-cart-shopping me-1"></i> Total Ventas</h4>
            <span><?= $totalVentas ?></span>
        </div>
        <div class="stat-card info">
            <h4><i class="fa-solid fa-dollar-sign me-1"></i> Facturado</h4>
            <span>$<?= number_format($totalGastado, 0, ',', '.') ?></span>
        </div>
    </div>

    <!-- TABLA -->
    <div class="clientes-table-container p-3">
        <table id="tablaClientes" class="table table-dark table-hover align-middle text-center w-100">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>DNI</th>
                    <th>Celular</th>
                    <th>Email</th>
                    <th>Alta</th>
                    <th>Estado</th>
                    <th>Compras</th>
                    <th>Total Gastado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td class="text-start fw-semibold">
                            <?= htmlspecialchars($c['apellido'] . ', ' . $c['nombre']) ?>
                        </td>
                        <td><?= htmlspecialchars($c['dni']) ?></td>
                        <td><?= htmlspecialchars($c['celular'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($c['email'] ?: '-') ?></td>
                        <td><?= $c['fecha_alta'] ? date('d/m/Y', strtotime($c['fecha_alta'])) : '-' ?></td>
                        <td>
                            <?php if ($c['estado'] ?? 1): ?>
                                <span class="badge badge-estado-activo">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-estado-inactivo">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-compras"><?= $c['cantidad_compras'] ?></span>
                        </td>
                        <td class="text-success fw-bold">
                            $<?= number_format($c['total_gastado'], 0, ',', '.') ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <!-- Editar -->
                                <button class="btn btn-warning btn-accion btn-editar"
                                    title="Editar"
                                    data-id="<?= $c['idCliente'] ?>"
                                    data-nombre="<?= htmlspecialchars($c['nombre']) ?>"
                                    data-apellido="<?= htmlspecialchars($c['apellido']) ?>"
                                    data-dni="<?= htmlspecialchars($c['dni']) ?>"
                                    data-celular="<?= htmlspecialchars($c['celular']) ?>"
                                    data-email="<?= htmlspecialchars($c['email'] ?? '') ?>">
                                    <i class="fa-solid fa-pen fa-xs"></i>
                                </button>
                                <!-- Eliminar -->
                                <button class="btn btn-danger btn-accion btn-eliminar"
                                    title="Eliminar"
                                    data-id="<?= $c['idCliente'] ?>"
                                    data-nombre="<?= htmlspecialchars($c['apellido'] . ' ' . $c['nombre']) ?>"
                                    data-compras="<?= $c['cantidad_compras'] ?>">
                                    <i class="fa-solid fa-trash fa-xs"></i>
                                </button>
                                <!-- Insight -->
                                <button class="btn btn-outline-warning btn-accion btnInsight"
                                    title="Ver qué compra"
                                    data-id="<?= $c['idCliente'] ?>"
                                    data-nombre="<?= htmlspecialchars($c['apellido'] . ' ' . $c['nombre']) ?>">
                                    <i class="fa-solid fa-chart-bar fa-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===== MODAL CREAR ===== -->
<div class="modal fade" id="modalCrear" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white border-secondary">
      <form action="crear_cliente.php" method="POST">
        <div class="modal-header border-secondary">
          <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2 text-success"></i>Nuevo Cliente</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label small text-secondary">Nombre *</label>
              <input type="text" name="nombre" class="form-control bg-dark text-white border-secondary" required>
            </div>
            <div class="col-6">
              <label class="form-label small text-secondary">Apellido *</label>
              <input type="text" name="apellido" class="form-control bg-dark text-white border-secondary" required>
            </div>
            <div class="col-6">
              <label class="form-label small text-secondary">DNI *</label>
              <input type="text" name="dni" class="form-control bg-dark text-white border-secondary" required maxlength="20">
            </div>
            <div class="col-6">
              <label class="form-label small text-secondary">Celular</label>
              <input type="text" name="celular" class="form-control bg-dark text-white border-secondary" maxlength="30">
            </div>
            <div class="col-12">
              <label class="form-label small text-secondary">Email</label>
              <input type="email" name="email" class="form-control bg-dark text-white border-secondary">
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
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white border-secondary">
      <form action="actualizar_cliente.php" method="POST">
        <input type="hidden" name="idCliente" id="edit_id">
        <div class="modal-header border-secondary">
          <h5 class="modal-title"><i class="fa-solid fa-pen me-2 text-warning"></i>Editar Cliente</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label small text-secondary">Nombre *</label>
              <input type="text" name="nombre" id="edit_nombre" class="form-control bg-dark text-white border-secondary" required>
            </div>
            <div class="col-6">
              <label class="form-label small text-secondary">Apellido *</label>
              <input type="text" name="apellido" id="edit_apellido" class="form-control bg-dark text-white border-secondary" required>
            </div>
            <div class="col-6">
              <label class="form-label small text-secondary">DNI *</label>
              <input type="text" name="dni" id="edit_dni" class="form-control bg-dark text-white border-secondary" required maxlength="20">
            </div>
            <div class="col-6">
              <label class="form-label small text-secondary">Celular</label>
              <input type="text" name="celular" id="edit_celular" class="form-control bg-dark text-white border-secondary" maxlength="30">
            </div>
            <div class="col-12">
              <label class="form-label small text-secondary">Email</label>
              <input type="email" name="email" id="edit_email" class="form-control bg-dark text-white border-secondary">
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

<!-- ===== MODAL INSIGHT ===== -->
<div class="modal fade" id="modalInsight" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title"><i class="fa-solid fa-chart-bar me-2 text-warning"></i>Análisis del Cliente</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center" id="modalInsightBody">
        <div class="spinner-border text-warning" role="status"></div>
      </div>
    </div>
  </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<script>
$(document).ready(function () {

    $('#tablaClientes').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        order: [[6, 'desc']],
        columnDefs: [
            { targets: [8], orderable: false }
        ],
        language: {
            search:       "Buscar:",
            lengthMenu:   "Mostrar _MENU_ clientes",
            info:         "Mostrando _START_ a _END_ de _TOTAL_ clientes",
            infoFiltered: "(filtrado de _MAX_)",
            zeroRecords:  "No se encontraron clientes",
            emptyTable:   "No hay clientes cargados",
            paginate: { previous: "Anterior", next: "Siguiente" }
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa-solid fa-file-excel me-1"></i>Excel',
                className: 'btn btn-success btn-sm',
                filename: 'Clientes_Motoshoppy',
                title: 'Motoshoppy — Clientes',
                exportOptions: { columns: [0,1,2,3,4,5,6,7] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa-solid fa-file-pdf me-1"></i>PDF',
                className: 'btn btn-danger btn-sm',
                filename: 'Clientes_Motoshoppy',
                title: 'Motoshoppy — Listado de Clientes',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0,1,2,3,4,5,6,7] },
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

    // === ABRIR MODAL EDITAR ===
    $(document).on('click', '.btn-editar', function () {
        const btn = $(this);
        $('#edit_id').val(btn.data('id'));
        $('#edit_nombre').val(btn.data('nombre'));
        $('#edit_apellido').val(btn.data('apellido'));
        $('#edit_dni').val(btn.data('dni'));
        $('#edit_celular').val(btn.data('celular'));
        $('#edit_email').val(btn.data('email'));
        new bootstrap.Modal('#modalEditar').show();
    });

    // === ELIMINAR ===
    $(document).on('click', '.btn-eliminar', function () {
        const id      = $(this).data('id');
        const nombre  = $(this).data('nombre');
        const compras = parseInt($(this).data('compras'));

        if (compras > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No se puede eliminar',
                html: `<b>${nombre}</b> tiene <strong>${compras}</strong> compra(s) registrada(s).<br><br>No es posible eliminar un cliente con historial de ventas.`,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        Swal.fire({
            title: '¿Eliminar cliente?',
            html: `¿Estás seguro de eliminar a <b>${nombre}</b>? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                window.location.href = `eliminar_cliente.php?id=${id}`;
            }
        });
    });

    // === INSIGHT ===
    $(document).on('click', '.btnInsight', function () {
        const id     = $(this).data('id');
        const nombre = $(this).data('nombre');

        $('#modalInsightBody').html('<div class="spinner-border text-warning" role="status"></div>');
        new bootstrap.Modal('#modalInsight').show();

        $.post('api_cliente_insight.php', { cliente: id }, function (data) {
            const res = JSON.parse(data);
            if (res) {
                $('#modalInsightBody').html(`
                    <h5 class="text-warning mb-3">${nombre}</h5>
                    <p class="text-secondary mb-1">Producto más comprado:</p>
                    <h4 class="text-white">${res.nombre}</h4>
                    <span class="badge bg-info fs-6 mt-2">Comprado ${res.veces} vez/veces</span>
                `);
            } else {
                $('#modalInsightBody').html(`
                    <i class="fa-solid fa-circle-info fa-2x text-secondary mb-3"></i>
                    <p class="text-secondary">Este cliente aún no tiene historial suficiente.</p>
                `);
            }
        });
    });

    // === TOAST por msg en URL ===
    const params = new URLSearchParams(window.location.search);
    const msg = params.get('msg');
    if (msg) {
        const msgs = {
            creado:      { icon: 'success', title: 'Cliente creado', text: 'El cliente fue agregado correctamente.' },
            actualizado: { icon: 'success', title: 'Cliente actualizado', text: 'Los datos fueron guardados.' },
            eliminado:   { icon: 'warning', title: 'Cliente eliminado', text: 'El cliente fue eliminado.' },
            error_datos: { icon: 'error',   title: 'Error', text: 'Faltan datos obligatorios.' },
            error:       { icon: 'error',   title: 'Error', text: 'Ocurrió un error inesperado.' }
        };
        const m = msgs[msg];
        if (m) {
            Swal.fire({ icon: m.icon, title: m.title, text: m.text, timer: 2000, showConfirmButton: false });
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
});
</script>

<?php include '../dashboard/footer.php'; ?>
