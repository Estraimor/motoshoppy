    <?php
    include '../dashboard/nav.php';
    requerirRol('Administrador', 'Ventas');
    require_once '../conexion/conexion.php';

    // Filtro de fechas (desde/hasta)
    $desde = trim($_GET['desde'] ?? '');
    $hasta = trim($_GET['hasta'] ?? '');

    $whereFecha = '';
    $paramsFecha = [];
    if ($desde !== '') {
        $whereFecha .= " AND v.fecha >= ? ";
        $paramsFecha[] = $desde . ' 00:00:00';
    }
    if ($hasta !== '') {
        $whereFecha .= " AND v.fecha <= ? ";
        $paramsFecha[] = $hasta . ' 23:59:59';
    }

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

WHERE 1=1 $whereFecha

ORDER BY v.fecha DESC;


";



    $stmt = $conexion->prepare($sql);
    $stmt->execute($paramsFecha);
    $ventas = $stmt->fetchAll();
    ?>

    <link rel="stylesheet" href="./historial_ventas.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <div class="container mt-4">
        <h2 class="mb-3"><i class="fa-solid fa-clock-rotate-left"></i> Historial de Ventas</h2>

        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label class="form-label text-light mb-1">Desde</label>
                <input type="date" name="desde" class="form-control" value="<?= htmlspecialchars($desde) ?>">
            </div>
            <div class="col-auto">
                <label class="form-label text-light mb-1">Hasta</label>
                <input type="date" name="hasta" class="form-control" value="<?= htmlspecialchars($hasta) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>
                <a href="index.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>

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

            <td>$<?= number_format($row['total'],0,',','.') ?></td>
            <td>
                <button 
                    class="btn btn-warning btn-sm ver-detalle"
                    data-id="<?= $row['idVenta'] ?>"
                    data-fecha="<?= $row['fecha'] ?>"
                    data-vendedor="<?= $row['user_nombre'].' '.$row['user_apellido'] ?>"
                    data-metodo="<?= ucfirst($row['metodo_pago'] ?? 'Sin método') ?>"
data-comprobante="<?= ucfirst($row['tipo_comprobante'] ?? 'Sin comprobante') ?>"

                    data-total="<?= number_format($row['total'],0,',','.') ?>"
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
            <button class="btn btn-outline-light" id="btnImprimir" onclick="imprimirVenta()">
              <i class="fa-solid fa-print me-1"></i> Imprimir
            </button>

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
        pageLength: 10,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa-solid fa-file-excel me-1"></i>Excel',
                className: 'btn btn-success btn-sm',
                filename: 'Historial_Ventas_Motoshoppy',
                title: 'Motoshoppy — Historial de Ventas',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa-solid fa-file-pdf me-1"></i>PDF',
                className: 'btn btn-danger btn-sm',
                filename: 'Historial_Ventas_Motoshoppy',
                title: 'Motoshoppy — Historial de Ventas',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                customize: function(doc) {
                    doc.content[0].fontSize  = 16;
                    doc.content[0].bold      = true;
                    doc.content[0].color     = '#1e3a5f';
                    doc.content[0].alignment = 'center';
                    doc.content[0].margin    = [0, 0, 0, 4];
                    doc.content.splice(1, 0, {
                        text: 'Generado: ' + new Date().toLocaleDateString('es-AR'),
                        alignment: 'right', fontSize: 8,
                        color: '#666666', margin: [0, 0, 0, 10]
                    });
                    doc.styles.tableHeader = {
                        bold: true, fontSize: 9,
                        color: '#ffffff', fillColor: '#1e3a5f',
                        alignment: 'center'
                    };
                    const body = doc.content[2].table.body;
                    for (let i = 1; i < body.length; i++) {
                        body[i].forEach(cell => {
                            if (typeof cell === 'object') {
                                cell.fillColor = i % 2 === 0 ? '#eef2f7' : '#ffffff';
                                cell.fontSize  = 8;
                                cell.color     = '#222222';
                            }
                        });
                    }
                    doc.content[2].table.widths =
                        Array(doc.content[2].table.body[0].length).fill('*');
                }
            }
        ]
    });

    // ===========================================
    //  Ver detalle
    // ===========================================
    window.ventaActualId = null;
    window.ventaActualComprobante = '';

    $(document).on("click", ".ver-detalle", function(){

        $("#d_fecha").text($(this).data("fecha"));
        $("#d_vendedor").text($(this).data("vendedor"));
        $("#d_metodo").text($(this).data("metodo"));
        $("#d_comprobante").text($(this).data("comprobante"));
        $("#d_total").text("$" + $(this).data("total"));

        let idVenta = $(this).data("id");
        let esCancelada = $(this).closest("tr").hasClass("venta-cancelada");

        // Guardar para el botón Imprimir
        window.ventaActualId = idVenta;
        window.ventaActualComprobante = $(this).data("comprobante").toLowerCase();

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
// IMPRIMIR VENTA (ticket o factura según comprobante)
// ======================================================
function imprimirVenta() {
    if (!window.ventaActualId) return;

    const base = window.location.origin + '/motoshoppy';

    if (window.ventaActualComprobante.includes('factura')) {
        window.open(`${base}/ventas/generar_factura.php?id=${window.ventaActualId}&dir=`, '_blank');
    } else {
        window.open(`${base}/ventas/generar_ticket.php?id=${window.ventaActualId}`, '_blank');
    }
}

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
