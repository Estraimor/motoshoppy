<?php
require_once '../conexion/conexion.php';

$idVenta = $_POST['idVenta'];

$sql = "SELECT dv.cantidad, dv.precio_unitario, dv.subtotal,
               p.nombre, p.modelo,
               m.nombre_marca,
               u.lugar AS ubicacion_lugar, u.estante AS ubicacion_estante
        FROM detalle_venta dv
        INNER JOIN producto p ON dv.producto_id = p.idProducto
        LEFT JOIN marcas m ON p.marcas_idmarcas = m.idmarcas
        LEFT JOIN ubicacion_producto u ON p.ubicacion_producto_idubicacion_producto = u.idubicacion_producto
        WHERE dv.venta_id = :idVenta";

$stmt = $conexion->prepare($sql);
$stmt->bindParam(':idVenta', $idVenta, PDO::PARAM_INT);
$stmt->execute();
$detalles = $stmt->fetchAll();
?>

<table class="table table-dark table-striped table-bordered align-middle">
    <thead class="table-secondary text-dark">
        <tr>
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
        <tr>
            <td><?= $row['nombre'] ?></td>
            <td><?= $row['nombre_marca'] ?: '-' ?></td>
            <td><?= $row['modelo'] ?: '-' ?></td>
            <td><?= $row['ubicacion_lugar'] ? $row['ubicacion_lugar'].' / Est. '.$row['ubicacion_estante'] : '-' ?></td>
            <td class="text-center"><?= $row['cantidad'] ?></td>
            <td class="text-end">$<?= number_format($row['precio_unitario'], 2, ',', '.') ?></td>
            <td class="text-end">$<?= number_format($row['subtotal'], 2, ',', '.') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
