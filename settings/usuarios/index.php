<?php
include '../../conexion/conexion.php';

/* =========================
   USUARIOS + ROLES
========================= */
$usuarios = $conexion->query("
    SELECT 
        u.idusuario,
        u.usuario,
        u.nombre,
        u.apellido,
        u.dni,
        u.celular,
        GROUP_CONCAT(r.idroles) AS roles_ids,
        GROUP_CONCAT(r.nombre_rol SEPARATOR ', ') AS roles_nombres
    FROM usuario u
    LEFT JOIN usuario_roles ur ON ur.usuario_id = u.idusuario
    LEFT JOIN roles r ON r.idroles = ur.rol_id
    GROUP BY u.idusuario
    ORDER BY u.idusuario DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   ROLES DISPONIBLES
========================= */
$roles = $conexion->query("
    SELECT idroles, nombre_rol
    FROM roles
    WHERE estado = 1
")->fetchAll(PDO::FETCH_ASSOC);

include '../../dashboard/nav.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../estilos_settings.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">👤 Usuarios del sistema</h2>
            <button class="btn btn-outline-warning btn-sm" onclick="history.back()">⬅ Volver</button>
        </div>

        <button class="btn btn-warning fw-bold" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
            ➕ Nuevo usuario
        </button>
    </div>

    <!-- TABLA -->
    <div class="card bg-dark text-white border-secondary shadow-lg">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead class="table-secondary text-dark">
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Roles</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= $u['idusuario'] ?></td>

                        <td class="fw-bold"><?= htmlspecialchars($u['usuario']) ?></td>

                        <td><?= htmlspecialchars($u['nombre'].' '.$u['apellido']) ?></td>

                        <td>
                            <small>
                                DNI: <?= $u['dni'] ?: '-' ?><br>
                                Cel: <?= $u['celular'] ?: '-' ?>
                            </small>
                        </td>

                        <td>
                            <?php if ($u['roles_nombres']): ?>
                                <?php foreach (explode(',', $u['roles_nombres']) as $rol): ?>
                                    <span class="badge bg-info text-dark me-1 mb-1"><?= trim($rol) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="badge bg-secondary">Sin rol</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button
                                    class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalRol"
                                    onclick='asignarRol(<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                    🛡️ Roles
                                </button>

                                <button
                                    class="btn btn-danger"
                                    onclick="eliminarUsuario(<?= $u['idusuario'] ?>)">
                                    🗑️
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

<!-- =========================
     MODAL CREAR USUARIO
========================= -->
<div class="modal fade" id="modalCrearUsuario">
    <div class="modal-dialog modal-dialog-centered">
        <form action="usuarios_controller.php" method="POST" class="modal-content bg-dark text-white">

            <input type="hidden" name="accion" value="crear_usuario">

            <div class="modal-header border-0">
                <h5 class="fw-bold">➕ Crear usuario</h5>
            </div>

            <div class="modal-body">
                <label class="form-label">Usuario</label>
                <input class="form-control" name="usuario" required>

                <label class="form-label mt-2">Contraseña</label>
                <input type="password" class="form-control" name="pass" required>

                <hr class="border-secondary">

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input class="form-control" name="nombre">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellido</label>
                        <input class="form-control" name="apellido">
                    </div>
                </div>

                <div class="row g-2 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">DNI</label>
                        <input class="form-control" name="dni">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Celular</label>
                        <input class="form-control" name="celular">
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-warning w-100 fw-bold">
                    Crear cuenta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- =========================
     MODAL ROLES (CHIPS)
========================= -->
<div class="modal fade" id="modalRol">
    <div class="modal-dialog modal-dialog-centered">
        <form action="usuarios_controller.php" method="POST" class="modal-content bg-dark text-white">

            <input type="hidden" name="accion" value="asignar_roles">
            <input type="hidden" name="usuario_id" id="rol_usuario_id">
            <input type="hidden" name="roles_json" id="roles_json">

            <div class="modal-header border-0">
                <h5 class="fw-bold">🛡️ Gestionar roles</h5>
            </div>

            <div class="modal-body">

                <label class="form-label">Agregar rol</label>
<select class="form-select" id="rol_select_simple" onchange="agregarRolAuto()">
    <option value="">Seleccionar rol…</option>
    <?php foreach ($roles as $r): ?>
        <option value="<?= $r['idroles'] ?>">
            <?= $r['nombre_rol'] ?>
        </option>
    <?php endforeach; ?>
</select>


                <div class="mt-3">
                    <label class="form-label">Roles asignados</label>
                    <div id="roles_container" class="d-flex flex-wrap gap-2"></div>
                </div>

            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-warning w-100 fw-bold">
                    Guardar roles
                </button>
            </div>
        </form>
    </div>
</div>

<!-- FORM ELIMINAR -->
<form id="formEliminarUsuario" action="usuarios_controller.php" method="POST" class="d-none">
    <input type="hidden" name="accion" value="eliminar_usuario">
    <input type="hidden" name="usuario_id" id="delete_usuario_id">
</form>

<script>
/* =========================================================
   ROLES (modal)
========================================================= */

let rolesAsignados = [];

/* Abrir modal y cargar roles actuales */
function asignarRol(u){
    document.getElementById('rol_usuario_id').value = u.idusuario;

    rolesAsignados = u.roles_ids
        ? u.roles_ids.split(',').map(r => r.trim())
        : [];

    renderRoles();
}

/* Agregar rol automáticamente al seleccionar */
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('rol_select_simple');

    if (!select) return;

    select.addEventListener('change', () => {
        const rolId = select.value;

        if (!rolId || rolesAsignados.includes(rolId)) {
            select.value = '';
            return;
        }

        rolesAsignados.push(rolId);
        select.value = '';
        renderRoles();
    });
});

/* Quitar rol */
function quitarRol(rolId){
    rolesAsignados = rolesAsignados.filter(r => r !== rolId);
    renderRoles();
}

/* Render badges */
function renderRoles(){
    const cont = document.getElementById('roles_container');
    cont.innerHTML = '';

    rolesAsignados.forEach(id => {
        const option = document.querySelector(
            `#rol_select_simple option[value="${id}"]`
        );
        const nombre = option ? option.textContent : id;

        const badge = document.createElement('span');
        badge.className =
            'badge bg-info text-dark d-flex align-items-center gap-2 px-3 py-2';

        badge.innerHTML = `
            ${nombre}
            <button type="button"
                class="btn-close btn-close-dark btn-sm"
                onclick="quitarRol('${id}')">
            </button>
        `;

        cont.appendChild(badge);
    });

    document.getElementById('roles_json').value =
        JSON.stringify(rolesAsignados);
}

/* =========================================================
   ELIMINAR USUARIO (SweetAlert confirm)
========================================================= */

function eliminarUsuario(id){
    Swal.fire({
        title: '¿Eliminar usuario?',
        text: 'Se eliminará el usuario y todos sus roles',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#444',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('delete_usuario_id').value = id;
            document.getElementById('formEliminarUsuario').submit();
        }
    });
}

/* =========================================================
   SWEET ALERT AUTOMÁTICO POR MSG
========================================================= */

document.addEventListener('DOMContentLoaded', () => {

    const params = new URLSearchParams(window.location.search);
    const msg = params.get('msg');

    if (!msg) return;

    /* USUARIO */
    if (msg === 'usuario_creado') {
        Swal.fire({
            icon: 'success',
            title: 'Usuario creado',
            text: 'La cuenta fue creada correctamente',
            timer: 1800,
            showConfirmButton: false
        });
    }

    if (msg === 'usuario_eliminado') {
        Swal.fire({
            icon: 'warning',
            title: 'Usuario eliminado',
            text: 'El usuario y sus roles fueron eliminados',
            timer: 1800,
            showConfirmButton: false
        });
    }

    /* ROLES */
    if (msg === 'roles_actualizados') {
        Swal.fire({
            icon: 'success',
            title: 'Roles guardados',
            text: 'Los roles se actualizaron correctamente',
            timer: 1800,
            showConfirmButton: false
        });
    }

    /* ERROR */
    if (msg === 'error') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un problema inesperado'
        });
    }

    /* Limpia la URL (opcional pero pro) */
    window.history.replaceState({}, document.title, window.location.pathname);
});
</script>




<?php include '../../dashboard/footer.php'; ?>
