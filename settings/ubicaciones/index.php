<?php
include '../../dashboard/nav.php';
requerirRol('Administrador', 'Reponedor');
require_once '../../conexion/conexion.php';

$ubicaciones = $conexion->query("
    SELECT idubicacion_producto, lugar, COALESCE(estante,'') AS estante,
           (SELECT COUNT(*) FROM producto p WHERE p.ubicacion_producto_idubicacion_producto = u.idubicacion_producto) AS total_productos
    FROM ubicacion_producto u
    ORDER BY lugar, estante
")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
#ub-page { background:#0f0f0f; min-height:100vh; padding:28px 24px; }
#ub-page h2 { color:#fff; font-weight:700; font-size:22px; }
#ub-page .sub { color:#9ca3af; font-size:13px; }

#ub-page .ub-card {
    background:#1a1a1a;
    border:1px solid #2a2a2a;
    border-radius:12px;
    padding:16px 20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    transition:.15s;
}
#ub-page .ub-card:hover { border-color:#f59e0b; }

#ub-page .ub-lugar { color:#fff; font-weight:600; font-size:15px; }
#ub-page .ub-estante { color:#9ca3af; font-size:13px; margin-top:2px; }
#ub-page .ub-badge {
    background:#1f2937; color:#9ca3af;
    border-radius:20px; padding:3px 12px;
    font-size:12px; white-space:nowrap;
}

#ub-page .ub-actions { display:flex; gap:8px; }

#ub-page .btn-ub-edit {
    background:#1d4ed8; color:#fff; border:none;
    border-radius:8px; padding:6px 14px; font-size:13px; cursor:pointer;
}
#ub-page .btn-ub-edit:hover { background:#2563eb; }

#ub-page .btn-ub-del {
    background:#7f1d1d; color:#fca5a5; border:none;
    border-radius:8px; padding:6px 12px; font-size:13px; cursor:pointer;
}
#ub-page .btn-ub-del:hover { background:#dc2626; color:#fff; }

#ub-page .search-wrap input {
    background:#111; border:1px solid #333; color:#fff;
    border-radius:10px; padding:9px 14px; font-size:14px; width:100%;
    outline:none;
}
#ub-page .search-wrap input:focus { border-color:#f59e0b; }

/* Modal */
.ub-modal-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.7); z-index:9999;
    align-items:center; justify-content:center;
}
.ub-modal-overlay.open { display:flex; }
.ub-modal {
    background:#1a1a1a; border:1px solid #333; border-radius:16px;
    padding:28px; width:420px; max-width:95vw;
}
.ub-modal h5 { color:#fff; font-weight:700; margin-bottom:18px; }
.ub-modal label { color:#9ca3af; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; display:block; margin-bottom:5px; }
.ub-modal input {
    width:100%; background:#111; border:1px solid #333; color:#fff;
    border-radius:8px; padding:10px 14px; font-size:14px; outline:none;
    box-sizing:border-box;
}
.ub-modal input:focus { border-color:#f59e0b; }
.ub-modal .modal-footer { display:flex; gap:10px; margin-top:20px; justify-content:flex-end; }
.btn-guardar { background:#f59e0b; color:#111; border:none; border-radius:8px; padding:9px 20px; font-weight:700; font-size:14px; cursor:pointer; }
.btn-guardar:hover { background:#d97706; }
.btn-cancelar { background:#2a2a2a; color:#9ca3af; border:none; border-radius:8px; padding:9px 16px; font-size:14px; cursor:pointer; }
.btn-cancelar:hover { background:#333; color:#fff; }
.empty-state { color:#4b5563; text-align:center; padding:48px 0; font-size:15px; }
</style>

<div id="ub-page">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h2><i class="fa-solid fa-map-pin me-2" style="color:#f59e0b"></i>Ubicaciones</h2>
            <p class="sub">Gestioná los lugares físicos donde se guardan los productos</p>
        </div>
        <div class="d-flex gap-2">
            <a href="../index.php" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Volver
            </a>
            <button class="btn btn-warning btn-sm fw-bold" onclick="abrirCrear()">
                <i class="fa-solid fa-plus me-1"></i>Nueva ubicación
            </button>
        </div>
    </div>

    <!-- BUSCADOR -->
    <div class="search-wrap mb-4">
        <input type="text" id="buscador" placeholder="🔍  Buscar por lugar o estante..." oninput="filtrar()">
    </div>

    <!-- CONTADOR -->
    <p class="sub mb-3" id="contador"><?= count($ubicaciones) ?> ubicaciones registradas</p>

    <!-- GRID -->
    <div id="listaUbicaciones" class="d-flex flex-column gap-2">
        <?php foreach ($ubicaciones as $u): ?>
        <div class="ub-card" data-lugar="<?= strtolower(htmlspecialchars($u['lugar'])) ?>" data-estante="<?= strtolower(htmlspecialchars($u['estante'])) ?>">
            <div>
                <div class="ub-lugar">
                    <i class="fa-solid fa-location-dot me-2" style="color:#f59e0b;font-size:13px"></i>
                    <?= htmlspecialchars($u['lugar']) ?>
                    <?php if ($u['estante']): ?>
                        <span style="color:#6b7280;font-weight:400"> — Estante <?= htmlspecialchars($u['estante']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="ub-estante">
                    <span class="ub-badge"><?= $u['total_productos'] ?> producto<?= $u['total_productos'] != 1 ? 's' : '' ?></span>
                </div>
            </div>
            <div class="ub-actions">
                <button class="btn-ub-edit"
                    onclick='abrirEditar(<?= $u["idubicacion_producto"] ?>, <?= json_encode($u["lugar"]) ?>, <?= json_encode($u["estante"]) ?>)'>
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="btn-ub-del" onclick="eliminar(<?= $u["idubicacion_producto"] ?>, <?= $u["total_productos"] ?>)">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($ubicaciones)): ?>
            <div class="empty-state">No hay ubicaciones registradas aún.</div>
        <?php endif; ?>
    </div>

</div>

<!-- ===== MODAL CREAR / EDITAR ===== -->
<div class="ub-modal-overlay" id="modalUb">
    <div class="ub-modal">
        <h5 id="modalUbTitulo">Nueva ubicación</h5>

        <input type="hidden" id="ub_id">

        <div class="mb-3">
            <label>Lugar</label>
            <input type="text" id="ub_lugar" placeholder="Ej: Depósito, Exhibición, Sector A...">
        </div>
        <div>
            <label>Estante <span style="color:#6b7280;font-weight:400;text-transform:none">(opcional)</span></label>
            <input type="text" id="ub_estante" placeholder="Ej: 3, A, Sur...">
        </div>

        <div class="modal-footer">
            <button class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-guardar" onclick="guardar()">
                <i class="fa-solid fa-check me-1"></i>Guardar
            </button>
        </div>
    </div>
</div>

<!-- FORM ELIMINAR -->
<form id="formEliminar" method="POST" action="ubicaciones_controller.php" class="d-none">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" id="del_id">
</form>

<?php include '../../dashboard/footer.php'; ?>

<script>
/* ===== MODAL ===== */
function abrirCrear() {
    document.getElementById('modalUbTitulo').textContent = 'Nueva ubicación';
    document.getElementById('ub_id').value    = '';
    document.getElementById('ub_lugar').value   = '';
    document.getElementById('ub_estante').value = '';
    document.getElementById('modalUb').classList.add('open');
    document.getElementById('ub_lugar').focus();
}

function abrirEditar(id, lugar, estante) {
    document.getElementById('modalUbTitulo').textContent = 'Editar ubicación';
    document.getElementById('ub_id').value    = id;
    document.getElementById('ub_lugar').value   = lugar;
    document.getElementById('ub_estante').value = estante;
    document.getElementById('modalUb').classList.add('open');
    document.getElementById('ub_lugar').focus();
}

function cerrarModal() {
    document.getElementById('modalUb').classList.remove('open');
}

document.getElementById('modalUb').addEventListener('click', e => {
    if (e.target === e.currentTarget) cerrarModal();
});

/* ===== GUARDAR (AJAX) ===== */
async function guardar() {
    const id     = document.getElementById('ub_id').value.trim();
    const lugar  = document.getElementById('ub_lugar').value.trim();
    const estante = document.getElementById('ub_estante').value.trim();

    if (!lugar) {
        Swal.fire({ icon:'warning', title:'Falta el lugar', text:'El campo Lugar es obligatorio.', confirmButtonColor:'#f59e0b' });
        return;
    }

    const fd = new FormData();
    fd.append('accion', id ? 'editar' : 'crear');
    fd.append('id', id);
    fd.append('lugar', lugar);
    fd.append('estante', estante);

    try {
        const r = await fetch('ubicaciones_controller.php', { method:'POST', body:fd });
        const j = await r.json();

        if (!j.ok) throw new Error(j.msg || 'Error');

        cerrarModal();
        Swal.fire({
            icon:'success',
            title: id ? 'Ubicación actualizada' : 'Ubicación creada',
            timer:1300, showConfirmButton:false
        }).then(() => location.reload());

    } catch(e) {
        Swal.fire({ icon:'error', title:'Error', text: e.message, confirmButtonColor:'#f59e0b' });
    }
}

/* Guardar con Enter */
document.addEventListener('keydown', e => {
    if (e.key === 'Enter' && document.getElementById('modalUb').classList.contains('open')) guardar();
});

/* ===== ELIMINAR ===== */
function eliminar(id, totalProd) {
    const txt = totalProd > 0
        ? `Esta ubicación tiene ${totalProd} producto(s) asignado(s). Se desvincularán pero no se eliminarán.`
        : '¿Seguro que querés eliminar esta ubicación?';

    Swal.fire({
        icon:'warning', title:'¿Eliminar ubicación?', text: txt,
        showCancelButton:true,
        confirmButtonColor:'#dc2626', cancelButtonColor:'#374151',
        confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar'
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('del_id').value = id;
            document.getElementById('formEliminar').submit();
        }
    });
}

/* ===== FILTRO LOCAL ===== */
function filtrar() {
    const q = document.getElementById('buscador').value.toLowerCase();
    let visible = 0;
    document.querySelectorAll('#listaUbicaciones .ub-card').forEach(card => {
        const match = card.dataset.lugar.includes(q) || card.dataset.estante.includes(q);
        card.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('contador').textContent = visible + ' ubicaciones encontradas';
}
</script>
