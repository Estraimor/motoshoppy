<?php
include '../../dashboard/nav.php';
require_once '../../conexion/conexion.php';
require_once '../auditoria_helper.php';

/* =========================
   OBTENER LOGS DE AUDITORÍA
========================= */
$logs = $conexion->query("
    SELECT 
        a.*,

        /* quién hizo */
        u.nombre   AS actor_nombre,
        u.apellido AS actor_apellido,
        u.usuario  AS actor_usuario,

        /* a quién afectó (usuario) */
        ua.nombre   AS afectado_nombre,
        ua.apellido AS afectado_apellido

    FROM auditoria a

    LEFT JOIN usuario u 
        ON u.idusuario = a.usuario_id

    LEFT JOIN usuario ua
        ON ua.idusuario = a.afectado_id

    ORDER BY a.fecha DESC
    LIMIT 500
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   TRADUCCIONES HUMANAS
========================= */
$acciones = [
    'LOGIN'  => 'Inicio de sesión',
    'INSERT' => 'Creación',
    'UPDATE' => 'Modificación',
    'DELETE' => 'Eliminación'
];

$modulos = [
    'auth'     => 'Seguridad',
    'usuarios' => 'Usuarios'
];

$tablas = [
    'usuario'       => 'Usuario',
    'usuario_roles' => 'Permisos de usuario'
];
?>

<link rel="stylesheet" href="auditoria.css">

<div class="audit-wrapper">
    <div class="container py-4">

        <h2 class="fw-bold mb-1">📜 Auditoría del sistema</h2>
        <p class="text-secondary small mb-4">
            Registro de acciones importantes realizadas dentro del sistema.
        </p>

        <button
            class="btn btn-outline-warning btn-sm mb-3"
            onclick="history.back()">
            ⬅ Volver
        </button>

        <div class="card shadow-lg">
            <div class="table-responsive">
                <table
                    id="tablaAuditoria"
                    class="table table-dark table-hover align-middle mb-0">

                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Qué hizo</th>
                            <th>Área</th>
                            <th>Elemento</th>
                            <th>Descripción</th>
                            <th width="140">Detalle</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($logs as $l): ?>

                        <?php
                        // 🔎 Auditoría mapeada
                        $l['cambios_mapeados'] = interpretarAuditoria(
                            $l['tabla_afectada'],
                            $l['datos_antes'],
                            $l['datos_despues']
                        );
                        ?>

                        <tr>

                            <!-- FECHA -->
                            <td class="audit-fecha">
                                <div><?= date('d/m/Y', strtotime($l['fecha'])) ?></div>
                                <small><?= date('H:i', strtotime($l['fecha'])) ?></small>
                            </td>

                            <!-- QUIÉN HIZO -->
                            <td class="audit-usuario">
                                <div class="nombre">
                                    <?= $l['actor_nombre']
                                        ? $l['actor_nombre'] . ' ' . $l['actor_apellido']
                                        : 'Sistema' ?>
                                </div>
                                <small class="username">
                                    <?= $l['actor_usuario'] ?? '-' ?>
                                </small>
                            </td>

                            <!-- ACCIÓN -->
                            <td>
                                <?php
                                    $badge = 'secondary';
                                    if ($l['accion'] === 'INSERT') $badge = 'success';
                                    if ($l['accion'] === 'UPDATE') $badge = 'warning';
                                    if ($l['accion'] === 'DELETE') $badge = 'danger';
                                    if ($l['accion'] === 'LOGIN')  $badge = 'info';
                                ?>
                                <span class="badge bg-<?= $badge ?>">
                                    <?= $acciones[$l['accion']] ?? $l['accion'] ?>
                                </span>
                            </td>

                            <!-- MÓDULO -->
                            <td>
                                <?= $modulos[$l['modulo']] ?? ucfirst($l['modulo']) ?>
                            </td>

                            <!-- ELEMENTO -->
                            <td>
                                <?= $tablas[$l['tabla_afectada']] ?? $l['tabla_afectada'] ?>
                            </td>

                            <!-- DESCRIPCIÓN + AFECTADO -->
                            <td>
                                <?= $l['descripcion'] ?>

                                <?php if (!empty($l['afectado_id'])): ?>
                                    <div class="text-muted small mt-1">
                                        Afectó a:
                                        <strong>
                                            <?= $l['afectado_nombre']
                                                ? $l['afectado_nombre'] . ' ' . $l['afectado_apellido']
                                                : 'ID ' . $l['afectado_id'] ?>
                                        </strong>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- DETALLE -->
                            <td>
                                <button
                                    class="btn btn-sm btn-outline-info"
                                    onclick='verDetalle(<?= json_encode($l, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                    🔍 Ver detalles
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

<!-- =========================
     MODAL DETALLE AUDITORÍA
========================= -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-dark text-white">

            <div class="modal-header border-0">
                <h5 class="fw-bold">📄 Detalle de la acción</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <p class="mb-2">
                    <strong>Qué pasó:</strong>
                    <span id="detalle_desc"></span>
                </p>

                <div id="detalle_afectado" style="display:none;"></div>


                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-warning mb-2">🟡 Cómo estaba antes</h6>
                        <pre id="detalle_antes">—</pre>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-success mb-2">🟢 Cómo quedó después</h6>
                        <pre id="detalle_despues">—</pre>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function safeParse(json) {
    try {
        return JSON.parse(json);
    } catch {
        return null;
    }
}

function verDetalle(log) {

    const accion   = log.accion;
    const antes    = log.datos_antes ? safeParse(log.datos_antes) : null;
    const despues  = log.datos_despues ? safeParse(log.datos_despues) : null;
    const mapeados = log.cambios_mapeados || [];

    /* =========================
       DESCRIPCIÓN
    ========================= */
    document.getElementById('detalle_desc').textContent =
        log.descripcion || 'Sin descripción';

    /* =========================
       AFECTADO (A QUIÉN)
       👉 SOLO SI EXISTE
    ========================= */
    const afectado = document.getElementById('detalle_afectado');

    if (log.afectado_id && log.afectado_id !== '0') {

        let nombre = '';

        if (log.afectado_nombre) {
            nombre = log.afectado_nombre +
                (log.afectado_apellido ? ' ' + log.afectado_apellido : '');
        } else {
            nombre = 'ID ' + log.afectado_id;
        }

        afectado.style.display = 'block';
        afectado.innerHTML = `
    <div class="afectado-box">
        <div class="afectado-label">👤 Usuario afectado</div>
        <div class="afectado-nombre">${nombre}</div>
    </div>
`;

    } else {
        afectado.style.display = 'none';
        afectado.innerHTML = '';
    }

    /* =========================
       ANTES / DESPUÉS
    ========================= */
    let txtAntes = '—';
    let txtDesp  = '—';

    /* 🔎 AUDITORÍA MAPEADA */
    if (mapeados.length) {

        let a = '';
        let d = '';

        mapeados.forEach(c => {
            a += `• ${c.campo}\n  ${c.antes}\n\n`;
            d += `• ${c.campo}\n  ${c.despues}\n\n`;
        });

        txtAntes = a;
        txtDesp  = d;
    }
    /* 🔁 FALLBACK */
    else {

        if (accion === 'INSERT') {
            txtAntes = '— No existía —';
            txtDesp  = despues
                ? JSON.stringify(despues, null, 2)
                : 'Información creada';
        }

        if (accion === 'DELETE') {
            txtAntes = antes
                ? JSON.stringify(antes, null, 2)
                : 'Información previa';
            txtDesp  = '— Eliminado —';
        }

        if (accion === 'UPDATE') {
            txtAntes = antes
                ? JSON.stringify(antes, null, 2)
                : 'Sin datos previos';
            txtDesp  = despues
                ? JSON.stringify(despues, null, 2)
                : '—';
        }
    }

    document.getElementById('detalle_antes').textContent   = txtAntes;
    document.getElementById('detalle_despues').textContent = txtDesp;

    new bootstrap.Modal(
        document.getElementById('modalDetalle')
    ).show();
}
</script>


<script>
$(function () {
    $('#tablaAuditoria').DataTable({
        pageLength: 5,
        lengthMenu: [[5,10,20,25],[5,10,20,25]],
        order: [[0,'desc']],
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
        },
        columnDefs: [
            { orderable: false, targets: [6] }
        ]
    });
});
</script>

<?php include '../../dashboard/footer.php'; ?>
                                    