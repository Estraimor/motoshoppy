<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';

// Traer categorías
$stmtCat = $conexion->query("SELECT idCategoria, nombre_categoria FROM categoria c
WHERE c.estado = 1
ORDER BY nombre_categoria ASC");
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Traer ubicaciones
$stmtUb = $conexion->query("SELECT idubicacion_producto, lugar, estante FROM ubicacion_producto ORDER BY lugar, estante");
$ubicaciones = $stmtUb->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="estilos_productos.css">

<div class="content-header d-flex justify-content-between align-items-center">
    <h2><i class="fa-solid fa-box"></i> Gestión de Productos</h2>
</div>

<div class="content-body">
    <!-- Paso 1 -->
    <div class="card shadow-sm p-3 mb-3">
        <h5><i class="fa-solid fa-layer-group"></i> Paso 1: Seleccione una categoría</h5>
        <select id="selectCategoria" class="form-select">
            <option value="">-- Seleccione Categoría --</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['idCategoria'] ?>">
                    <?= htmlspecialchars($cat['nombre_categoria']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Paso 2 -->
    <div id="formProducto" class="card shadow-sm p-3 mb-3 d-none">
        <h5><i class="fa-solid fa-pen-to-square"></i> Paso 2: Complete la información</h5>
        <form id="productoForm" method="POST" action="crear_producto.php" enctype="multipart/form-data">
            <input type="hidden" name="categoria_id" id="categoria_id">

            <!-- Datos básicos -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Código</label>
                    <input type="text" class="form-control" name="codigo">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" >
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Marca</label>
                    <select class="form-select" name="marcas_idmarcas" id="marcasSelect" >
                        <option value="0" selected disabled>-- Seleccione Marca --</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Modelo</label>
                    <input type="text" class="form-control" name="modelo">
                </div>
            </div>

            <!-- Peso -->
            <h6><i class="fa-solid fa-weight-scale"></i> Peso</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Peso (ml)</label>
                    <input type="number" class="form-control" name="peso_ml" id="peso_ml">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Peso (g)</label>
                    <input type="number" class="form-control" name="peso_g" id="peso_g">
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="sinPeso">
                        <label class="form-check-label" for="sinPeso">Sin peso ni ML</label>
                    </div>
                </div>
            </div>


            <h6><i class="fa-solid fa-cubes"></i> Stock</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Stock mínimo</label>
                    <input type="number" class="form-control" name="stock_minimo" min="0" >
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cantidad actual</label>
                    <input type="number" class="form-control" name="cantidad_actual" min="0" >
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cantidad Exibida</label>
                    <input type="number" class="form-control" name="cantidad_exhibida" min="0" >
                </div>
            </div>

            <!-- Ubicación -->
            <h6><i class="fa-solid fa-map-marker-alt"></i> Ubicación</h6>
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Ubicación existente</label>
                    <select class="form-select" name="ubicacion_id">
                        <option value="">-- Seleccione --</option>
                        <?php foreach ($ubicaciones as $u): ?>
                            <option value="<?= $u['idubicacion_producto'] ?>">
                                <?= htmlspecialchars($u['lugar'] . " - " . $u['estante']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nueva ubicación</label>
                    <input type="text" class="form-control" name="nueva_ubicacion" placeholder="Lugar - Estante">
                </div>
            </div>

           

            <h6><i class="fa-solid fa-list"></i> Atributos adicionales</h6>
            <div id="jsonCampos" class="row"></div>

            <div class="mb-3">
                <button type="button" id="addJsonCampo" class="btn btn-outline-info btn-sm">
                    <i class="fa-solid fa-plus"></i> Agregar atributo
                </button>
            </div>

            <!-- Precios -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Precio Costo</label>
                    <input type="number" step="0.01" class="form-control" name="precio_costo">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Precio Expuesto</label>
                    <input type="number" step="0.01" class="form-control" name="precio_expuesto">
                </div>
            </div>
            
            <h6><i class="fa-solid fa-image"></i> Imagen del Producto</h6>
<div class="row mb-3">
    <!-- Input para subir -->
    <div class="col-md-6">
        <label class="form-label">Subir Imagen</label>
        <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
    </div>

    <!-- Preview -->
    <div class="col-md-6 d-flex align-items-center">
        <div id="previewContainer" 
             class="border rounded p-2 text-center bg-dark" 
             style="width:400px; height:400px; margin:auto;">
            <img id="previewImg" src="" alt="Preview" 
                 style="max-width:100%; max-height:100%; display:none; object-fit:contain;">
        </div>
    </div>
</div>



<!-- === APARTADO PARA CATEGORÍAS ESPECIALES === -->
<div id="bloqueCubiertas" class="card shadow-sm p-3 mb-3 d-none">
    <h5><i class="fa-solid fa-circle-info"></i> Datos específicos de Cubiertas / Ruedas</h5>

    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">Aro</label>
            <input type="number" class="form-control" name="aro" min="8" max="30" placeholder="Ej: 17">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Ancho (mm)</label>
            <input type="number" step="0.1" class="form-control" name="ancho" placeholder="Ej: 120">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Perfil (%)</label>
            <input type="number" step="0.1" class="form-control" name="perfil_cubierta" placeholder="Ej: 70">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Tipo</label>
            <select class="form-select" name="tipo">
                <option value="">-- Seleccione --</option>
                <option value="TL">TL (sin cámara)</option>
                <option value="TT">TT (con cámara)</option>
            </select>
        </div>
    </div>

    <hr>

    <div id="listaAplicaciones" class="row g-2"></div>

    <!-- === VARIAS APLICACIONES (tipo JSON) === -->
<div class="mb-3">
  <h6><i class="fa-solid fa-list"></i> Varias aplicaciones</h6>
  <div id="aplicacionesCampos" class="row"></div>
  <button type="button" id="addAplicacion" class="btn btn-outline-info btn-sm mt-2">
    <i class="fa-solid fa-plus"></i> Agregar aplicación
  </button>
</div>
</div>
<!-- === FIN APARTADO ESPECIAL === -->

<!-- === FIN APARTADO ESPECIAL === -->


            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-save"></i> Guardar Producto
            </button>
        </form>
    </div>
</div>

<script>
// Preview de imagen
document.getElementById('imagen').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const previewImg = document.getElementById('previewImg');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewImg.src = '';
        previewImg.style.display = 'none';
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {

// =====================================================
// === SELECCIÓN DE CATEGORÍA
// =====================================================
document.getElementById('selectCategoria').addEventListener('change', function() {
    const categoriaId = this.value;
    if (!categoriaId) return;

    document.getElementById('categoria_id').value = categoriaId;
    document.getElementById('formProducto').classList.remove('d-none');

    fetch(`cargar_datos_categoria.php?id=${categoriaId}`)
        .then(res => {
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            return res.json();
        })
        .then(data => {

            // === Cargar Marcas ===
            const marcasSelect = document.getElementById('marcasSelect');
            marcasSelect.innerHTML = `<option value="0" selected disabled>-- Seleccione Marca --</option>`;

            if (Array.isArray(data.marcas)) {
                data.marcas.forEach(m => {
                    const opt = document.createElement("option");
                    opt.value = m.idmarcas;
                    opt.textContent = m.nombre_marca;
                    marcasSelect.appendChild(opt);
                });
            }

            // =====================================================
            // ❌ ELIMINADO: Precarga automática de JSON
            // Ahora el usuario agrega lo que quiera manualmente
            // =====================================================

            // === Bloques especiales (Cubiertas, etc) ===
            const categoriasEspeciales = [12, 14,85,87];
            const bloqueCubiertas = document.getElementById('bloqueCubiertas');
            const catId = parseInt(categoriaId);

            if (categoriasEspeciales.includes(catId)) {
                bloqueCubiertas.classList.remove('d-none');
            } else {
                bloqueCubiertas.classList.add('d-none');
            }

        })
        .catch(err => console.error("Error al cargar datos:", err));
});


// =====================================================
// === ATRIBUTOS DINÁMICOS (JSON LIBRE)
// =====================================================
document.getElementById('addJsonCampo').addEventListener('click', function () {
    const jsonCampos = document.getElementById('jsonCampos');
    const index = document.querySelectorAll(".campo-json-wrapper").length + 1;

    const wrapper = document.createElement("div");
    wrapper.className = "col-md-4 mb-3 campo-json-wrapper fade-in";

    wrapper.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label mb-0">Atributo ${index}</label>
            <button type="button" class="btn btn-sm btn-outline-danger btnEliminarCampo">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>

        <input type="text" 
               class="form-control mb-2" 
               name="json_keys[]" 
               placeholder="Clave (ej: Viscosidad)" required>

        <input type="text" 
               class="form-control" 
               name="json_values[]" 
               placeholder="Valor (ej: 10W40)" required>
    `;

    jsonCampos.appendChild(wrapper);

    wrapper.querySelector('.btnEliminarCampo').addEventListener('click', function () {
        wrapper.classList.add('fade-out');
        setTimeout(() => wrapper.remove(), 300);
    });

    wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
});


// =====================================================
// === CHECKBOX SIN PESO
// =====================================================
document.getElementById('sinPeso').addEventListener('change', function() {
    const pesoML = document.getElementById('peso_ml');
    const pesoG = document.getElementById('peso_g');

    pesoML.disabled = this.checked;
    pesoG.disabled = this.checked;

    if (this.checked) {
        pesoML.value = '';
        pesoG.value = '';
    }
});


// =====================================================
// === APLICACIONES DINÁMICAS (PARA CUBIERTAS)
// =====================================================
const btnAddAplicacion = document.getElementById('addAplicacion');
const aplicacionesCampos = document.getElementById('aplicacionesCampos');

if (btnAddAplicacion) {
    btnAddAplicacion.addEventListener('click', () => {

        const index = document.querySelectorAll('.campo-aplicacion-wrapper').length + 1;

        const wrapper = document.createElement('div');
        wrapper.className = "col-md-4 mb-3 campo-aplicacion-wrapper fade-in";

        wrapper.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Aplicación ${index}</label>
                <button type="button" class="btn btn-sm btn-outline-danger btnEliminarAplicacion">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>

            <input type="text" 
                   class="form-control mb-2" 
                   name="aplicacion_keys[]" 
                   placeholder="Clave (ej: Tipo de uso)" required>

            <input type="text" 
                   class="form-control" 
                   name="aplicacion_values[]" 
                   placeholder="Valor (ej: Enduro)" required>
        `;

        aplicacionesCampos.appendChild(wrapper);

        wrapper.querySelector('.btnEliminarAplicacion').addEventListener('click', function () {
            wrapper.classList.add('fade-out');
            setTimeout(() => wrapper.remove(), 300);
        });

        wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });

    });
}

});
</script>







<?php include '../dashboard/footer.php'; ?>
