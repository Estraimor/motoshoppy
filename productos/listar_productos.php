<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';

// Traer productos
$stmt = $conexion->query("
    SELECT 
        p.idproducto, 
        p.codigo, 
        p.nombre, 
        p.modelo, 
        p.precio_expuesto,
        p.precio_costo,      -- si querés permitir verlo
        c.nombre_categoria,

        m.idmarcas AS idmarca,
        m.nombre_marca,

        p.descripcion, 
        p.peso_ml, 
        p.peso_g, 
        p.imagen,

        u.lugar, 
        u.estante,

        -- 🔥 STOCK
        sp.idstock_producto,
        sp.stock_minimo,
        sp.cantidad_actual,
        sp.cantidad_exhibida

    FROM producto p
    LEFT JOIN categoria c 
        ON p.Categoria_idCategoria = c.idCategoria
    LEFT JOIN marcas m 
        ON p.marcas_idmarcas = m.idmarcas
    LEFT JOIN ubicacion_producto u 
        ON p.ubicacion_producto_idubicacion_producto = u.idubicacion_producto

    -- 🔥 JOIN REAL QUE FALTABA
    LEFT JOIN stock_producto sp 
        ON sp.producto_idProducto = p.idproducto

    ORDER BY p.idproducto DESC
");

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
/* Tema oscuro para que combine */
.select2-container .select2-selection {
    background-color: #181818 !important;
    color: #fff !important;
    border: 1px solid #666 !important;
}
.select2-dropdown {
    background-color: #1f1f1f !important;
    color: white !important;
}
.select2-search__field {
    background-color: #111 !important;
    color: white !important;
}
</style>


<link rel="stylesheet" href="estilos_productos.css">

<div class="content-header d-flex justify-content-between align-items-center">
    <h2><i class="fa-solid fa-boxes-stacked"></i> Listado de Productos</h2>

    <!-- 🔧 Botón único Settings General -->
    <button id="btnSettings" class="btn btn-warning">
  <i class="fa-solid fa-sliders"></i> Filtros y Settings
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
                            <td><?= htmlspecialchars($p['codigo'] ?? '') ?></td>
<td><?= htmlspecialchars($p['nombre'] ?? '') ?></td>
<td><?= htmlspecialchars($p['nombre_marca'] ?? '') ?></td>
<td><?= htmlspecialchars($p['nombre_categoria'] ?? '') ?></td>

                            <td>$<?= number_format($p['precio_expuesto'], 2, ',', '.') ?></td>
                            <td class="text-center">
                                <!-- Botón detalle de cada producto -->
                                <button class="btn btn-info btn-sm ver-detalle"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDetalle"
                                    data-producto='<?= json_encode($p, JSON_UNESCAPED_UNICODE) ?>'>
                                    <i class="fa-solid fa-circle-info"></i> Detalle
                                </button>
                                <button class="btn btn-danger btn-sm borrar-producto" data-id="<?= $p['idproducto'] ?>">
                                    <i class="fa-solid fa-trash"></i> Borrar
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
      <div class="modal-header border-secondary">
        <h5 class="modal-title text-warning">
          <i class="fa-solid fa-box-open"></i> Detalle del Producto
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="detalleContenido" class="d-flex flex-wrap gap-4 align-items-start"></div>
        <hr class="border-secondary">
        <h6 class="text-warning mb-3"><i class="fa-solid fa-list"></i> Atributos adicionales</h6>
        <div id="bloqueAtributos" class="d-flex flex-wrap gap-2"></div>
      </div>
      <div class="modal-footer border-secondary">
        <button id="btnEditar" class="btn btn-primary">Modificar</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Zoom Imagen -->
<div class="modal fade" id="modalZoom" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark border-0">
      <img id="zoomImagen" src="" class="img-fluid rounded" alt="Zoom Imagen">
    </div>
  </div>
</div>


<div class="offcanvas offcanvas-end bg-dark text-light" tabindex="-1" id="panelSettings">
  <div class="offcanvas-header border-bottom border-secondary">
    <h5 class="offcanvas-title"><i class="fa-solid fa-sliders"></i> Filtros y Configuración</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>

  <div class="offcanvas-body">
    <!-- 🔍 Buscar -->
    <div class="mb-3">
      <label class="form-label text-warning"><i class="fa-solid fa-magnifying-glass"></i> Buscar por nombre o código</label>
      <input type="text" id="filtroBusqueda" class="form-control bg-dark text-light border-secondary" placeholder="Ej: Motul, 10W40...">
    </div>

    <!-- 🏷️ Marca -->
    <div class="mb-3">
      <label class="form-label text-warning"><i class="fa-solid fa-tags"></i> Marca</label>
      <select id="filtroMarca" class="form-select bg-dark text-light border-secondary">
        <option value="">Todas</option>
        <?php
          $marcas = $conexion->query("SELECT idmarcas, nombre_marca FROM marcas ORDER BY nombre_marca ASC");
          while($m = $marcas->fetch(PDO::FETCH_ASSOC)){
              echo "<option value='{$m['idmarcas']}'>{$m['nombre_marca']}</option>";
          }
        ?>
      </select>
    </div>

    <!-- 🧩 Categoría -->
    <div class="mb-3">
      <label class="form-label text-warning"><i class="fa-solid fa-layer-group"></i> Categoría</label>
      <select id="filtroCategoria" class="form-select bg-dark text-light border-secondary">
        <option value="">Todas</option>
        <?php
          $categorias = $conexion->query("SELECT idCategoria, nombre_categoria FROM categoria ORDER BY nombre_categoria ASC");
          while($c = $categorias->fetch(PDO::FETCH_ASSOC)){
              echo "<option value='{$c['idCategoria']}'>{$c['nombre_categoria']}</option>";
          }
        ?>
      </select>
    </div>

    <!-- 💲 Rango de precios -->
<div class="mb-3">
  <label class="form-label text-warning d-flex align-items-center gap-2">
    <i class="fa-solid fa-dollar-sign"></i> Rango de precios
  </label>
  <div class="input-group">
    <span class="input-group-text bg-dark text-light border-secondary">$</span>
    <input type="number" id="precioMin" class="form-control bg-dark text-light border-secondary" placeholder="Mínimo" min="0">
    <span class="input-group-text bg-dark text-light border-secondary">–</span>
    <input type="number" id="precioMax" class="form-control bg-dark text-light border-secondary" placeholder="Máximo" min="0">
  </div>
</div>


  

    <!-- 🧾 Proveedor -->
    <div class="mb-3">
      <label class="form-label text-warning"><i class="fa-solid fa-truck-field"></i> Proveedor</label>
      <select id="filtroProveedor" class="form-select bg-dark text-light border-secondary">
        <option value="">Todos</option>
        <?php
          $proveedores = $conexion->query("SELECT idproveedores, empresa FROM proveedores ORDER BY empresa ASC");
          while($p = $proveedores->fetch(PDO::FETCH_ASSOC)){
              echo "<option value='{$p['idproveedores']}'>{$p['empresa']}</option>";
          }
        ?>
      </select>
    </div>

    <!-- 🔄 Ordenar -->
    <div class="mb-3">
      <label class="form-label text-warning"><i class="fa-solid fa-arrow-down-a-z"></i> Ordenar por</label>
      <select id="ordenarPor" class="form-select bg-dark text-light border-secondary">
        <option value="">Predeterminado</option>
        <option value="precio_asc">Precio: más bajo a más alto</option>
        <option value="precio_desc">Precio: más alto a más bajo</option>
        <option value="nombre_asc">Nombre: A → Z</option>
        <option value="nombre_desc">Nombre: Z → A</option>
      </select>
    </div>

    <hr class="border-secondary">
    <div class="d-flex justify-content-between">
      <button class="btn btn-outline-warning" id="btnAplicarFiltros"><i class="fa-solid fa-check"></i> Aplicar</button>
      <button class="btn btn-outline-light" id="btnLimpiarFiltros"><i class="fa-solid fa-rotate-left"></i> Limpiar</button>
    </div>
  </div>
</div>



<script>
$(document).ready(function () {

    // === Inicializar DataTable ===
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

    // === Abrir panel lateral (offcanvas) ===
    $(document).on('click', '#btnSettings', function () {
        const panel = document.getElementById('panelSettings');
        if (panel) {
            const offcanvas = new bootstrap.Offcanvas(panel);
            offcanvas.show();
        } else {
            console.error('❌ No se encontró #panelSettings en el DOM');
        }
    });

    // === Mostrar detalles del producto ===
    $(document).on('click', '.ver-detalle', function () {
        productoActual = $(this).data('producto'); // ✅ guardamos el producto actual
        const data = $(this).data('producto');
        let atributosHTML = "";

        try {
            if (data.descripcion) {
                const jsonData = JSON.parse(data.descripcion);
                atributosHTML = Object.entries(jsonData).map(([key, value]) => `
                    <div class="atributo-box">
                        <label><i class="fa-solid fa-key"></i> ${key}</label>
                        <input type="text" value="${value}" readonly>
                    </div>
                `).join('');
            } else {
                atributosHTML = `<p class="text-muted">Sin atributos</p>`;
            }
        } catch (e) {
            atributosHTML = `<p class="text-danger">Error al interpretar JSON</p>`;
        }

        const imagenURL = (data.imagen && data.imagen !== "NULL") 
            ? '../' + data.imagen 
            : 'https://via.placeholder.com/250x250?text=Sin+Imagen';

        // Construcción del contenido horizontal
        const html = `
        <div class="col-info">
            <p><strong>Código:</strong> ${data.codigo ?? ''}</p>
            <p><strong>Nombre:</strong> ${data.nombre ?? ''}</p>
            <p><strong>Modelo:</strong> ${data.modelo ?? ''}</p>
            <p><strong>Marca:</strong> ${data.nombre_marca ?? ''}</p>
            <p><strong>Categoría:</strong> ${data.nombre_categoria ?? ''}</p>
            <p><strong>Precio Expuesto:</strong> $${parseFloat(data.precio_expuesto || 0).toFixed(2)}</p>
            <p><strong>Peso (ml):</strong> ${data.peso_ml ?? ''}</p>
            <p><strong>Peso (g):</strong> ${data.peso_g ?? ''}</p>
            <p><strong>Ubicación:</strong> ${(data.lugar ?? '')} ${(data.estante ?? '')}</p>
        </div>

        <div class="col-img">
            <img id="detalleImagen" src="${imagenURL}" alt="Imagen del producto">
        </div>
        `;

        $('#detalleContenido').html(html);
        $('#bloqueAtributos').html(atributosHTML);

        // Restaurar botón si estaba en modo guardar
        $('#btnGuardar').text('Modificar').attr('id', 'btnEditar')
            .removeClass('btn-success').addClass('btn-primary');
    });

let productoActual = null;

// ==============================
//     MODO EDICIÓN DEL MODAL
// ==============================
$(document).on('click', '#btnEditar', function () {

    const data = productoActual;
    const contenido = $('#detalleContenido');

    // ===============================
    //   CAMPOS PRINCIPALES
    // ===============================
    contenido.html(`
        <div class="col-info w-50">

            <label><strong>Código:</strong></label>
            <input type="text" id="edit_codigo" class="form-control form-control-sm" value="${data.codigo ?? ''}">

            <label class="mt-2"><strong>Nombre:</strong></label>
            <input type="text" id="edit_nombre" class="form-control form-control-sm" value="${data.nombre ?? ''}">

            <label class="mt-2"><strong>Modelo:</strong></label>
            <input type="text" id="edit_modelo" class="form-control form-control-sm" value="${data.modelo ?? ''}">

            <label class="mt-2"><strong>Marca:</strong></label>
            <select id="edit_marca" class="form-select form-select-sm bg-dark text-light border-secondary" style="width:100%;">
                <option value="">Cargando...</option>
            </select>

            <label class="mt-2"><strong>Categoría:</strong></label>
            <input type="text" id="edit_categoria" 
                class="form-control form-control-sm bg-secondary text-light" 
                value="${data.nombre_categoria ?? ''}" readonly>

            <label class="mt-2"><strong>Precio Expuesto:</strong></label>
            <input type="number" id="edit_precio" class="form-control form-control-sm" value="${data.precio_expuesto ?? 0}">
            
            ${
                ("<?= $_SESSION['rol'] ?>") === "Administrador"
                ? `
                <label class="mt-2"><strong>Precio Costo:</strong></label>
                <input type="number" id="edit_precio_costo" class="form-control form-control-sm" value="${data.precio_costo ?? 0}">
                `
                : ""
            }

            <label class="mt-2"><strong>Peso (ml):</strong></label>
            <input type="number" id="edit_pesoml" class="form-control form-control-sm" value="${data.peso_ml ?? ''}">

            <label class="mt-2"><strong>Peso (g):</strong></label>
            <input type="number" id="edit_pesog" class="form-control form-control-sm" value="${data.peso_g ?? ''}">
        </div>

        <div class="col-img w-50 text-center">
            <img id="previewImg" src="${data.imagen ? '../'+data.imagen : 'https://via.placeholder.com/250'}" class="img-fluid mb-2" style="max-height:220px;">
            <input type="file" id="nuevaImagen" class="form-control form-control-sm">
        </div>
    `);

    // ===============================
    //    CARGAR MARCAS (Select2)
    // ===============================
    $.ajax({
        url: 'ajax_cargar_marcas.php',
        type: 'GET',
        success: function(opcionesHTML) {

            const select = $('#edit_marca');
            select.html(`<option value="">Sin marca</option>` + opcionesHTML);

            if (data.idmarca && select.find(`option[value="${data.idmarca}"]`).length)
                select.val(String(data.idmarca)).trigger('change');

            select.select2({
                dropdownParent: $('#modalDetalle'),
                width: '100%',
                placeholder: "Buscar marca...",
                allowClear: true
            });
        }
    });

    // ===============================
    //   ATRIBUTOS DE CUBIERTA
    // ===============================
    let cubiertasHTML = '';
    if (data.aro || data.ancho || data.perfil_cubierta || data.tipo || data.varias_aplicaciones) {

        cubiertasHTML += `
            <div class="w-100 mt-3 p-2 border rounded">
                <h6 class="text-warning mb-2"><i class="fa-solid fa-road"></i> Atributos de Cubierta</h6>

                <label>Aro:</label>
                <input type="text" id="edit_aro" class="form-control form-control-sm" value="${data.aro ?? ''}">

                <label class="mt-2">Ancho:</label>
                <input type="text" id="edit_ancho" class="form-control form-control-sm" value="${data.ancho ?? ''}">

                <label class="mt-2">Perfil:</label>
                <input type="text" id="edit_perfil" class="form-control form-control-sm" value="${data.perfil_cubierta ?? ''}">

                <label class="mt-2">Tipo:</label>
                <input type="text" id="edit_tipo" class="form-control form-control-sm" value="${data.tipo ?? ''}">

                <label class="mt-2">Varias Aplicaciones:</label>
                <input type="text" id="edit_varias" class="form-control form-control-sm" value="${data.varias_aplicaciones ?? ''}">
            </div>
        `;
    }

    // ===============================
    //     ATRIBUTOS JSON EDITABLES
    // ===============================
    let atributosHTML = "";

    try {
        if (data.descripcion) {
            const jsonData = JSON.parse(data.descripcion);

            atributosHTML = `
                <div class="w-100 mt-3 p-2 border rounded">
                    <h6 class="text-warning mb-2"><i class="fa-solid fa-key"></i> Atributos adicionales</h6>
                    <div id="contenedorAtributos">
            `;

            Object.entries(jsonData).forEach(([key, val]) => {
                atributosHTML += `
                    <div class="row mb-2 atributo-item">
                        <div class="col-5"><input type="text" class="form-control form-control-sm attr-key" value="${key}"></div>
                        <div class="col-5"><input type="text" class="form-control form-control-sm attr-value" value="${val}"></div>
                        <div class="col-2">
                            <button class="btn btn-danger btn-sm borrar-atributo"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                `;
            });

            atributosHTML += `
                    </div>
                    <button id="btnAgregarAtributo" class="btn btn-outline-warning btn-sm mt-2">
                        <i class="fa-solid fa-plus"></i> Agregar atributo
                    </button>
                </div>
            `;
        }
    } catch (e) {
        atributosHTML = `<p class="text-danger">Error al interpretar JSON</p>`;
    }

    // ===============================
    //           STOCK
    // ===============================
    let stockHTML = `
        <div class="w-100 mt-3 p-2 border rounded">
            <h6 class="text-warning mb-2"><i class="fa-solid fa-box"></i> Stock</h6>

            <label>Mínimo:</label>
            <input type="number" id="edit_stock_minimo" class="form-control form-control-sm" value="${data.stock_minimo ?? 0}">

            <label class="mt-2">Cantidad Actual:</label>
            <input type="number" id="edit_cantidad_actual" class="form-control form-control-sm" value="${data.cantidad_actual ?? 0}">

            <label class="mt-2">Cantidad Exhibida:</label>
            <input type="number" id="edit_cantidad_exhibida" class="form-control form-control-sm" value="${data.cantidad_exhibida ?? 0}">
        </div>
    `;

    // Render final
    $('#bloqueAtributos').html(cubiertasHTML + atributosHTML + stockHTML);

    // PREVIEW IMAGEN
    $(document).on('change', '#nuevaImagen', function (event) {
        const file = event.target.files[0];
        if (file) $('#previewImg').attr('src', URL.createObjectURL(file));
    });

    // Cambiar botón a Guardar
    $(this).text('Guardar')
        .removeClass('btn-primary')
        .addClass('btn-success')
        .attr('id','btnGuardar');
});

// ======================================
//   AGREGAR NUEVO ATRIBUTO JSON
// ======================================
$(document).on("click", "#btnAgregarAtributo", function () {
    $("#contenedorAtributos").append(`
        <div class="row mb-2 atributo-item">
            <div class="col-5"><input type="text" class="form-control form-control-sm attr-key" placeholder="Clave"></div>
            <div class="col-5"><input type="text" class="form-control form-control-sm attr-value" placeholder="Valor"></div>
            <div class="col-2"><button class="btn btn-danger btn-sm borrar-atributo"><i class="fa-solid fa-trash"></i></button></div>
        </div>
    `);
});

// BORRAR ATRIBUTO JSON
$(document).on("click", ".borrar-atributo", function () {
    $(this).closest(".atributo-item").remove();
});

// ==============================
//         GUARDAR
// ==============================
$(document).on('click', '#btnGuardar', function () {

    const data = productoActual;
    const formData = new FormData();

    formData.append('id', data.idproducto);
    formData.append('codigo', $('#edit_codigo').val());
    formData.append('nombre', $('#edit_nombre').val());
    formData.append('modelo', $('#edit_modelo').val());
    formData.append('marca', $('#edit_marca').val());
    formData.append('precio', $('#edit_precio').val());
    formData.append('peso_ml', $('#edit_pesoml').val());
    formData.append('peso_g', $('#edit_pesog').val());

    // Si es admin → guardar precio costo
    if ("<?= $_SESSION['rol'] ?>" === "Administrador") {
        formData.append('precio_costo', $('#edit_precio_costo').val());
    }

    // Imagen
    const img = $('#nuevaImagen')[0].files[0];
    if (img) formData.append('imagen', img);

    // Atributos cubiertas
    if ($('#edit_aro').length) {
        formData.append('aro', $('#edit_aro').val());
        formData.append('ancho', $('#edit_ancho').val());
        formData.append('perfil', $('#edit_perfil').val());
        formData.append('tipo', $('#edit_tipo').val());
        formData.append('varias', $('#edit_varias').val());
    }

    // ==========================
    //   ATRIBUTOS JSON
    // ==========================
    let atributos = {};
    $(".atributo-item").each(function () {
        let key = $(this).find(".attr-key").val().trim();
        let value = $(this).find(".attr-value").val().trim();
        if (key) atributos[key] = value;
    });

    formData.append("atributos_json", JSON.stringify(atributos));

    // ==========================
    //        STOCK
    // ==========================
    formData.append('stock_minimo', $('#edit_stock_minimo').val());
    formData.append('cantidad_actual', $('#edit_cantidad_actual').val());
    formData.append('cantidad_exhibida', $('#edit_cantidad_exhibida').val());

    // ==========================
    //       AJAX GUARDAR
    // ==========================
    $.ajax({
        url: "actualizar_producto.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            Swal.fire("Producto actualizado ✅", "", "success");
            setTimeout(()=> location.reload(), 1200);
        }
    });
});




    // === Aplicar filtros (con precio mínimo y máximo funcionando) ===
    $(document).on('click', '#btnAplicarFiltros', function() {
        const filtros = {
            busqueda: $('#filtroBusqueda').val(),
            marca: $('#filtroMarca').val(),
            categoria: $('#filtroCategoria').val(),
            min: $('#precioMin').val(),
            max: $('#precioMax').val(),
            proveedor: $('#filtroProveedor').val(),
            orden: $('#ordenarPor').val()
        };

        $.ajax({
            url: 'buscar_productos.php',
            method: 'POST',
            data: filtros,
            beforeSend: function() {
                $('#tablaProductos tbody').html('<tr><td colspan="6" class="text-center text-warning">Buscando...</td></tr>');
            },
            success: function(data) {
                $('#tablaProductos tbody').html(data);

                // Cerrar panel automáticamente
                const offcanvas = bootstrap.Offcanvas.getInstance($('#panelSettings')[0]);
                if (offcanvas) offcanvas.hide();

                // Desplazar la vista hacia la tabla
                $('html, body').animate({
                    scrollTop: $('#tablaProductos').offset().top - 100
                }, 500);
            }
        });
    });

    // === Limpiar filtros ===
    $(document).on('click', '#btnLimpiarFiltros', function() {
        $('#panelSettings input, #panelSettings select').val('');
        $('#tablaProductos tbody').load(location.href + ' #tablaProductos tbody>*', '');
    });


    // === Zoom imagen ===
    $(document).on('click', '#detalleImagen, #previewImg', function() {
        const src = $(this).attr('src');
        $('#zoomImagen').attr('src', src);
        const zoomModal = new bootstrap.Modal('#modalZoom');
        zoomModal.show();
    });

});
</script>

<script>
 // === Eliminar producto ===
$(document).on('click', '.borrar-producto', function() {
    const id = $(this).data('id');
    const fila = $(this).closest('tr'); // Guardamos la fila

    Swal.fire({
        title: '¿Seguro que querés eliminar este producto?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        scrollbarPadding: false // 🚫 evita el achicamiento del nav
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'eliminar_producto.php',
                type: 'POST',
                data: { id },
                success: function(res) {
                    if (res.trim() === 'OK') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: 'El producto fue eliminado correctamente',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // 🧩 Remover la fila sin recargar
                        const tabla = $('#tablaProductos').DataTable();
                        tabla.row(fila).remove().draw(false);
                    } else {
                        Swal.fire('Error', 'No se pudo eliminar el producto', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error en la conexión con el servidor', 'error');
                }
            });
        }
    });
});


</script>

<!-- SweetAlert2 -->



<?php include '../dashboard/footer.php'; ?>
