<?php
include '../dashboard/nav.php';
requerirRol('Administrador', 'Reponedor');
require_once '../conexion/conexion.php';

// Traer categorías
$stmt = $conexion->query("SELECT * FROM categoria ORDER BY idCategoria DESC");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<head>
    <link rel="stylesheet" href="estilos_categorias.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
</head>

<?php
$totalCategorias = count($categorias);
$activasCategorias = 0;
foreach ($categorias as $cat) { if ($cat['estado']) $activasCategorias++; }
$inactivasCategorias = $totalCategorias - $activasCategorias;
?>

<div class="cat-header">

    <div class="content-header d-flex justify-content-between align-items-center">
        <h2><i class="fa-solid fa-tags"></i> Categorías</h2>

        <button class="btn btn-success fw-semibold" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            <i class="fa-solid fa-plus"></i> Nueva Categoría
        </button>
    </div>

    <div class="d-flex gap-3 flex-wrap my-3">
        <div class="stat-mini">
            <i class="fa-solid fa-tags fa-lg text-info"></i>
            <div>
                <div class="num"><?= $totalCategorias ?></div>
                <div class="lbl">Total</div>
            </div>
        </div>
        <div class="stat-mini">
            <i class="fa-solid fa-circle-check fa-lg text-success"></i>
            <div>
                <div class="num" style="color:#22c55e"><?= $activasCategorias ?></div>
                <div class="lbl">Activas</div>
            </div>
        </div>
        <div class="stat-mini">
            <i class="fa-solid fa-circle-xmark fa-lg text-danger"></i>
            <div>
                <div class="num" style="color:#f87171"><?= $inactivasCategorias ?></div>
                <div class="lbl">Inactivas</div>
            </div>
        </div>
    </div>

</div>

<div class="content-body">
    <div class="table-cat">
    <div class="table-responsive">

        <table id="tablaCategorias" class="table table-dark align-middle mb-0">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($categorias as $cat): ?>
                    <tr>

                        <td><?= $cat['idCategoria'] ?></td>

                        <td><?= htmlspecialchars($cat['nombre_categoria'] ?? '') ?></td>

                        <td><?= htmlspecialchars($cat['descripcion'] ?? '') ?></td>

                        <td>
                            <?php if ($cat['estado']): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-center">

                            <!-- BOTON EDITAR -->
                            <button
                                class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditar"
                                data-id="<?= $cat['idCategoria'] ?>"
                                data-nombre="<?= htmlspecialchars($cat['nombre_categoria'] ?? '') ?>"
                                data-descripcion="<?= htmlspecialchars($cat['descripcion'] ?? '') ?>"
                                data-estado="<?= $cat['estado'] ?>">

                                <i class="fa-solid fa-pen"></i>

                            </button>

                            <!-- BOTON ELIMINAR -->
                            <a
                                href="eliminar.php?id=<?= $cat['idCategoria'] ?>"
                                class="btn btn-sm btn-danger btn-eliminar">

                                <i class="fa-solid fa-trash"></i>

                            </a>

                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    </div>
    </div>
</div>


<!-- =========================
MODAL AGREGAR
========================= -->
<div class="modal fade" id="modalAgregar" tabindex="-1">
    <div class="modal-dialog">

        <div class="modal-content bg-dark text-white">

            <form action="crear.php" method="POST">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-plus"></i> Nueva Categoría
                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre_categoria" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado</label>

                        <select name="estado" class="form-select">
                            <option value="">Seleccione Estado</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-success">
                        Guardar
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>


<!-- =========================
MODAL EDITAR
========================= -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">

        <div class="modal-content bg-dark text-white">

            <form action="editar.php" method="POST">

                <input type="hidden" name="idCategoria" id="edit-id">

                <div class="modal-header">

                    <h5 class="modal-title">
                        <i class="fa-solid fa-pen"></i> Editar Categoría
                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre_categoria" id="edit-nombre" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" id="edit-descripcion" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado</label>

                        <select name="estado" id="edit-estado" class="form-select">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-warning">
                        Actualizar
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>


<!-- =========================
CARGAR DATOS EN MODAL EDITAR
========================= -->
<script>

document.getElementById('modalEditar').addEventListener('show.bs.modal', function (event) {

    const button = event.relatedTarget;

    document.getElementById('edit-id').value = button.getAttribute('data-id');
    document.getElementById('edit-nombre').value = button.getAttribute('data-nombre');
    document.getElementById('edit-descripcion').value = button.getAttribute('data-descripcion');
    document.getElementById('edit-estado').value = button.getAttribute('data-estado');

});

</script>


<!-- =========================
DATATABLE
========================= -->
<script>

$(document).ready(function () {

    $('#tablaCategorias').DataTable({

        responsive: true,
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],

        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },

        columnDefs: [
            { orderable: false, targets: 4 }
        ],

        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa-solid fa-file-excel me-1"></i>Excel',
                className: 'btn btn-success btn-sm',
                filename: 'Categorias_Motoshoppy',
                title: 'Motoshoppy — Categorías',
                exportOptions: { columns: [0, 1, 2, 3] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa-solid fa-file-pdf me-1"></i>PDF',
                className: 'btn btn-danger btn-sm',
                filename: 'Categorias_Motoshoppy',
                title: 'Motoshoppy — Listado de Categorías',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0, 1, 2, 3] },
                customize: function(doc) {
                    doc.content[0].fontSize  = 16;
                    doc.content[0].bold      = true;
                    doc.content[0].color     = '#1e3a5f';
                    doc.content[0].alignment = 'center';
                    doc.content[0].margin    = [0, 0, 0, 4];
                    doc.content.splice(1, 0, {
                        text: 'Generado: ' + new Date().toLocaleDateString('es-AR'),
                        alignment: 'right', fontSize: 8,
                        color: '#666666', margin: [0, 0, 0, 10]
                    });
                    doc.styles.tableHeader = {
                        bold: true, fontSize: 9,
                        color: '#ffffff', fillColor: '#1e3a5f',
                        alignment: 'center'
                    };
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
                    doc.content[2].table.widths =
                        Array(doc.content[2].table.body[0].length).fill('*');
                }
            }
        ]

    });

});

</script>


<!-- =========================
ALERTAS CREAR / EDITAR / ELIMINAR
========================= -->
<script>

const params = new URLSearchParams(window.location.search);
const msg = params.get("msg");

if (msg === "creado") {
    Swal.fire({
        icon: 'success',
        title: 'Categoría creada',
        text: 'La categoría se guardó correctamente'
    });
}

if (msg === "editado") {
    Swal.fire({
        icon: 'success',
        title: 'Categoría actualizada',
        text: 'Los cambios se guardaron correctamente'
    });
}

if (msg === "eliminado") {
    Swal.fire({
        icon: 'success',
        title: 'Categoría eliminada',
        text: 'La categoría fue eliminada'
    });
}

</script>


<!-- =========================
CONFIRMAR ELIMINAR SWEETALERT
========================= -->
<script>

document.querySelectorAll('.btn-eliminar').forEach(btn => {

    btn.addEventListener('click', function (e) {

        e.preventDefault();

        const url = this.getAttribute('href');

        Swal.fire({
            title: '¿Eliminar categoría?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {

            if (result.isConfirmed) {
                window.location.href = url;
            }

        });

    });

});

</script>


<?php include '../dashboard/footer.php'; ?>