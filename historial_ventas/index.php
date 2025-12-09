    <?php
    include '../dashboard/nav.php';
    require_once '../conexion/conexion.php';

    // Consulta usando PDO
    $sql = "
SELECT 
    v.idVenta,
    v.fecha,
    v.total,

    mp.nombre AS metodo_pago,
    tc.nombre AS tipo_comprobante,

    u.nombre AS user_nombre,
    u.apellido AS user_apellido,

    /* Cantidad de productos devueltos */
    (
        SELECT COUNT(*) 
        FROM detalle_venta dv 
        WHERE dv.ventas_idVenta = v.idVenta 
          AND dv.devuelto = 1
    ) AS productos_devueltos,

    /* Cantidad total original de productos */
    (
        SELECT SUM(dv2.cantidad) 
        FROM detalle_venta dv2
        WHERE dv2.ventas_idVenta = v.idVenta
    ) AS cant_productos,

    /* ¿Está cancelada? */
    (
        SELECT COUNT(*) 
        FROM ventas_anuladas va 
        WHERE va.ventas_idVenta = v.idVenta
    ) AS esta_cancelada

FROM ventas v
LEFT JOIN usuario u 
    ON v.usuario_idusuario = u.idusuario
LEFT JOIN metodo_pago mp
    ON mp.idmetodo_pago = v.metodo_pago_idmetodo_pago
LEFT JOIN tipo_comprobante tc
    ON tc.idtipo_comprobante = v.tipo_comprobante_idtipo_comprobante

ORDER BY v.fecha DESC;


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
       $clase = "";

// cancelada
if ($row['esta_cancelada'] > 0) {
    $clase = "venta-cancelada";

// devolución parcial o total
} elseif ($row['productos_devueltos'] > 0) {
    
    if ($row['productos_devueltos'] == $row['cant_productos']) {
        $clase = "venta-devuelta-total";
    } else {
        $clase = "venta-devuelta-parcial";
    }
}

        ?>

        <tr class="<?= $clase ?>">
            <td><?= $row['idVenta'] ?></td>
            <td><?= $row['fecha'] ?></td>
            <td><?= $row['user_nombre'].' '.$row['user_apellido'] ?></td>
           <td><?= ucfirst($row['metodo_pago'] ?? 'Sin método') ?></td>
<td><?= ucfirst($row['tipo_comprobante'] ?? 'Sin comprobante') ?></td>

            <td>$<?= number_format($row['total'],2,',','.') ?></td>
            <td>
                <button 
                    class="btn btn-warning btn-sm ver-detalle"
                    data-id="<?= $row['idVenta'] ?>"
                    data-fecha="<?= $row['fecha'] ?>"
                    data-vendedor="<?= $row['user_nombre'].' '.$row['user_apellido'] ?>"
                    data-metodo="<?= ucfirst($row['metodo_pago'] ?? 'Sin método') ?>"
data-comprobante="<?= ucfirst($row['tipo_comprobante'] ?? 'Sin comprobante') ?>"

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
     MODAL CANCELAR VENTA
================================ -->
<div class="modal fade" id="modalCancelarVenta" tabindex="-1">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content bg-dark text-light">

      <div class="modal-header border-secondary">
        <h5 class="modal-title fw-bold text-danger">❌ Cancelar Venta</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <p class="mb-2">Ingresá el motivo de la cancelación:</p>

        <textarea id="motivoCancelarVenta"
                  class="form-control"
                  style="height:120px; resize:none;"
                  placeholder="Escribí el motivo..."></textarea>

      </div>

      <div class="modal-footer border-secondary">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
        <button class="btn btn-danger" id="btnConfirmarCancelarVenta">
          Confirmar Cancelación
        </button>
      </div>

    </div>
  </div>
</div>


   <script>
// ===========================================
//  DataTable
// ===========================================
$(document).ready(() => {

    $('#tablaHistorial').DataTable({
        order: [[1, 'desc']],
        pageLength: 10
    });

    // ===========================================
    //  Ver detalle
    // ===========================================
    $(document).on("click", ".ver-detalle", function(){

        $("#d_fecha").text($(this).data("fecha"));
        $("#d_vendedor").text($(this).data("vendedor"));
        $("#d_metodo").text($(this).data("metodo"));
        $("#d_comprobante").text($(this).data("comprobante"));
        $("#d_total").text("$" + $(this).data("total"));

        let idVenta = $(this).data("id");
        let esCancelada = $(this).closest("tr").hasClass("venta-cancelada");

        $.post("obtener_detalle.php", { idVenta, modo: "view" }, function(data){

            $("#detalleContenido").html(data);
            $("#modalDetalle").modal("show");

            // Botones
            if (esCancelada){
                $("#btnCancelarVentaContainer").html(`
                    <button type="button"
                            class="btn btn-success"
                            id="btnReactivarVenta"
                            data-id="${idVenta}">
                        🔄 Reactivar Venta
                    </button>`);
                $("#btnDevolverParcialContainer").html("");

            } else {
                $("#btnCancelarVentaContainer").html(`
                    <button type="button"
                            class="btn btn-danger"
                            id="btnCancelarVenta"
                            data-id="${idVenta}">
                        ❌ Cancelar Venta Completa
                    </button>`);

                $("#btnDevolverParcialContainer").html(`
                    <button type="button"
                            class="btn btn-primary"
                            id="btnDevolverParcial"
                            data-id="${idVenta}">
                        🔄 Devolución Parcial
                    </button>`);
            }
        });
    });
});


// ======================================================
// CANCELAR VENTA COMPLETA — USANDO MODAL PERSONALIZADO
// ======================================================
let ventaSeleccionada = null;

$(document).on("click", "#btnCancelarVenta", function(){

    ventaSeleccionada = $(this).data("id");

    // Limpiar motivo antes de abrir
    $("#motivoCancelarVenta").val("");

    // Abrir modal
    $("#modalCancelarVenta").modal("show");
});

// Confirmar cancelación en modal
$(document).on("click", "#btnConfirmarCancelarVenta", function(){

    let motivo = $("#motivoCancelarVenta").val().trim();

    if (motivo.length < 3){
        Swal.fire("Atención", "Ingresá un motivo válido.", "warning");
        return;
    }

    $.post("cancelar_venta.php", {
        idVenta: ventaSeleccionada,
        motivo: motivo
    }, function(resp){

        if (resp.trim() === "ok") {
            Swal.fire("Venta Cancelada", "", "success")
                .then(() => location.reload());

        } else {
            Swal.fire("Error", resp, "error");
        }
    });
});




// ======================================================
// REACTIVAR VENTA
// ======================================================
$(document).on("click", "#btnReactivarVenta", function(){
    desbloquearBootstrap();

    let id = $(this).data("id");

    Swal.fire({
        title: "¿Reactivar esta venta?",
        showCancelButton: true,
        confirmButtonText: "Reactivar",
        confirmButtonColor: "#28a745"
    }).then(result => {

        if (result.isConfirmed){

            $.post("activar_venta.php", { idVenta: id }, function(resp){

                if (resp.trim() === "ok"){
                    Swal.fire("Venta Reactivada", "", "success")
                        .then(()=> location.reload());
                } else {
                    Swal.fire("Error", resp, "error");
                }
            });
        }
    });
});



// ======================================================
// DEVOLUCIÓN PARCIAL
// ======================================================
$(document).on("click", "#btnDevolverParcial", function () {

    desbloquearBootstrap();

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

// Confirmar devolución parcial
$(document).on("click", "#btnConfirmarDevolucion", function () {

    desbloquearBootstrap();

    let idVenta = $(this).data("id");
    let motivo = $("#dp_motivo").val().trim();

    if (motivo === "") {
        Swal.fire("Atención", "Ingresá un motivo.", "warning");
        return;
    }

    let items = [];

    $(".chkDevolver:checked").each(function () {

        items.push({
            idDetalle: $(this).attr("data-id"),
            producto_id: $(this).attr("data-producto"),
            cantidad: $(this).attr("data-cant")
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

        if (resp === "ok" || resp === "completa") {
            Swal.fire("Listo", "La devolución fue aplicada", "success")
                .then(()=> location.reload());
        } else {
            Swal.fire("Error inesperado", resp, "error");
        }
    });
});



// ======================================================
// CANCELAR UNA DEVOLUCIÓN (SIN MOTIVO)
// ======================================================
$(document).on("click", ".btnCancelarDevolucion", function () {

    desbloquearBootstrap();

    let idDev     = $(this).data("iddev");
    let idVenta   = $(this).data("idventa");
    let producto  = $(this).data("producto");

    Swal.fire({
        title: "¿Cancelar devolución?",
        html: `
            <p>Se reactivará el producto <strong>${producto}</strong> en la venta.</p>
        `,
        showCancelButton: true,
        confirmButtonText: "Sí, cancelar",
        confirmButtonColor: "#d33",
        cancelButtonText: "Volver"
    }).then(result => {

        if (!result.isConfirmed) return;

        $.post("cancelar_devolucion.php", {
            idDevolucion: idDev,
            idVenta: idVenta,
            producto_id: producto
        }, function(resp){

            if (resp.trim() === "ok") {
                Swal.fire("Listo", "La devolución fue cancelada.", "success")
                    .then(()=> location.reload());
            } else {
                Swal.fire("Error", resp, "error");
            }
        });
    });
});



// ======================================================
// FIX OVERLAY BOOTSTRAP
// ======================================================
function desbloquearBootstrap(){

    // 1) Evitar aria-hidden en wrappers del modal
    document.querySelectorAll('[aria-hidden="true"]').forEach(e => {
        e.removeAttribute('aria-hidden');
    });

    // 2) Restaurar pointer-events
    document.querySelectorAll('*').forEach(e => {
        if (e.style.pointerEvents === 'none'){
            e.style.pointerEvents = 'auto';
        }
    });

    // 3) Forzar que SweetAlert pueda tomar foco
    const swal = document.querySelector(".swal2-container");
    if (swal){
        swal.removeAttribute("inert");
        swal.style.pointerEvents = "auto";
    }

    // 4) Destruir el backdrop de bootstrap si bloquea el input
    const backdrops = document.querySelectorAll(".modal-backdrop");
    backdrops.forEach(b => b.style.pointerEvents = "none");
}

</script>










    <?php include '../dashboard/footer.php'; ?>
