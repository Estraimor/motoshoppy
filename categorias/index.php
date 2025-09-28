<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';

// Traer categorías
$stmt = $conexion->query("SELECT * FROM categoria ORDER BY idCategoria DESC");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<head>
    <link rel="stylesheet" href="estilos_categorias.css">
</head>

<div class="content-header d-flex justify-content-between align-items-center">
    <h2><i class="fa-solid fa-tags"></i> Categorías</h2>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregar">
        <i class="fa-solid fa-plus"></i> Nueva Categoría
    </button>
</div>

<div class="content-body mt-4">
    <div class="table-responsive">
        <table id="tablaCategorias" class="table table-dark table-striped table-hover align-middle shadow-sm rounded">
            <thead class="table-primary">
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
                        <td><?= htmlspecialchars($cat['nombre_categoria']) ?></td>
                        <td><?= htmlspecialchars($cat['descripcion']) ?></td>
                        <td>
                            <?php if ($cat['estado']): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEditar"
                                    data-id="<?= $cat['idCategoria'] ?>"
                                    data-nombre="<?= htmlspecialchars($cat['nombre_categoria']) ?>"
                                    data-descripcion="<?= htmlspecialchars($cat['descripcion']) ?>"
                                    data-estado="<?= $cat['estado'] ?>">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <a href="eliminar.php?id=<?= $cat['idCategoria'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Seguro que deseas eliminar esta categoría?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL AGREGAR -->
<div class="modal fade" id="modalAgregar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <form action="crear.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-solid fa-plus"></i> Nueva Categoría</h5>
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
            <select name="estado"class="form-select">
                <option value="">Seleccione Estado</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <form action="editar.php" method="POST">
        <input type="hidden" name="idCategoria" id="edit-id">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-solid fa-pen"></i> Editar Categoría</h5>
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
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Pasar datos al modal de edición
document.getElementById('modalEditar').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    document.getElementById('edit-id').value = button.getAttribute('data-id');
    document.getElementById('edit-nombre').value = button.getAttribute('data-nombre');
    document.getElementById('edit-descripcion').value = button.getAttribute('data-descripcion');
    document.getElementById('edit-estado').value = button.getAttribute('data-estado');
});
</script>

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
            { orderable: false, targets: 4 } // desactiva orden en columna Acciones
        ]
    });
});
</script>


<?php include '../dashboard/footer.php'; ?>
