<?php
include '../dashboard/nav.php';
?>
<link rel="stylesheet" href="./movimiento_stock.css">

<div class="mod-mov-stock">

    <div class="container-fluid mt-4">

        <!-- ===================================================== -->
        <!-- 🔥 RESUMEN DE INVENTARIO -->
        <!-- ===================================================== -->
        <div class="row g-3 mb-4">

            <!-- 🟢 STOCK CORRECTO -->
            <div class="col-md-3">
                <div class="card kpi-card kpi-ok h-100">
                    <div class="card-body text-center">
                        <h6>🟢 Stock correcto</h6>
                        <small>Depósito y exhibición en nivel normal</small>
                        <h3 id="kpiOk">0</h3>
                    </div>
                </div>
            </div>

            <!-- 🟠 PARA ROTAR -->
            <div class="col-md-3">
                <div class="card kpi-card kpi-rotar h-100">
                    <div class="card-body text-center">
                        <h6>🟠 Mover a exhibición</h6>
                        <small>Hay stock disponible en depósito</small>
                        <h3 id="kpiRotar">0</h3>
                    </div>
                </div>
            </div>

            <!-- 🟡 BAJO MÍNIMO -->
            <div class="col-md-3">
                <div class="card kpi-card kpi-bajo h-100">
                    <div class="card-body text-center">
                        <h6>🟡 Stock bajo</h6>
                        <small>Depósito o exhibición debajo del mínimo</small>
                        <h3 id="kpiBajo">0</h3>
                    </div>
                </div>
            </div>

            <!-- 🔴 SIN STOCK -->
            <div class="col-md-3">
                <div class="card kpi-card kpi-sin-stock h-100">
                    <div class="card-body text-center">
                        <h6>🔴 Sin stock</h6>
                        <small>No hay unidades en depósito ni exhibición</small>
                        <h3 id="kpiSinStock">0</h3>
                    </div>
                </div>
            </div>

        </div>
        <!-- FIN RESUMEN -->

        <!-- ===================================================== -->
        <!-- 📦 TABLA INVENTARIO -->
        <!-- ===================================================== -->
        <div class="card bg-dark text-white shadow-sm border-0 mb-4">
            <div class="card-body">

                <h5 class="mb-3">
                    <i class="fa-solid fa-warehouse me-2"></i>
                    Control de Inventario
                </h5>

                <div class="table-responsive">
                    <table id="tablaInventario" class="table table-dark table-hover align-middle">
                        <thead class="text-warning">
                            <tr>
                                <th>Producto</th>
                                <th>Código</th>
                                <th class="text-center">Exhibición</th>
                                <th class="text-center">Depósito</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Mínimo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Carga por AJAX -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <!-- FIN TABLA INVENTARIO -->

        <!-- ===================================================== -->
        <!-- 🔄 HISTORIAL DE MOVIMIENTOS -->
        <!-- ===================================================== -->
        <div class="card bg-dark text-white shadow-sm border-0 mb-4">
            <div class="card-body">

                <h5 class="mb-3">
                    <i class="fa-solid fa-arrows-rotate me-2"></i>
                    Historial de Movimientos
                </h5>

                <form id="filtrosMovimientos" class="row g-3 mb-3">

                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Producto">
                    </div>

                    <div class="col-md-2">
                        <select class="form-select">
                            <option value="">Todos</option>
                            <option value="INGRESO">Ingreso</option>
                            <option value="SALIDA">Salida</option>
                            <option value="AJUSTE">Ajuste</option>
                            <option value="TRANSFERENCIA">Transferencia</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="date" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <input type="date" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-warning w-100">
                            Filtrar
                        </button>
                    </div>

                </form>

                <div class="table-responsive">
                    <table id="tablaMovimientos" class="table table-dark table-striped">
                        <thead class="text-warning">
                            <tr>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Origen</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Carga por AJAX -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <!-- FIN HISTORIAL -->

    </div>
    <!-- FIN CONTAINER -->

    <!-- ===================================================== -->
    <!-- 📦 MODAL STOCK -->
    <!-- ===================================================== -->
    <div class="modal fade" id="modalStock" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white">

                <div class="modal-header border-secondary">
                    <h5 class="modal-title">
                        Gestión de Stock
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- INFO PRODUCTO -->
                    <div id="infoProducto" class="mb-4"></div>

                    <!-- ESTADO ACTUAL -->
                    <div class="row text-center mb-4">

                        <div class="col-md-3">
                            <div class="stock-box">
                                <small>Depósito</small>
                                <h4 id="stockDepositoActual">0</h4>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stock-box">
                                <small>Exhibición</small>
                                <h4 id="stockExhibicionActual">0</h4>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stock-box">
                                <small>Total</small>
                                <h4 id="stockTotalActual">0</h4>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stock-box">
                                <small>Mínimo</small>
                                <h4 id="stockMinimoActual">0</h4>
                            </div>
                        </div>

                    </div>

                    <!-- ZONA DINÁMICA -->
                    <div id="zonaAccionStock"></div>

                </div>

            </div>
        </div>
    </div>
    <!-- FIN MODAL -->

</div>
<!-- FIN WRAPPER PRINCIPAL -->



<script>
$(document).ready(function(){

/* ======================================================
   🔥 TABLA INVENTARIO
====================================================== */

let tablaInventario = $('#tablaInventario').DataTable({
    ajax: {
        url: 'api/listar_inventario.php',
        type: 'GET',
        dataSrc: 'data'
    },
    columns: [
        { data: 'nombre' },
        { data: 'codigo' },
        { data: 'cantidad_exhibida', className:'text-center' },
        { data: 'cantidad_actual', className:'text-center' },
        { 
            data: null,
            className:'text-center',
            render: function(row){
                return parseInt(row.cantidad_exhibida) + parseInt(row.cantidad_actual);
            }
        },
        { data: 'stock_minimo', className:'text-center' },
        { data: 'estado_html', className:'text-center' },
        { data: 'accion_html', className:'text-center' }
    ],
    order: [[0,'asc']],
    pageLength: 10
});

/* ======================================================
   📊 KPIs
====================================================== */

function cargarKPIs(){
    $.get('api/inventario_resumen.php', function(res){
        $('#kpiSinStock').text(res.sin_stock);
        $('#kpiRotar').text(res.rotar);
        $('#kpiBajo').text(res.bajo);
        $('#kpiOk').text(res.ok);
    }, 'json');
}
cargarKPIs();

/* ======================================================
   🔄 TABLA MOVIMIENTOS
====================================================== */

let tablaMovimientos = $('#tablaMovimientos').DataTable({
    ajax: {
        url: 'api/listar_movimientos.php',
        type: 'GET',
        dataSrc: 'data'
    },
    columns: [
        { data: 'fecha' },
        { data: 'producto' },
        { data: 'tipo' },
        { data: 'cantidad' },
        { data: 'origen' },
        { data: 'usuario' }
    ],
    order: [[0,'desc']],
    pageLength: 10
});

/* ======================================================
   🧠 MODAL STOCK
====================================================== */

let productoActual = null;

$(document).on('click', '.btn-stock', function(){

    productoActual = $(this).data('producto');

    $('#zonaAccionStock').html('');

    $('#infoProducto').html(`
        <h5 class="mb-1">${productoActual.nombre}</h5>
        <small class="text-light opacity-75">
            ${productoActual.marca} | ${productoActual.categoria}<br>
            Código: ${productoActual.codigo}
        </small>
    `);

    actualizarVistaActual();
    renderBarraStock();

    if(parseInt(productoActual.stock_minimo) === 0){
        renderModoConfiguracion();
    } else {
        renderModoMovimiento();
    }

    $('#modalStock').modal('show');
});

/* ======================================================
   📊 ACTUALIZAR VISTA ACTUAL
====================================================== */

function actualizarVistaActual(){

    let deposito  = parseInt(productoActual.cantidad_actual);
    let exhibido  = parseInt(productoActual.cantidad_exhibida);
    let minimo    = parseInt(productoActual.stock_minimo);
    let total     = deposito + exhibido;

    $('#stockDepositoActual').text(deposito);
    $('#stockExhibicionActual').text(exhibido);
    $('#stockTotalActual').text(total);
    $('#stockMinimoActual').text(minimo);

    if(total < minimo){
        $('#stockTotalActual').addClass('text-danger');
    } else {
        $('#stockTotalActual').removeClass('text-danger');
    }
}

/* ======================================================
   📈 BARRA VISUAL STOCK VS MINIMO
====================================================== */

function renderBarraStock(){

    let deposito  = parseInt(productoActual.cantidad_actual);
    let exhibido  = parseInt(productoActual.cantidad_exhibida);
    let minimo    = parseInt(productoActual.stock_minimo);
    let total     = deposito + exhibido;

    if(minimo <= 0) return;

    let porcentaje = Math.min((total/minimo)*100,100);

    let color = 'bg-success';
    if(total < minimo) color = 'bg-danger';
    else if(total < minimo*1.5) color = 'bg-warning';

    $('#zonaAccionStock').append(`
        <div class="mb-4">
            <label class="form-label">Nivel actual vs mínimo</label>
            <div class="progress" style="height:10px;">
                <div class="progress-bar ${color}" style="width:${porcentaje}%"></div>
            </div>
        </div>
    `);
}

/* ======================================================
   🟥 CONFIGURACIÓN INICIAL
====================================================== */

function renderModoConfiguracion(){

    $('#zonaAccionStock').append(`
        <hr class="border-secondary">
        <h6 class="mb-3 text-warning">
            Configuración inicial
        </h6>

        <div class="mb-3">
            <label class="form-label">Stock mínimo</label>
            <input type="number" class="form-control bg-dark text-white border-secondary" id="nuevoMinimo" min="1">
        </div>

        <div class="mb-3">
            <label class="form-label">Cantidad en depósito</label>
            <input type="number" class="form-control bg-dark text-white border-secondary" id="nuevoDeposito" min="0">
        </div>

        <div class="mb-4">
            <label class="form-label">Cantidad en exhibición</label>
            <input type="number" class="form-control bg-dark text-white border-secondary" id="nuevoExhibido" min="0">
        </div>

        <button class="btn btn-success w-100" id="guardarConfiguracion">
            Guardar configuración
        </button>
    `);
}

$(document).on('click','#guardarConfiguracion',function(){

    let minimo   = parseInt($('#nuevoMinimo').val()) || 0;
    let deposito = parseInt($('#nuevoDeposito').val()) || 0;
    let exhibido = parseInt($('#nuevoExhibido').val()) || 0;

    if(minimo <= 0){
        Swal.fire('Error','Debe ingresar un stock mínimo válido','error');
        return;
    }

    $.post('api/configurar_stock.php',{
        producto_id: productoActual.idproducto,
        minimo: minimo,
        deposito: deposito,
        exhibido: exhibido
    },function(){

        Swal.fire({
            icon:'success',
            title:'Stock configurado',
            timer:1200,
            showConfirmButton:false
        });

        $('#modalStock').modal('hide');
        tablaInventario.ajax.reload();
        tablaMovimientos.ajax.reload();
        cargarKPIs();
    });
});

/* ======================================================
   🟢 MOVIMIENTO DE STOCK
====================================================== */

function renderModoMovimiento(){

    $('#zonaAccionStock').append(`
        <hr class="border-secondary">
        <h6 class="mb-3 text-warning">
            Movimiento de stock
        </h6>

        <div class="mb-3">
            <label class="form-label">Origen del movimiento</label>
            <select class="form-select bg-dark text-white border-secondary" id="origenMovimiento">
                <option value="deposito">Depósito → Exhibición</option>
                <option value="exhibicion">Exhibición → Depósito</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label">Cantidad a mover</label>
            <input type="number" class="form-control bg-dark text-white border-secondary" id="cantidadMover" min="1">
        </div>

        <button class="btn btn-warning w-100" id="confirmarMovimiento">
            Confirmar movimiento
        </button>
    `);
}

$(document).on('click','#confirmarMovimiento',function(){

    let cantidad = parseInt($('#cantidadMover').val()) || 0;
    let origen   = $('#origenMovimiento').val();

    if(cantidad <= 0){
        Swal.fire('Error','Ingrese una cantidad válida','error');
        return;
    }

    Swal.fire({
        title: 'Confirmar movimiento',
        text: '¿Desea continuar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, mover',
        cancelButtonText: 'Cancelar'
    }).then((result)=>{

        if(result.isConfirmed){

            $.post('api/mover_stock.php',{
                producto_id: productoActual.idproducto,
                cantidad: cantidad,
                origen: origen
            },function(){

                Swal.fire({
                    icon:'success',
                    title:'Movimiento registrado',
                    timer:1200,
                    showConfirmButton:false
                });

                $('#modalStock').modal('hide');
                tablaInventario.ajax.reload();
                tablaMovimientos.ajax.reload();
                cargarKPIs();
            });

        }

    });

});

});
</script>




<?php include '../dashboard/footer.php'; ?>
