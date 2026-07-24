<?php
include '../../dashboard/nav.php';
requerirRol('Administrador', 'Reponedor');
require_once '../../conexion/conexion.php';

$idProveedor = intval($_GET['proveedor'] ?? 0);
if ($idProveedor <= 0) {
    die('Proveedor inválido.');
}

$stmtProv = $conexion->prepare("SELECT * FROM proveedores WHERE idproveedores = ?");
$stmtProv->execute([$idProveedor]);
$proveedor = $stmtProv->fetch(PDO::FETCH_ASSOC);

if (!$proveedor) {
    die('Proveedor no encontrado.');
}

$stmtCompras = $conexion->prepare("
    SELECT * FROM factura_proveedor
    WHERE proveedores_idproveedores = ?
    ORDER BY fecha_compra DESC, idFacturaProveedor DESC
");
$stmtCompras->execute([$idProveedor]);
$compras = $stmtCompras->fetchAll(PDO::FETCH_ASSOC);

$totalComprado = 0;
$totalPagado   = 0;
foreach ($compras as $c) {
    $totalComprado += (float)$c['monto'];
    $totalPagado   += (float)$c['monto_pagado'];
}
$saldo = $totalComprado - $totalPagado;
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
.cuenta-header { padding: 1.4rem 2rem .6rem; }
.stat-mini {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 10px;
    padding: .8rem 1.4rem;
    display: flex;
    align-items: center;
    gap: .8rem;
    color: #fff;
    min-width: 180px;
}
.stat-mini .num { font-size: 1.5rem; font-weight: 700; }
.stat-mini .lbl { font-size: .72rem; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; }
.table-cuenta { background: #0f172a; border-radius: 10px; overflow: hidden; border: 1px solid #1e293b; }
.table-cuenta th {
    font-size: .72rem; text-transform: uppercase; letter-spacing: .07em;
    background: #1e293b !important; color: #94a3b8; border-bottom: 1px solid #334155;
}
.table-cuenta td { font-size: .9rem; vertical-align: middle; border-color: #1e293b; }
</style>

<div class="cuenta-header">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="../index.php" class="text-secondary text-decoration-none small">
                <i class="fa-solid fa-arrow-left me-1"></i>Volver a Proveedores
            </a>
            <h2 class="text-white mb-0 mt-1">
                <i class="fa-solid fa-file-invoice-dollar me-2"></i>
                Cuenta — <?= htmlspecialchars($proveedor['empresa']) ?>
            </h2>
        </div>
        <button class="btn btn-success fw-semibold" data-bs-toggle="modal" data-bs-target="#modalCompra">
            <i class="fa-solid fa-plus me-1"></i> Cargar compra / factura
        </button>
    </div>

    <div class="d-flex gap-3 flex-wrap mb-3">
        <div class="stat-mini">
            <i class="fa-solid fa-cart-shopping fa-lg text-info"></i>
            <div>
                <div class="num">₲<?= number_format($totalComprado, 0, ',', '.') ?></div>
                <div class="lbl">Total comprado</div>
            </div>
        </div>
        <div class="stat-mini">
            <i class="fa-solid fa-hand-holding-dollar fa-lg text-success"></i>
            <div>
                <div class="num" style="color:#22c55e">₲<?= number_format($totalPagado, 0, ',', '.') ?></div>
                <div class="lbl">Total pagado</div>
            </div>
        </div>
        <div class="stat-mini">
            <i class="fa-solid fa-scale-unbalanced fa-lg text-warning"></i>
            <div>
                <div class="num" style="color:<?= $saldo > 0 ? '#f87171' : '#22c55e' ?>">₲<?= number_format($saldo, 0, ',', '.') ?></div>
                <div class="lbl">Saldo pendiente</div>
            </div>
        </div>
    </div>

    <div class="table-cuenta">
        <div class="table-responsive">
            <table id="tablaCuenta" class="table table-dark align-middle mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>N° Factura</th>
                        <th>¿Dejó factura?</th>
                        <th>Monto</th>
                        <th>Pagado</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($compras as $c):
                        $saldoFila = (float)$c['monto'] - (float)$c['monto_pagado'];
                        $badges = [
                            'pendiente' => 'bg-danger',
                            'parcial'   => 'bg-warning text-dark',
                            'pagado'    => 'bg-success',
                        ];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($c['fecha_compra']))) ?></td>
                            <td><?= htmlspecialchars($c['descripcion'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($c['numero_factura'] ?: '-') ?></td>
                            <td>
                                <?= $c['tiene_factura']
                                    ? '<span class="badge bg-success">Sí</span>'
                                    : '<span class="badge bg-secondary">No</span>' ?>
                            </td>
                            <td>₲<?= number_format($c['monto'], 0, ',', '.') ?></td>
                            <td>₲<?= number_format($c['monto_pagado'], 0, ',', '.') ?></td>
                            <td>₲<?= number_format($saldoFila, 0, ',', '.') ?></td>
                            <td><span class="badge <?= $badges[$c['estado_pago']] ?? 'bg-secondary' ?>"><?= ucfirst($c['estado_pago']) ?></span></td>
                            <td class="text-center">
                                <?php if ($saldoFila > 0): ?>
                                <button class="btn btn-primary btn-sm btn-registrar-pago"
                                    data-id="<?= $c['idFacturaProveedor'] ?>"
                                    data-saldo="<?= $saldoFila ?>">
                                    <i class="fa-solid fa-money-bill"></i> Registrar pago
                                </button>
                                <?php else: ?>
                                    <span class="text-secondary">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===== MODAL CARGAR COMPRA ===== -->
<div class="modal fade" id="modalCompra" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title"><i class="fa-solid fa-plus me-2 text-success"></i>Cargar compra / factura</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small text-secondary">Fecha de compra *</label>
            <input type="date" id="f_fecha" class="form-control bg-dark text-white border-secondary" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label small text-secondary">Monto *</label>
            <input type="number" id="f_monto" min="0" step="0.01" class="form-control bg-dark text-white border-secondary" required>
          </div>
          <div class="col-12">
            <label class="form-label small text-secondary">Descripción (qué se compró)</label>
            <textarea id="f_descripcion" class="form-control bg-dark text-white border-secondary"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label small text-secondary">N° Factura</label>
            <input type="text" id="f_numero_factura" class="form-control bg-dark text-white border-secondary">
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <div class="form-check">
              <input type="checkbox" id="f_tiene_factura" class="form-check-input">
              <label class="form-check-label" for="f_tiene_factura">El proveedor dejó factura</label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success fw-semibold" id="btnGuardarCompra">
          <i class="fa-solid fa-save me-1"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ===== MODAL REGISTRAR PAGO ===== -->
<div class="modal fade" id="modalPago" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title"><i class="fa-solid fa-money-bill me-2 text-primary"></i>Registrar pago</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="p_id">
        <p class="mb-2">Saldo pendiente: <strong id="p_saldo_texto">₲0</strong></p>
        <label class="form-label small text-secondary">Monto a pagar *</label>
        <input type="number" id="p_monto" min="0" step="0.01" class="form-control bg-dark text-white border-secondary" required>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary fw-semibold" id="btnConfirmarPago">
          <i class="fa-solid fa-check me-1"></i> Confirmar pago
        </button>
      </div>
    </div>
  </div>
</div>

<?php include '../../dashboard/footer.php'; ?>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const ID_PROVEEDOR = <?= (int)$idProveedor ?>;

$(document).ready(function () {
    $('#tablaCuenta').DataTable({
        responsive: false,
        pageLength: 15,
        lengthMenu: [10, 15, 25, 50],
        order: [[0, 'desc']],
        columnDefs: [{ targets: [8], orderable: false }],
        language: {
            search:       "Buscar en tabla:",
            lengthMenu:   "Mostrar _MENU_ registros",
            info:         "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoFiltered: "(filtrado de _MAX_)",
            zeroRecords:  "No se encontraron registros",
            emptyTable:   "Todavía no hay compras cargadas",
            paginate: { previous: "Anterior", next: "Siguiente" }
        }
    });

    // === GUARDAR COMPRA ===
    $('#btnGuardarCompra').on('click', function () {
        const payload = {
            proveedor_id: ID_PROVEEDOR,
            fecha_compra: $('#f_fecha').val(),
            monto: $('#f_monto').val(),
            descripcion: $('#f_descripcion').val(),
            numero_factura: $('#f_numero_factura').val(),
            tiene_factura: $('#f_tiene_factura').is(':checked') ? 1 : 0
        };

        if (!payload.fecha_compra || !payload.monto || parseFloat(payload.monto) <= 0) {
            Swal.fire('Atención', 'Completá fecha y monto válidos.', 'warning');
            return;
        }

        fetch('api/guardar_compra.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                Swal.fire('Listo', 'Compra registrada correctamente', 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', data.msg || 'No se pudo guardar.', 'error');
            }
        })
        .catch(() => Swal.fire('Error de conexión', '', 'error'));
    });

    // === ABRIR MODAL PAGO ===
    $(document).on('click', '.btn-registrar-pago', function () {
        const id = $(this).data('id');
        const saldo = parseFloat($(this).data('saldo'));
        $('#p_id').val(id);
        $('#p_saldo_texto').text('₲' + saldo.toLocaleString('es-PY'));
        $('#p_monto').val('').attr('max', saldo);
        new bootstrap.Modal('#modalPago').show();
    });

    // === CONFIRMAR PAGO ===
    $('#btnConfirmarPago').on('click', function () {
        const payload = {
            id: $('#p_id').val(),
            monto: $('#p_monto').val()
        };

        if (!payload.monto || parseFloat(payload.monto) <= 0) {
            Swal.fire('Atención', 'Ingresá un monto válido.', 'warning');
            return;
        }

        fetch('api/registrar_pago.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                Swal.fire('Listo', 'Pago registrado correctamente', 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', data.msg || 'No se pudo registrar el pago.', 'error');
            }
        })
        .catch(() => Swal.fire('Error de conexión', '', 'error'));
    });
});
</script>
