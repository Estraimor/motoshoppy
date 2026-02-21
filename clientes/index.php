<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';

/* ===============================
   RESUMEN GENERAL
================================= */

$totalClientes = $conexion->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$totalVentas   = $conexion->query("SELECT COUNT(*) FROM ventas")->fetchColumn();

/* ===============================
   LISTADO CLIENTES
================================= */

$stmt = $conexion->prepare("
    SELECT 
        c.idCliente,
        c.apellido,
        c.nombre,
        c.dni,
        c.celular,
        c.email,
        c.fecha_alta,
        COUNT(v.idVenta) AS cantidad_compras,
        IFNULL(SUM(v.total),0) AS total_gastado
    FROM clientes c
    LEFT JOIN ventas v 
        ON v.clientes_idCliente = c.idCliente
    GROUP BY c.idCliente
    ORDER BY cantidad_compras DESC
");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="clientes.css">
<div class="clientes-wrapper fade-in">
    
    <div class="clientes-header">
        <h2><i class="fa-solid fa-users"></i> Gestión Inteligente de Clientes</h2>
    </div>

    <!-- RESUMEN CARDS -->
    <div class="clientes-stats">
        <div class="stat-card">
            <h4>Total Clientes</h4>
            <span><?= $totalClientes ?></span>
        </div>

        <div class="stat-card success">
            <h4>Total Ventas</h4>
            <span><?= $totalVentas ?></span>
        </div>
    </div>

    <!-- TABLA -->
    <div class="clientes-table-container">
        <table id="tablaClientes" class="table table-dark table-hover align-middle text-center">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>DNI</th>
                    <th>Compras</th>
                    <th>Total Gastado</th>
                    <th>Insight</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($clientes as $c): ?>
                    <tr>
                        <td><?= $c['apellido'].' '.$c['nombre'] ?></td>
                        <td><?= $c['dni'] ?></td>
                        <td>
                            <span class="badge bg-info">
                                <?= $c['cantidad_compras'] ?>
                            </span>
                        </td>
                        <td class="text-success fw-bold">
                            $<?= number_format($c['total_gastado'],2,',','.') ?>
                        </td>
                        <td>
                            <button 
                                class="btn btn-sm btn-outline-warning btnInsight"
                                data-id="<?= $c['idCliente'] ?>"
                                data-nombre="<?= $c['apellido'].' '.$c['nombre'] ?>"
                            >
                                Ver qué compra
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>


<div class="modal fade" id="modalInsight" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title">Análisis del Cliente</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalInsightBody">
        Cargando análisis...
      </div>
    </div>
  </div>
</div>

<script>
    $(document).on('click','.btnInsight',function(){

    let cliente = $(this).data('id');
    let nombre  = $(this).data('nombre');

    $('#modalInsight').modal('show');
    $('#modalInsightBody').html("Analizando comportamiento de compra...");

    $.post('api_cliente_insight.php',{cliente:cliente},function(data){

        let res = JSON.parse(data);

        if(res){
            $('#modalInsightBody').html(`
                <h4>${nombre}</h4>
                <p>Producto más comprado:</p>
                <h3 class="text-warning">${res.nombre}</h3>
                <p>Comprado ${res.veces} veces</p>
            `);
        } else {
            $('#modalInsightBody').html("Este cliente aún no tiene historial suficiente.");
        }

    });

});

</script>

<script>
    $(document).ready(function(){

    $('#tablaClientes').DataTable({
        responsive: true,
        pageLength: 5,
        lengthChange: true,
        autoWidth: false,
        order: [[2, 'desc']], // ordenar por compras
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ clientes",
            info: "Mostrando _START_ a _END_ de _TOTAL_ clientes",
            paginate: {
                previous: "Anterior",
                next: "Siguiente"
            }
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: 'Exportar Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: 'Exportar PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: 'Imprimir',
                className: 'btn btn-info btn-sm'
            }
        ]
    });

});

</script>

<?php include '../dashboard/footer.php'; ?>
