<?php
require_once '../settings/bootstrap.php';
include '../dashboard/nav.php';

$logs = $conexion->query("
    SELECT a.*, u.nombre, u.apellido
    FROM auditoria a
    LEFT JOIN usuario u ON u.idusuario = a.usuario_id
    ORDER BY a.fecha DESC
    LIMIT 500
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">

    <h2 class="fw-bold mb-4">📜 Auditoría del sistema</h2>

    <div class="table-responsive">
        <table class="table table-dark table-striped table-hover">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Tabla</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($logs as $l): ?>
                <tr>
                    <td><?= $l['fecha'] ?></td>
                    <td><?= $l['nombre'].' '.$l['apellido'] ?></td>
                    <td><span class="badge bg-warning text-dark"><?= $l['accion'] ?></span></td>
                    <td><?= $l['tabla_afectada'] ?></td>
                    <td><?= $l['descripcion'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
