<?php
require_once '../conexion/conexion.php';
include '../dashboard/nav.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.settings-card{
    border-radius:16px;
    padding:30px;
    cursor:pointer;
    transition:.2s;
    background:#111;
    color:white;
    border:1px solid #222;
}
.settings-card:hover{
    transform:translateY(-5px);
    border-color:#ffc107;
    box-shadow:0 10px 30px rgba(255,193,7,.15);
}
.icon{
    font-size:40px;
    margin-bottom:10px;
}
</style>

<div class="container py-5">

    <h2 class="mb-4 fw-bold">
        ⚙ Configuración del sistema
    </h2>

    <div class="row g-4">

        <!-- Usuarios -->
        <div class="col-md-4">
            <a href="./usuarios/index.php" class="text-decoration-none">
                <div class="settings-card text-center">
                    <div class="icon">👤</div>
                    <h5>Usuarios</h5>
                    <small>Administrar cuentas</small>
                </div>
            </a>
        </div>

        <!-- Roles -->
        <div class="col-md-4">
            <a href="./roles.php" class="text-decoration-none">
                <div class="settings-card text-center">
                    <div class="icon">🛡️</div>
                    <h5>Roles y permisos</h5>
                    <small>Control de accesos</small>
                </div>
            </a>
        </div>

        <!-- Auditoría -->
        <div class="col-md-4">
            <a href="./auditoria/ver_auditoria.php" class="text-decoration-none">
                <div class="settings-card text-center">
                    <div class="icon">📜</div>
                    <h5>Auditoría</h5>
                    <small>Historial del sistema</small>
                </div>
            </a>
        </div>

        <!-- Cotización -->
        <div class="col-md-4">
            <a href="cotizacion.php" class="text-decoration-none">
                <div class="settings-card text-center">
                    <div class="icon">💱</div>
                    <h5>Cotización</h5>
                    <small>Dólar / Guaraní / ARS</small>
                </div>
            </a>
        </div>

        <!-- Parámetros -->
        <div class="col-md-4">
            <a href="descuentos/index.php" class="text-decoration-none">
                <div class="settings-card text-center">
                    <div class="icon">⚙️</div>
                    <h5>Descuentos</h5>
                    <small>Porcentajes de descuentos </small>
                </div>
            </a>
        </div>

        <!-- Backup -->
        <div class="col-12 col-md-4">
    <a href="../backups/exportar_productos_excel.php" 
       class="text-decoration-none d-block">

        <div class="settings-card text-center p-4 h-100">
            
            <div class="icon mb-3 fs-1">
                💾
            </div>

            <h5 class="fw-semibold mb-1">
                Backups
            </h5>

            <small class="text-mb-1">
                Exportar base de datos
            </small>

        </div>
    </a>
</div>

    </div>
</div>

<?php include '../dashboard/footer.php'; ?>
