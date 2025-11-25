    <?php
    include '../dashboard/nav.php';
    require_once '../conexion/conexion.php';

    // Consulta usando PDO
    $sql = "
    SELECT 
        v.idVenta,
        v.fecha,
        v.total,
        v.metodo_pago,
        v.tipo_comprobante,
        u.nombre AS user_nombre,
        u.apellido AS user_apellido,

        /* ¿Tiene devoluciones parciales? */
        (SELECT COUNT(*) 
            FROM detalle_venta dv 
            WHERE dv.venta_id = v.idVenta 
            AND dv.devuelto = 1
        ) AS productos_devueltos,

        /* Cantidad total original de productos */
        (SELECT SUM(cantidad) 
            FROM detalle_venta 
            WHERE venta_id = v.idVenta
        ) AS cant_productos,

        /* Estado cancelada si existe en la tabla ventas_anuladas */
        (SELECT COUNT(*) 
            FROM ventas_anuladas va 
            WHERE va.venta_id = v.idVenta
        ) AS esta_cancelada

    FROM ventas v
    LEFT JOIN usuario u ON v.usuario_id = u.idusuario
    ORDER BY v.fecha DESC
    ";


    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $ventas = $stmt->fetchAll();
    ?>

    <link rel="stylesheet" href="./historial_ventas.css">

    <div class="container mt-4">
        <h2 class="mb-3"><i class="fa-solid fa-clock-rotate-left"></i> Historial de Ventas</h2>
        <hr>

        <table id="tablaHistorial" class="table table-striped table-bordered table-dark align-middle w-100">
            <thead class="table-secondary text-dark">
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Vendedor</th>
                    <th>Método</th>
                    <th>Comprobante</th>
                    <th>Total</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas as $row): ?>

        <?php
        // Determinar color según estado
        $clase = "";

        // Venta cancelada → Rojo
        if ($row['esta_cancelada'] > 0) {
            $clase = "venta-cancelada";

        // Devolución completa → rojo
        } elseif ($row['productos_devueltos'] > 0 && $row['productos_devueltos'] == $row['cant_productos']) {
            $clase = "venta-devuelta-total";

        // Devolución parcial → amarillo
        } elseif ($row['productos_devueltos'] > 0) {
            $clase = "venta-devuelta-parcial";
        }
        ?>

        <tr class="<?= $clase ?>">
            <td><?= $row['idVenta'] ?></td>
            <td><?= $row['fecha'] ?></td>
            <td><?= $row['user_nombre'].' '.$row['user_apellido'] ?></td>
            <td><?= ucfirst($row['metodo_pago']) ?></td>
            <td><?= ucfirst($row['tipo_comprobante']) ?></td>
            <td>$<?= number_format($row['total'],2,',','.') ?></td>
            <td>
                <button 
                    class="btn btn-warning btn-sm ver-detalle"
                    data-id="<?= $row['idVenta'] ?>"
                    data-fecha="<?= $row['fecha'] ?>"
                    data-vendedor="<?= $row['user_nombre'].' '.$row['user_apellido'] ?>"
                    data-metodo="<?= ucfirst($row['metodo_pago']) ?>"
                    data-comprobante="<?= ucfirst($row['tipo_comprobante']) ?>"
                    data-total="<?= number_format($row['total'],2,',','.') ?>"
                >
                    Ver Detalle
                </button>
            </td>
        </tr>

    <?php endforeach; ?>

            </tbody>
        </table>
    </div>


    <!-- ================================
            MODAL DETALLE
    ================================ -->
    <div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-dark text-light shadow-lg" style="border:1px solid #555;">
        
        <div class="modal-header border-secondary">
            <h4 class="modal-title fw-bold">🧾 Detalle de Venta</h4>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <div class="text-center mb-3">
            <h3 class="fw-bold">MOTOSHOPPY</h3>
            <small class="text-secondary">Comprobante Interno</small>
            </div>

            <div class="row mb-3">
            <div class="col-md-6">
                <strong>Vendedor:</strong> <span id="d_vendedor"></span><br>
                <strong>Fecha:</strong> <span id="d_fecha"></span>
            </div>
            <div class="col-md-6">
                <strong>Método de pago:</strong> <span id="d_metodo"></span><br>
                <strong>Comprobante:</strong> <span id="d_comprobante"></span>
            </div>
            </div>

            <div id="detalleContenido"></div>

            <div class="text-end mt-3">
            <h4>Total: <span id="d_total" class="fw-bold text-warning"></span></h4>
            </div>

        </div>

        <div class="modal-footer border-secondary">
            <button class="btn btn-outline-light" onclick="window.print()">🖨 Imprimir</button>

            <span id="btnDevolverParcialContainer"></span>
            <span id="btnCancelarVentaContainer"></span>

            <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cerrar</button>
        </div>

        </div>
    </div>
    </div>


    <!-- ================================
            JAVASCRIPT
    ================================ -->
    <script>
$(document).ready(() => {

    // ===========================================
    //  TABLA HISTORIAL
    // ===========================================
    $('#tablaHistorial').DataTable({
        order: [[1, 'desc']],
        pageLength: 10
    });

    // ===========================================
    //  VER DETALLE
    // ===========================================
    $(document).on("click", ".ver-detalle", function(){

        $("#d_fecha").text($(this).data("fecha"));
        $("#d_vendedor").text($(this).data("vendedor"));
        $("#d_metodo").text($(this).data("metodo"));
        $("#d_comprobante").text($(this).data("comprobante"));
        $("#d_total").text("$" + $(this).data("total"));

        let idVenta = $(this).data("id");

        $.post("obtener_detalle.php", { idVenta, modo: "view" }, function(data){
            $("#detalleContenido").html(data);
            $("#modalDetalle").modal("show");

            $("#btnCancelarVentaContainer").html(`
                <button class="btn btn-danger" id="btnCancelarVenta" data-id="${idVenta}">
                    ❌ Cancelar Venta Completa
                </button>
            `);

            $("#btnDevolverParcialContainer").html(`
                <button class="btn btn-primary" id="btnDevolverParcial" data-id="${idVenta}">
                    🔄 Devolución Parcial
                </button>
            `);
        });
    });

});


// ===========================================
//  CANCELAR VENTA COMPLETA
// ===========================================
$(document).on("click", "#btnCancelarVenta", function(){
    let id = $(this).data("id");

    Swal.fire({
        title: "¿Cancelar venta completa?",
        input: "text",
        inputLabel: "Motivo de la cancelación",
        inputPlaceholder: "Ej: Producto defectuoso, error de carga, etc",
        showCancelButton: true,
        confirmButtonText: "Cancelar Venta",
        confirmButtonColor: "#d33"
    }).then(result=>{
        if(result.isConfirmed){
            $.post("cancelar_venta.php", {idVenta:id, motivo: result.value}, function(resp){
                if(resp.trim()=="ok"){
                    Swal.fire("✅ Venta Cancelada","","success").then(()=>location.reload());
                }
            });
        }
    });
});


// ===========================================
//  ABRIR MODO DEVOLUCIÓN PARCIAL
// ===========================================
$(document).on("click", "#btnDevolverParcial", function () {

    let idVenta = $(this).data("id");

    $.post("obtener_detalle.php", { idVenta, modo: "select" }, function (html) {

        $("#detalleContenido").html(html);

        $("#detalleContenido").append(`
            <div class="mt-3">
                <label class="fw-bold">Motivo de la devolución</label>
                <textarea id="dp_motivo" 
                    class="form-control" 
                    style="height:90px; resize:none;"
                    placeholder="Escribí el motivo..."></textarea>
            </div>

            <div class="text-end mt-3">
                <button class="btn btn-primary"
                        id="btnConfirmarDevolucion"
                        data-id="${idVenta}">
                    Confirmar Devolución
                </button>
            </div>
        `);
    });
});


// ===========================================
//  CONFIRMAR DEVOLUCIÓN PARCIAL
// ===========================================
$(document).on("click", "#btnConfirmarDevolucion", function () {

    let idVenta = $(this).data("id");
    let motivo = $("#dp_motivo").val().trim();

    if (motivo === "") {
        Swal.fire("Atención", "Ingresá un motivo.", "warning");
        return;
    }

    let items = [];

    $(".chkDevolver:checked").each(function () {

        let idDetalle = $(this).attr("data-id");
        let productoId = $(this).attr("data-producto");
        let cantidad = $(this).attr("data-cant");

        items.push({
            idDetalle: idDetalle,
            producto_id: productoId,
            cantidad: cantidad
        });
    });

    if (items.length === 0) {
        Swal.fire("Error", "Seleccioná al menos un producto válido.", "error");
        return;
    }

    $.post("devolucion_parcial.php", {
        idVenta: idVenta,
        motivo: motivo,
        items: JSON.stringify(items)
    }, function (resp) {

        resp = resp.trim();

        console.log("RESPUESTA:", resp);

        // =====================================
        //  MANEJO DE RESPUESTAS DEL BACKEND
        // =====================================

        if (resp === "ok") {
            Swal.fire("Devolución realizada", "", "success")
                .then(()=> location.reload());
        }
        else if (resp === "completa") {
            Swal.fire("Venta completamente devuelta", "", "success")
                .then(()=> location.reload());
        }
        else if (resp === "no_hay_productos") {
            Swal.fire(
                "No hay productos para devolver",
                "Todos los productos ya fueron devueltos previamente.",
                "error"
            );
        }
        else {
            Swal.fire("Error inesperado", resp, "error");
        }
    });
});
</script>



    <?php include '../dashboard/footer.php'; ?>
