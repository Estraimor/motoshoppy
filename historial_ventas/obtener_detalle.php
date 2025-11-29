<?php
require_once '../conexion/conexion.php';

// Asegura que llegue como número limpio
$idVenta = intval(trim($_POST['idVenta']));
$modo = $_POST['modo'] ?? 'view';

/* ==========================================
   CONSULTA PRINCIPAL + ID DE DEVOLUCIÓN
========================================== */
$sql = "
SELECT 
    dv.idDetalle, 
    dv.producto_id, 
    dv.cantidad, 
    dv.precio_unitario, 
    dv.subtotal,
    dv.devuelto,

    /* Buscar la devolución real si existe */
    (
        SELECT idDevolucion 
        FROM devoluciones_venta dvv 
        WHERE dvv.venta_id = dv.venta_id
          AND dvv.producto_id = dv.producto_id
        LIMIT 1
    ) AS idDevolucionReal,

    p.nombre, 
    p.modelo,
    m.nombre_marca,

    u.lugar AS ubicacion_lugar, 
    u.estante AS ubicacion_estante

FROM detalle_venta dv
INNER JOIN producto p ON dv.producto_id = p.idProducto
LEFT JOIN marcas m ON p.marcas_idmarcas = m.idmarcas
LEFT JOIN ubicacion_producto u 
       ON p.ubicacion_producto_idubicacion_producto = u.idubicacion_producto

WHERE dv.venta_id = :idVenta
ORDER BY dv.idDetalle ASC
";

$stmt = $conexion->prepare($sql);
$stmt->bindParam(':idVenta', $idVenta, PDO::PARAM_INT);
$stmt->execute();
$detalles = $stmt->fetchAll();
?>

<!-- ======================================================
     CONTENEDOR AISLADO PARA EVITAR CONFLICTO DE ESTILOS
======================================================= -->
<div class="detalle-venta-modal">

<table class="table table-dark table-striped table-bordered align-middle">
    <thead class="table-secondary text-dark">
        <tr>

            <?php if ($modo === 'select'): ?>
                <th style="width:50px;">✔</th>
            <?php endif; ?>

            <th>Producto</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Ubicación</th>

            <th class="text-center">Cant.</th>
            <th class="text-end">Unitario</th>
            <th class="text-end">Subtotal</th>

            <th class="text-center">Acciones</th>
        </tr>
    </thead>

    <tbody>

    <?php foreach ($detalles as $row): ?>
        <?php 
            $devuelto = ($row['devuelto'] == 1);
            $idDev = $row['idDevolucionReal'];
        ?>

        <tr>

            <!-- CHECK SOLO EN MODO SELECT -->
            <?php if ($modo === 'select'): ?>
                <td class="text-center">
                    <input 
                        type="checkbox" 
                        class="chkDevolver"
                        data-id="<?= $row['idDetalle'] ?>"
                        data-producto="<?= $row['producto_id'] ?>"
                        data-cant="<?= $row['cantidad'] ?>"
                        <?= $devuelto ? 'disabled readonly checked onclick="return false;"' : '' ?>
                    >
                </td>
            <?php endif; ?>

            <!-- PRODUCTO -->
            <td>
                <strong><?= $row['producto_id'] ?> - <?= $row['nombre'] ?></strong>

                <?php if ($devuelto): ?>
                    <span class="badge badge-devuelto ms-1">🔄 Devuelto</span>
                <?php endif; ?>
            </td>

            <td><?= $row['nombre_marca'] ?: '-' ?></td>
            <td><?= $row['modelo'] ?: '-' ?></td>

            <td>
                <?= $row['ubicacion_lugar'] 
                    ? $row['ubicacion_lugar'] . ' / Est. ' . $row['ubicacion_estante']
                    : '-' ?>
            </td>

            <td class="text-center"><?= $row['cantidad'] ?></td>

            <td class="text-end">
                $<?= number_format($row['precio_unitario'], 2, ',', '.') ?>
            </td>

            <td class="text-end">
                $<?= number_format($row['subtotal'], 2, ',', '.') ?>
            </td>

            <!-- COLUMNA ACCIONES -->
            <td class="text-center">

                <?php if ($devuelto && $idDev): ?>

                    <button 
                        class="btn btn-sm btn-cancelar-dev btnCancelarDevolucion"
                        data-iddev="<?= $idDev ?>"
                        data-producto="<?= $row['producto_id'] ?>"
                        data-idventa="<?= $idVenta ?>"
                    >
                        ❌ Cancelar
                    </button>

                <?php else: ?>
                    <span class="text-secondary">Sin acciones</span>
                <?php endif; ?>

            </td>

        </tr>
    <?php endforeach; ?>

    </tbody>
</table>

</div> <!-- cierre del contenedor aislado -->
