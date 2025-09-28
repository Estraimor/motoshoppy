<?php
include './dashboard/nav.php';
?>

<div class="content-header d-flex justify-content-between align-items-center">
    <h2>Bienvenido, <?= htmlspecialchars($_SESSION['nombre']); ?> 👋</h2>
    <small class="text-muted">Hoy es <?= date('d/m/Y'); ?></small>
</div>

<div class="content-body">
    
    <!-- === ALERTAS MOTIVACIONALES === -->
    <div class="alert alert-info shadow-sm">
        <i class="fa-solid fa-lightbulb"></i> Consejo del día: <strong>¡Mantén tu stock actualizado!</strong> Una buena gestión de inventario evita pérdidas.
    </div>

    <!-- === TARJETAS DE RESUMEN === -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-dark text-white shadow h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <i class="fa-solid fa-box fa-2x mb-2 text-warning"></i>
                    <h5 class="mb-0">15</h5>
                    <small>Categorías</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark text-white shadow h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <i class="fa-solid fa-bookmark fa-2x mb-2 text-success"></i>
                    <h5 class="mb-0">32</h5>
                    <small>Marcas registradas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark text-white shadow h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <i class="fa-solid fa-boxes-stacked fa-2x mb-2 text-info"></i>
                    <h5 class="mb-0">120</h5>
                    <small>Productos en stock</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark text-white shadow h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <i class="fa-solid fa-chart-line fa-2x mb-2 text-danger"></i>
                    <h5 class="mb-0">12</h5>
                    <small>Ventas hoy</small>
                </div>
            </div>
        </div>
    </div>

    <!-- === MENSAJE DE NOTICIAS === -->
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
        <i class="fa-solid fa-bullhorn"></i> <strong>Novedad:</strong> Próximamente podrás exportar reportes en PDF y Excel directamente desde el sistema.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- === MÓDULO PRINCIPAL === -->
    <div class="modulo mt-4">
        <h3>Panel Principal</h3>
        <p>Desde aquí podrás acceder a todas las herramientas de gestión: <strong>categorías, marcas, productos, ventas y más</strong>.</p>
        <p class="text-muted">Haz clic en el menú lateral para empezar a trabajar.</p>
    </div>

</div>

<?php
include './dashboard/footer.php';
?>
