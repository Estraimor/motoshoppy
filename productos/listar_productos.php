<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';

// Traer productos
$stmt = $conexion->query("
    SELECT p.idproducto, p.codigo, p.nombre, p.modelo, p.precio_expuesto, 
           c.nombre_categoria, m.nombre_marca, p.descripcion, 
           p.peso_ml, p.peso_g, p.imagen,
           u.lugar, u.estante
    FROM producto p
    LEFT JOIN categoria c ON p.Categoria_idCategoria = c.idCategoria
    LEFT JOIN marcas m ON p.marcas_idmarcas = m.idmarcas
    LEFT JOIN ubicacion_producto u ON p.ubicacion_producto_idubicacion_producto = u.idubicacion_producto
    ORDER BY p.idproducto DESC
");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<link rel="stylesheet" href="estilos_productos.css">

<div class="content-header d-flex justify-content-between align-items-center">
    <h2><i class="fa-solid fa-boxes-stacked"></i> Listado de Productos</h2>

    <!-- 🔧 Botón único Settings General -->
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalSettings">
        <i class="fa-solid fa-gear"></i> Settings General
    </button>
</div>

<div class="content-body">
    <div class="card shadow-sm p-3">
        <div class="table-responsive">
            <table id="tablaProductos" class="table table-dark table-striped align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['codigo']) ?></td>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td><?= htmlspecialchars($p['nombre_marca']) ?></td>
                            <td><?= htmlspecialchars($p['nombre_categoria']) ?></td>
                            <td>$<?= number_format($p['precio_expuesto'], 2, ',', '.') ?></td>
                            <td class="text-center">
                                <!-- Botón detalle de cada producto -->
                                <button class="btn btn-info btn-sm ver-detalle"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDetalle"
                                    data-producto='<?= json_encode($p, JSON_UNESCAPED_UNICODE) ?>'>
                                    <i class="fa-solid fa-circle-info"></i> Detalle
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-box"></i> Detalle del Producto</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="detalleContenido"></div>
      </div>
      <div class="modal-footer">
        <!-- 🔧 Nuevo botón Modificar -->
        <button id="btnEditar" class="btn btn-primary">Modificar</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Settings General -->
<div class="modal fade" id="modalSettings" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-gears"></i> Settings General</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Aquí podés configurar ajustes generales de productos (ejemplo: IVA, margen, etc).</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {
    $('#tablaProductos').DataTable({
        pageLength: 5,
        lengthChange: false,
        language: {
            search: "Buscar:",
            zeroRecords: "No se encontraron resultados",
            info: "Mostrando _START_ a _END_ de _TOTAL_ productos",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        }
    });

    // Cargar detalles en el modal
    $('.ver-detalle').on('click', function () {
        const data = $(this).data('producto');
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Código:</strong> ${data.codigo ?? ''}</p>
                    <p><strong>Nombre:</strong> ${data.nombre ?? ''}</p>
                    <p><strong>Modelo:</strong> ${data.modelo ?? ''}</p>
                    <p><strong>Marca:</strong> ${data.nombre_marca ?? ''}</p>
                    <p><strong>Categoría:</strong> ${data.nombre_categoria ?? ''}</p>
                    <p><strong>Precio Expuesto:</strong> $${parseFloat(data.precio_expuesto).toFixed(2)}</p>
                    <p><strong>Peso:</strong> ${(data.peso_ml ? data.peso_ml+' ml ' : '') + (data.peso_g ? data.peso_g+' g' : '')}</p>
                    <p><strong>Ubicación:</strong> ${(data.lugar ?? '')} ${(data.estante ?? '')}</p>
                </div>
                <div class="col-md-6 text-center">
                    <!-- 🔧 Imagen corregida con ruta ../ y fallback -->
                    <img src="${data.imagen ? '../'+data.imagen : 'https://via.placeholder.com/250x250?text=Sin+Imagen'}" 
                         alt="Imagen" class="img-fluid rounded shadow" style="max-height:250px;">
                </div>
            </div>
            <hr>
            <h6><i class="fa-solid fa-list"></i> Atributos (JSON)</h6>
            <pre class="bg-secondary p-2 rounded">${data.descripcion ? JSON.stringify(JSON.parse(data.descripcion), null, 2) : 'Sin atributos'}</pre>
        `;
        $('#detalleContenido').html(html);
    });

    // 🔧 Botón para editar detalle
    $(document).on('click', '#btnEditar', function(){
        let contenido = $('#detalleContenido');

        // Transformar <p> en inputs
        contenido.find('p').each(function(){
            let texto = $(this).text().split(':');
            if(texto.length > 1){
                let label = texto[0].trim();
                let valor = texto.slice(1).join(':').trim(); 
                $(this).html(`<strong>${label}:</strong> <input type="text" class="form-control form-control-sm" value="${valor}">`);
            }
        });

        // Reemplazar imagen por input file
        contenido.find('img').replaceWith(`
            <input type="file" class="form-control" id="nuevaImagen">
        `);

        // Cambiar botón a Guardar
        $(this).text('Guardar').removeClass('btn-primary').addClass('btn-success').attr('id','btnGuardar');
    });

    // 🔧 Guardar cambios (aquí se hace el UPDATE vía AJAX)
    $(document).on('click', '#btnGuardar', function(){
        alert('Aquí iría la lógica para guardar en la BD con PHP y subir la nueva imagen.');
    });
});
</script>

<?php include '../dashboard/footer.php'; ?>
