<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';

// Traer marcas con join para mostrar el nombre de la categoría y el estado
$stmt = $conexion->query("
    SELECT m.idmarcas, m.nombre_marca, m.categoria_idCategoria, m.estado, 
           c.nombre_categoria
    FROM marcas m
    LEFT JOIN categoria c ON m.categoria_idCategoria = c.idCategoria
    ORDER BY m.idmarcas DESC
");
$marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="estilos_marcas.css">

<div class="content-header d-flex justify-content-between align-items-center">
    <h2><i class="fa-solid fa-bookmark"></i> Marcas</h2>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregar">
        <i class="fa-solid fa-plus"></i> Nueva Marca
    </button>
</div>

<div class="content-body mt-4">
    <div class="table-responsive">
        <table id="tablaMarcas" class="table table-dark table-striped table-hover align-middle shadow-sm rounded">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($marcas as $marca): ?>
                    <tr>
                        <td><?= $marca['idmarcas'] ?></td>
                        <td><?= htmlspecialchars($marca['nombre_categoria'] ?? 'Sin categoría') ?></td>
                        <td><?= htmlspecialchars($marca['nombre_marca']) ?></td>
                        
                        <!-- Estado con badge -->
                        <td>
                            <?php if ($marca['estado'] == 1): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>

                        <!-- Botones de acción -->
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEditar"
                                    data-id="<?= $marca['idmarcas'] ?>"
                                    data-nombre="<?= htmlspecialchars($marca['nombre_marca']) ?>"
                                    data-categoria-id="<?= $marca['categoria_idCategoria'] ?>"
                                    data-categoria-nombre="<?= htmlspecialchars($marca['nombre_categoria']) ?>"
                                    data-estado="<?= $marca['estado'] ?>">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <a href="eliminar.php?id=<?= $marca['idmarcas'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Seguro que deseas eliminar esta marca?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ====== MODAL AGREGAR ====== -->
<div class="modal fade" id="modalAgregar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <form action="crear.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-solid fa-plus"></i> Nueva Marca</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- Categoría con buscador -->
          <div class="mb-3 position-relative">
            <label class="form-label">Categoría</label>
            <input type="text" id="buscarCategoria" class="form-control" placeholder="Escriba para buscar..." autocomplete="off">
            <input type="hidden" name="categoria_idCategoria" id="categoriaSeleccionada">
            <div id="listaCategorias" class="list-group position-absolute w-100 mt-1 shadow"></div>
          </div>

          <!-- Nombre de la Marca -->
          <div class="mb-3">
            <label class="form-label">Nombre de la Marca</label>
            <input type="text" name="nombre_marca" class="form-control" required>
          </div>

          <!-- Estado -->
          <div class="mb-3">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select" required>
                <option value="" selected>Elija estado</option>
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

<!-- ====== MODAL EDITAR ====== -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <form action="editar.php" method="POST">
        <input type="hidden" name="idmarcas" id="edit-id">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-solid fa-pen"></i> Editar Marca</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- Categoría con buscador -->
          <div class="mb-3 position-relative">
            <label class="form-label">Categoría</label>
            <input type="text" id="buscarCategoriaEdit" class="form-control" placeholder="Escriba para buscar..." autocomplete="off">
            <input type="hidden" name="categoria_idCategoria" id="categoriaSeleccionadaEdit">
            <div id="listaCategoriasEdit" class="list-group position-absolute w-100 mt-1 shadow"></div>
          </div>

          <!-- Nombre de la Marca -->
          <div class="mb-3">
            <label class="form-label">Nombre de la Marca</label>
            <input type="text" name="nombre_marca" id="edit-nombre" class="form-control" required>
          </div>

          <!-- Estado -->
          <div class="mb-3">
            <label class="form-label">Estado</label>
            <select name="estado" id="edit-estado" class="form-select" required>
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
// Configurador para búsqueda en tiempo real de categorías
function configurarBuscador(inputId, listaId, hiddenId) {
    const input = document.getElementById(inputId);
    const lista = document.getElementById(listaId);
    const hidden = document.getElementById(hiddenId);

    input.addEventListener("keyup", function() {
        const query = this.value.trim();
        if (query.length < 2) {
            lista.innerHTML = "";
            return;
        }
        fetch("buscar_categoria.php?term=" + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                lista.innerHTML = "";
                if (data.length > 0) {
                    data.forEach(cat => {
                        const item = document.createElement("button");
                        item.type = "button";
                        item.classList.add("list-group-item", "list-group-item-action");
                        item.textContent = cat.nombre_categoria;
                        item.onclick = () => {
                            input.value = cat.nombre_categoria;
                            hidden.value = cat.idCategoria;
                            lista.innerHTML = "";
                        };
                        lista.appendChild(item);
                    });
                } else {
                    lista.innerHTML = `<div class="list-group-item disabled">No se encontraron resultados</div>`;
                }
            });
    });

    document.addEventListener("click", function(e) {
        if (!input.contains(e.target) && !lista.contains(e.target)) {
            lista.innerHTML = "";
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    configurarBuscador("buscarCategoria", "listaCategorias", "categoriaSeleccionada");
    configurarBuscador("buscarCategoriaEdit", "listaCategoriasEdit", "categoriaSeleccionadaEdit");
});

document.getElementById('modalEditar').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    document.getElementById('edit-id').value = button.getAttribute('data-id');
    document.getElementById('edit-nombre').value = button.getAttribute('data-nombre');
    document.getElementById('buscarCategoriaEdit').value = button.getAttribute('data-categoria-nombre');
    document.getElementById('categoriaSeleccionadaEdit').value = button.getAttribute('data-categoria-id');
    document.getElementById('edit-estado').value = button.getAttribute('data-estado');
});

// Inicializar DataTable
$(document).ready(function () {
    $('#tablaMarcas').DataTable({
        responsive: true,
        pageLength: 5,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        columnDefs: [
            { orderable: false, targets: [3, 4] }
        ]
    });
});
</script>

<?php include '../dashboard/footer.php'; ?>
