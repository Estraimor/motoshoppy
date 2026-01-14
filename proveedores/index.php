<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';

/* =========================
   EDITAR (CARGA FORM)
========================= */
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conexion->prepare(
        "SELECT * FROM proveedores WHERE idproveedores = :id"
    );
    $stmt->execute(['id' => $id]);
    $edit = $stmt->fetch();
}

/* =========================
   LISTADO
========================= */
$proveedores = $conexion
    ->query("SELECT * FROM proveedores ORDER BY empresa ASC")
    ->fetchAll();
?>

<link rel="stylesheet" href="./proveedores.css">

<!-- ================= TOAST BOOTSTRAP ================= -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1100;">
    <div id="toastProveedor" class="toast border-0" role="alert">
        <div class="d-flex">
            <div id="toastBody" class="toast-body fw-semibold"></div>
            <button
                type="button"
                class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast">
            </button>
        </div>
    </div>
</div>

<!-- ================= MODULO PROVEEDORES ================= -->
<div class="container-fluid mt-4 modulo-proveedores">
    <div class="row">

        <!-- ================= FORM ================= -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">
                    <?= $edit ? 'Editar proveedor' : 'Nuevo proveedor' ?>
                </div>

                <div class="card-body">
                    <form
                        method="post"
                        action="<?= $edit ? 'actualizar_provee.php' : 'insertar_provee.php' ?>">

                        <?php if ($edit): ?>
                            <input
                                type="hidden"
                                name="idproveedores"
                                value="<?= $edit['idproveedores'] ?>">
                        <?php endif; ?>

                        <div class="mb-2">
                            <label>Empresa</label>
                            <input
                                type="text"
                                name="empresa"
                                class="form-control"
                                required
                                value="<?= htmlspecialchars($edit['empresa'] ?? '') ?>">
                        </div>

                        <div class="mb-2">
                            <label>Ubicación</label>
                            <input
                                type="text"
                                name="ubicacion"
                                class="form-control"
                                value="<?= htmlspecialchars($edit['ubicacion'] ?? '') ?>">
                        </div>

                        <div class="mb-2">
                            <label>Teléfono Empresa</label>
                            <input
                                type="text"
                                name="telefono"
                                class="form-control"
                                value="<?= htmlspecialchars($edit['telefono'] ?? '') ?>">
                        </div>

                        <div class="mb-2">
                            <label>Email</label>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?= htmlspecialchars($edit['email'] ?? '') ?>">
                        </div>
                            
                        <div class="mb-2">
                        <label>Vendedor</label>
                        <input
                            type="text"
                            name="vendedor"
                            class="form-control"
                            value="<?= htmlspecialchars($edit['vendedor'] ?? '') ?>">
                    </div>

                    <div class="mb-2">
                        <label>telefono Vendedor</label>
                        <input
                            type="text"
                            name="numero_vendedor"
                            class="form-control"
                            value="<?= htmlspecialchars($edit['numero_vendedor'] ?? '') ?>">
                    </div>
                        <button
                            class="btn btn-primary w-100 mt-2"
                            name="guardar">
                            <?= $edit ? 'Guardar cambios' : 'Agregar proveedor' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================= TABLA ================= -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">
                    Proveedores
                </div>

                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Empresa</th>
                                <th>Ubicación</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Vendedor</th>
                                <th>Telefono Vendedor</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($proveedores as $p):
                                $clase = $p['activo'] ? 'table-success' : 'table-danger';
                            ?>
                                <tr class="<?= $clase ?>">
                                    <td><?= htmlspecialchars($p['empresa']) ?></td>
                                    <td><?= htmlspecialchars($p['ubicacion']) ?></td>
                                    <td>
                                        <?= $p['telefono']
                                            ?: '<span class="text-muted">No se agregó</span>' ?>
                                    </td>
                                    <td>
                                        <?= $p['email']
                                            ?: '<span class="text-muted">No se agregó</span>' ?>
                                    </td>
                                    <td>
                                    <?= $p['vendedor']
                                        ?: '<span class="text-muted">No asignado</span>' ?>
                                </td>

                                <td>
                                    <?= $p['numero_vendedor']
                                        ?: '<span class="text-muted">No asignado</span>' ?>
                                </td>
                                    <td>
                                        <?= $p['activo']
                                            ? '<span class="badge bg-success">Activo</span>'
                                            : '<span class="badge bg-danger">Inactivo</span>' ?>
                                    </td>

                                    <td class="text-center">
                                        <a
                                            href="?edit=<?= $p['idproveedores'] ?>"
                                            class="btn btn-sm btn-warning">
                                            ✏️
                                        </a>

                                        <button
                                            type="button"
                                            class="btn btn-sm <?= $p['activo'] ? 'btn-danger' : 'btn-success' ?>"
                                            onclick="confirmarBaja(<?= $p['idproveedores'] ?>, <?= $p['activo'] ?>)">
                                            <?= $p['activo'] ? '⛔' : '✔' ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ===== MODAL CONFIRMACIÓN BAJA ===== -->
<div class="modal fade" id="modalConfirmarBaja" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Confirmar acción</h5>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body text-center">
                <div class="fs-1 mb-2">⚠️</div>
                <p id="textoConfirmacion" class="fw-semibold mb-0"></p>
            </div>

            <div class="modal-footer justify-content-center">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cancelar
                </button>

                <a
                    href="#"
                    id="btnConfirmarBaja"
                    class="btn btn-danger">
                    Sí, confirmar
                </a>
            </div>

        </div>
    </div>
</div>

<?php include '../dashboard/footer.php'; ?>



<!-- ================= JS ================= -->
<script>
function confirmarBaja(id, activo) {

    const modal = new bootstrap.Modal(
        document.getElementById('modalConfirmarBaja')
    );

    const texto = document.getElementById('textoConfirmacion');
    const btn = document.getElementById('btnConfirmarBaja');

    if (activo == 1) {
        texto.textContent = '¿Seguro que querés dar de baja este proveedor?';
        btn.className = 'btn btn-danger';
        btn.textContent = 'Sí, dar de baja';
    } else {
        texto.textContent = '¿Seguro que querés activar este proveedor?';
        btn.className = 'btn btn-success';
        btn.textContent = 'Sí, activar';
    }

    btn.href = `baja_provee.php?toggle=${id}`;
    modal.show();
}

// ===== TOAST =====
document.addEventListener('DOMContentLoaded', () => {

    const params = new URLSearchParams(window.location.search);
    const msg = params.get('msg');
    if (!msg) return;

    const toastEl = document.getElementById('toastProveedor');
    const toastBody = document.getElementById('toastBody');

    let texto = '';
    let clase = 'bg-success text-white';

    switch (msg) {
        case 'insertado':
            texto = '✅ Proveedor agregado correctamente';
            break;
        case 'actualizado':
            texto = '✏️ Proveedor actualizado correctamente';
            clase = 'bg-primary text-white';
            break;
        case 'estado':
            texto = '🔄 Estado del proveedor actualizado';
            clase = 'bg-warning text-dark';
            break;
        default:
            return;
    }

    toastEl.className = `toast border-0 ${clase}`;
    toastBody.textContent = texto;

    new bootstrap.Toast(toastEl, { delay: 3000 }).show();
    window.history.replaceState({}, document.title, window.location.pathname);
});
</script>
