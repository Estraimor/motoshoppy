<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';

// Traer categorías
$stmtCat = $conexion->query("SELECT idCategoria, nombre_categoria FROM categoria ORDER BY nombre_categoria ASC");
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

// === Selección de categoría ===
document.getElementById('selectCategoria').addEventListener('change', function() {
    const categoriaId = this.value;
    if (!categoriaId) return;

    document.getElementById('categoria_id').value = categoriaId;
    document.getElementById('formProducto').classList.remove('d-none');

    // === Cargar datos dinámicos de la categoría ===
    fetch(`cargar_datos_categoria.php?id=${categoriaId}`)
        .then(res => {
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            return res.json();
        })
        .then(data => {
            console.log("📦 Datos recibidos del backend:", data);

            // === Marcas ===
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

            // === JSON dinámico con check habilitar ===
            const jsonCampos = document.getElementById('jsonCampos');
            jsonCampos.innerHTML = "";

            if (Array.isArray(data.json_keys) && typeof data.json_values === 'object') {
                data.json_keys.forEach(k => {
                    const valor = data.json_values && data.json_values[k] ? data.json_values[k] : "";

                    jsonCampos.insertAdjacentHTML("beforeend", `
                        <div class="col-md-4 mb-3 campo-json-wrapper fade-in">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">${k}</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input toggle-json" 
                                           type="checkbox" 
                                           name="json_enabled[${k}]" 
                                           value="1" checked>
                                </div>
                            </div>
                            <input type="text" class="form-control mb-2" value="${k}" readonly>
                            <label class="form-label">Valor</label>
                            <input type="text" class="form-control campo-json" name="json[${k}]" value="${valor}">
                        </div>
                    `);
                });

                // Vincular checkboxes con inputs
                document.querySelectorAll('.toggle-json').forEach((chk) => {
                    chk.addEventListener('change', function () {
                        const valorInput = this.closest('.campo-json-wrapper').querySelector('.campo-json');
                        valorInput.disabled = !this.checked;
                        if (!this.checked) valorInput.value = "";
                    });
                });
            }

            // === Mostrar bloque especial si la categoría es "especial" ===
            const categoriasEspeciales = [12, 13]; // IDs de categorías especiales (cubiertas, ruedas, etc.)
            const bloqueCubiertas = document.getElementById('bloqueCubiertas');
            const catId = parseInt(categoriaId);

            if (categoriasEspeciales.includes(catId)) {
                bloqueCubiertas.classList.remove('d-none');

                // Si el backend trae aplicaciones, precargarlas
                if (data.aplicaciones && Object.keys(data.aplicaciones).length > 0) {
                    cargarAplicacionesDesdePlantilla(data.aplicaciones);
                    document.getElementById('addAplicacion').style.display = 'none';
                } else {
                    aplicacionesCampos.innerHTML = '';
                    document.getElementById('addAplicacion').style.display = 'inline-block';
                }
            } else {
                bloqueCubiertas.classList.add('d-none');
            }
        })
        .catch(err => console.error("❌ Error al cargar datos de la categoría:", err));
});


// === Agregar atributo manual sin borrar los anteriores (con botón eliminar) ===
document.getElementById('addJsonCampo').addEventListener('click', function () {
    const jsonCampos = document.getElementById('jsonCampos');
    const index = document.querySelectorAll(".campo-json-wrapper").length + 1;

    const wrapper = document.createElement("div");
    wrapper.className = "col-md-4 mb-3 campo-json-wrapper fade-in";

    wrapper.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label mb-0">Clave ${index}</label>
            <div class="d-flex align-items-center gap-2">
                <div class="form-check form-switch">
                    <input class="form-check-input toggle-json" 
                           type="checkbox" 
                           name="json_new_enabled[]" 
                           value="1" checked>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger btnEliminarCampo" title="Eliminar atributo">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
        <input type="text" class="form-control mb-2" name="json_new_keys[]" placeholder="Clave">
        <label class="form-label">Valor</label>
        <input type="text" class="form-control campo-json" name="json_new_values[]" placeholder="Valor">
    `;

    jsonCampos.appendChild(wrapper);

    const chk = wrapper.querySelector('.toggle-json');
    const valorInput = wrapper.querySelector('.campo-json');
    chk.addEventListener('change', function () {
        valorInput.disabled = !this.checked;
        if (!this.checked) valorInput.value = "";
    });

    wrapper.querySelector('.btnEliminarCampo').addEventListener('click', function () {
        wrapper.classList.add('fade-out');
        setTimeout(() => wrapper.remove(), 300);
    });

    wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
});


// === Checkbox sin peso ===
document.getElementById('sinPeso').addEventListener('change', function() {
    document.getElementById('peso_ml').disabled = this.checked;
    document.getElementById('peso_g').disabled = this.checked;
    if (this.checked) {
        document.getElementById('peso_ml').value = '';
        document.getElementById('peso_g').value = '';
    }
});


// ==========================================================
// === Lógica de "Varias aplicaciones" (como JSON dinámico) ===
// ==========================================================

const btnAddAplicacion = document.getElementById('addAplicacion');
const aplicacionesCampos = document.getElementById('aplicacionesCampos');

// === Agregar nueva aplicación manual ===
if (btnAddAplicacion) {
    btnAddAplicacion.addEventListener('click', () => {
        const index = document.querySelectorAll('.campo-aplicacion-wrapper').length + 1;

        const wrapper = document.createElement('div');
        wrapper.className = "col-md-4 mb-3 campo-aplicacion-wrapper fade-in";

        wrapper.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Aplicación ${index}</label>
                <div class="d-flex align-items-center gap-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input toggle-aplicacion" 
                               type="checkbox" 
                               name="aplicacion_enabled[]" 
                               value="1" checked>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger btnEliminarAplicacion" title="Eliminar">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>

            <label class="form-label">Clave</label>
            <input type="text" class="form-control mb-2" name="aplicacion_keys[]" placeholder="Clave (ej: tipo)">
            <label class="form-label">Valor</label>
            <input type="text" class="form-control campo-aplicacion" name="aplicacion_values[]" placeholder="Valor (ej: Enduro)">
        `;

        aplicacionesCampos.appendChild(wrapper);

        const chk = wrapper.querySelector('.toggle-aplicacion');
        const valorInput = wrapper.querySelector('.campo-aplicacion');
        chk.addEventListener('change', function () {
            valorInput.disabled = !this.checked;
            if (!this.checked) valorInput.value = "";
        });

        wrapper.querySelector('.btnEliminarAplicacion').addEventListener('click', function () {
            wrapper.classList.add('fade-out');
            setTimeout(() => wrapper.remove(), 300);
        });

        wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
}

// === Precargar aplicaciones desde plantilla (bloqueadas) ===
function cargarAplicacionesDesdePlantilla(aplicaciones = {}) {
    aplicacionesCampos.innerHTML = '';
    if (Object.keys(aplicaciones).length === 0) return;

    Object.entries(aplicaciones).forEach(([clave, valor]) => {
        const div = document.createElement('div');
        div.className = 'col-md-4 mb-3 campo-aplicacion-wrapper';
        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">${clave}</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" checked disabled>
                </div>
            </div>
            <label class="form-label">Clave</label>
            <input type="text" class="form-control mb-2" value="${clave}" readonly>
            <label class="form-label">Valor</label>
            <input type="text" class="form-control" value="${valor}" readonly>
        `;
        aplicacionesCampos.appendChild(div);
    });

    btnAddAplicacion.style.display = 'none';
}

});
</script>






<?php include '../dashboard/footer.php'; ?>
