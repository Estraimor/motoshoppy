<?php
require_once '../conexion/conexion.php';

$idVenta = $_POST['idVenta'];
$modo = $_POST['modo'] ?? 'view'; // view = normal, select = con checkbox

$sql = "
SELECT 
    dv.idDetalle, 
    dv.producto_id, 
    dv.cantidad, 
    dv.precio_unitario, 
    dv.subtotal,
    dv.devuelto,

    -- 🔥 Verifica si este producto tiene devoluciones reales
    (
        SELECT COUNT(*) 
        FROM devoluciones_venta dvv
        WHERE dvv.venta_id = dv.venta_id
          AND dvv.producto_id = dv.producto_id   -- ✔ CORREGIDO
    ) AS devuelto_real,

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
";

$stmt = $conexion->prepare($sql);
$stmt->bindParam(':idVenta', $idVenta, PDO::PARAM_INT);
$stmt->execute();
$detalles = $stmt->fetchAll();
?>

<style>
tr.devuelto-parcial,
tr.devuelto-parcial td,
.table-dark tr.devuelto-parcial td {
    background-color: #a37f00 !important;
    color: #fff !important;
}
</style>

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
        </tr>
    </thead>

    <tbody>
    <?php foreach ($detalles as $row): ?>

        <?php
        // 🔥 Regla final: Se pinta solo si devuelto=1 Y tiene devoluciones registradas
        $pintar = ($row['devuelto'] == 1 && $row['devuelto_real'] > 0);
        ?>

        <tr class="<?= $pintar ? 'devuelto-parcial' : '' ?>">

            <?php if ($modo === 'select'): ?>
            <td class="text-center">
                <input 
                    type="checkbox" 
                    class="chkDevolver"
                    data-id="<?= $row['idDetalle'] ?>"
                    data-producto="<?= $row['producto_id'] ?>"
                    data-cant="<?= $row['cantidad'] ?>"

                    <?= $pintar ? 'disabled readonly checked onclick="return false;"' : '' ?>
                >
            </td>
            <?php endif; ?>

            <td><?= $row['nombre'] ?></td>
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
        </tr>

    <?php endforeach; ?>
    </tbody>
</table>
