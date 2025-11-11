<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';

// Consulta usando PDO
$sql = "SELECT v.idVenta, v.fecha, v.total, v.metodo_pago, v.tipo_comprobante,
               u.nombre AS user_nombre, u.apellido AS user_apellido
        FROM ventas v
        LEFT JOIN usuario u ON v.usuario_id = u.idusuario
        ORDER BY v.fecha DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$ventas = $stmt->fetchAll();
?>

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
                <tr>
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

<!-- MODAL DETALLE -->
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

<script>
$(document).ready(() => {

    $('#tablaHistorial').DataTable({
        order: [[1, 'desc']],
        pageLength: 10
    });

    $(document).on("click", ".ver-detalle", function(){

        $("#d_fecha").text($(this).data("fecha"));
        $("#d_vendedor").text($(this).data("vendedor"));
        $("#d_metodo").text($(this).data("metodo"));
        $("#d_comprobante").text($(this).data("comprobante"));
        $("#d_total").text("$" + $(this).data("total"));

        let idVenta = $(this).data("id");

        $.post("obtener_detalle.php", { idVenta }, function(data){
            $("#detalleContenido").html(data);
            $("#modalDetalle").modal("show");

            $("#btnCancelarVentaContainer").html(
                `<button class="btn btn-danger" id="btnCancelarVenta" data-id="${idVenta}">
                    ❌ Cancelar Venta Completa
                </button>`
            );

            $("#btnDevolverParcialContainer").html(
                `<button class="btn btn-primary" id="btnDevolverParcial" data-id="${idVenta}">
                    🔄 Devolución Parcial
                </button>`
            );
        });

    });

});

// Cancelar Venta Completa
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

// Devolución Parcial
$(document).on("click", "#btnDevolverParcial", function(){
    let id = $(this).data("id");

    $.post("obtener_detalle_select.php", {idVenta:id}, function(html){
        Swal.fire({
            title: "Devolución Parcial",
            html: html + `
                <br><input type='number' id='dp_cant' class='form-control' placeholder='Cantidad a devolver'>
                <br><textarea id='dp_motivo' class='form-control' placeholder='Motivo'></textarea>
            `,
            showCancelButton:true,
            confirmButtonText:"Confirmar"
        }).then(r=>{
            if(r.isConfirmed){
                let prod = $("#selectProducto").val();
                let cant = $("#dp_cant").val();
                let mot = $("#dp_motivo").val();

                $.post("devolucion_parcial.php",{idVenta:id, producto_id:prod, cantidad:cant, motivo:mot}, function(resp){
                    Swal.fire("✅ Devolución Registrada","","success").then(()=>location.reload());
                });
            }
        });
    });
});
</script>

<?php include '../dashboard/footer.php'; ?>
