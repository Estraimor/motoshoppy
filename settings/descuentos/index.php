<?php 
include '../../dashboard/nav.php';
include '../../conexion/conexion.php';
?>

<div class="container mt-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="fa-solid fa-tags me-2"></i>Gestión de Descuentos</h3>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalDescuento">
      <i class="fa-solid fa-plus me-2"></i>Nuevo descuento
    </button>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">

      <table id="tablaDescuentos" class="table table-dark table-hover align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>% Descuento</th>
            <th>Estado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
      </table>

    </div>
  </div>

</div>

<!-- MODAL -->
<div class="modal fade" id="modalDescuento" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">

      <div class="modal-header border-0">
        <h5 class="modal-title">Descuento</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="formDescuento">
        <input type="hidden" name="id" id="descuento_id">

        <div class="modal-body">

          <label class="form-label">Nombre</label>
          <input type="text" name="nombre_lista" id="nombre_lista"
                 class="form-control bg-dark text-white border-secondary mb-3" required>

          <label class="form-label">Porcentaje</label>
          <input type="number" step="0.01" name="porcentaje_descuento" id="porcentaje_descuento"
                 class="form-control bg-dark text-white border-secondary mb-3" required>

          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
            <label class="form-check-label">Activo</label>
          </div>

        </div>

        <div class="modal-footer border-0">
          <button type="submit" class="btn btn-warning w-100">
            Guardar
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

<script>
let tabla = $('#tablaDescuentos').DataTable({
  ajax: 'api/listar.php',
  columns: [
    { data: 'id' },
    { data: 'nombre_lista' },
    { 
      data: 'porcentaje_descuento',
      render: data => data + '%'
    },
    {
  data: 'activo',
  render: function(data, type, row){
    const clase = data == 1 ? 'bg-success' : 'bg-danger';
    const texto = data == 1 ? 'Activo' : 'Inactivo';

    return `
      <span class="badge ${clase} me-2">${texto}</span>
      <button class="btn btn-sm btn-outline-light"
              onclick="toggleEstado(${row.id}, ${data})">
        <i class="fa-solid fa-power-off"></i>
      </button>
    `;
  }
},
    { 
      data: null,
      className:'text-center',
      render: function(row){
        return `
          <button class="btn btn-sm btn-warning me-1" onclick="editar(${row.id})">
            <i class="fa-solid fa-pen"></i>
          </button>
          <button class="btn btn-sm btn-danger" onclick="eliminar(${row.id})">
            <i class="fa-solid fa-trash"></i>
          </button>
        `;
      }
    }
  ]
});

document.getElementById('formDescuento').addEventListener('submit', async (e) => {
  e.preventDefault();

  const formData = new FormData(e.target);

  const res = await fetch('api/guardar.php', {
    method: 'POST',
    body: formData
  });

  const r = await res.json();

  if(r.ok){
    bootstrap.Modal.getInstance(document.getElementById('modalDescuento')).hide();
    tabla.ajax.reload();
  } else {
    alert(r.msg);
  }
});

function editar(id){
  fetch('api/obtener.php?id=' + id)
    .then(res => res.json())
    .then(data => {
      document.getElementById('descuento_id').value = data.id;
      document.getElementById('nombre_lista').value = data.nombre_lista;
      document.getElementById('porcentaje_descuento').value = data.porcentaje_descuento;
      document.getElementById('activo').checked = data.activo == 1;
      new bootstrap.Modal(document.getElementById('modalDescuento')).show();
    });
}

function eliminar(id){
  if(!confirm("¿Eliminar descuento?")) return;

  fetch('api/eliminar.php?id=' + id)
    .then(res => res.json())
    .then(r => {
      if(r.ok) tabla.ajax.reload();
    });
}
</script>

<script>
    async function toggleEstado(id, estadoActual){

  const nuevoEstado = estadoActual == 1 ? 0 : 1;

  const res = await fetch('api/cambiar_estado.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, estado: nuevoEstado })
  });

  const r = await res.json();

  if(r.ok){
    tabla.ajax.reload(null,false);
  } else {
    alert(r.msg);
  }
}
</script>

<?php include '../../dashboard/footer.php'; ?>