<?php
include '../conexion/conexion.php';

$roles = $conexion->query("
    SELECT *
    FROM roles
    ORDER BY idroles DESC
")->fetchAll(PDO::FETCH_ASSOC);

include '../dashboard/nav.php';
?>




<link rel="stylesheet" href="estilos_settings.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="roles-ui container py-4">

    <h2 class="fw-bold mb-4">🛡️ Roles del sistema</h2>

    <!-- BOTON NUEVO -->
    <button class="btn btn-warning fw-bold mb-3" data-bs-toggle="modal" data-bs-target="#modalCrear">
        + Nuevo rol
    </button>

    <div class="card bg-dark text-white border-secondary shadow-lg">

        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Detalle</th>
                    <th>Estado</th>
                    <th width="200">Acciones</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($roles as $r): ?>
                <tr>
                    <td><?= $r['idroles'] ?></td>
                    <td><?= $r['nombre_rol'] ?></td>
                    <td><?= $r['detalle_rol'] ?></td>
                    <td>
                        <?php if($r['estado']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>

                        <!-- EDITAR -->
                        <button
                            class="btn btn-sm btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditar"
                            onclick="editarRol(<?= htmlspecialchars(json_encode($r)) ?>)">
                            Editar
                        </button>

                        <!-- ELIMINAR -->
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= $r['idroles'] ?>">
                            <button class="btn btn-sm btn-danger">Eliminar</button>
                        </form>

                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>


<!-- =========================
     MODAL CREAR
========================= -->

<div class="modal fade" id="modalCrear">
    <div class="modal-dialog modal-dialog-centered">
        <form action="roles_controller.php" method="POST" class="modal-content bg-dark text-white roles-form">

            <input type="hidden" name="accion" value="crear">

            <div class="modal-header border-0">
                <h5 class="fw-bold">➕ Crear nuevo rol</h5>
            </div>

            <div class="modal-body">

                <!-- NOMBRE -->
                <label class="form-label">Nombre del rol</label>
                <input class="form-control" name="nombre" required>
                <small class="form-text text-soft">
                    Ej: Administrador, Ventas, Cajero, Supervisor
                </small>

                <!-- DETALLE -->
                <label class="form-label mt-3">Descripción / Permisos</label>
                <textarea class="form-control" name="detalle" rows="3"></textarea>
                <small class="form-text text-soft">
                    Explicá qué puede hacer este rol dentro del sistema
                </small>

            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-warning w-100 btn-confirm">
                    Guardar rol
                </button>
            </div>
        </form>
    </div>
</div>


<!-- =========================
     MODAL EDITAR
========================= -->

<div class="modal fade" id="modalEditar">
    <div class="modal-dialog modal-dialog-centered">
        <form action="roles_controller.php" method="POST" class="modal-content bg-dark text-white roles-form">

            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="edit_id">

            <div class="modal-header border-0">
                <h5 class="fw-bold">✏️ Editar rol</h5>
            </div>

            <div class="modal-body">

                <label class="form-label">Nombre del rol</label>
                <input class="form-control" id="edit_nombre" name="nombre">

                <label class="form-label mt-3">Detalle</label>
                <textarea class="form-control" id="edit_detalle" name="detalle"></textarea>

                <label class="form-label mt-3">Estado</label>
                <select class="form-select" name="estado" id="edit_estado">
                    <option value="1">🟢 Activo</option>
                    <option value="0">🔴 Inactivo</option>
                </select>

            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-warning w-100 btn-confirm">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>



<script>
function editarRol(r){
    document.getElementById('edit_id').value = r.idroles
    document.getElementById('edit_nombre').value = r.nombre_rol
    document.getElementById('edit_detalle').value = r.detalle_rol
    document.getElementById('edit_estado').value = r.estado
}
</script>

<?php include '../dashboard/footer.php'; ?>