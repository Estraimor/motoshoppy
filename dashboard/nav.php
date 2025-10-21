<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    $current_page = basename($_SERVER['PHP_SELF']); 
}

if (!isset($_SESSION['idusuario'])) {
    header("Location: /motoshoppy/login/login.php");
    exit;
}

// Simulamos el rol del usuario (lo ideal es traerlo de la BD al momento del login)
$rol = $_SESSION['rol'] ?? 'Administrador'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motoshoppy - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- css generalaes -->
    <link rel="stylesheet" href="/motoshoppy/dashboard/estilos_dashboard.css">
    <!-- <link rel="stylesheet" href="/motoshoppy/categorias/estilos_categorias.css"> -->
    <!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

</head>
<body>

<!-- ===== PANTALLA DE CARGA ===== -->
<div id="preloader">
    <div class="loader-content">
        <img src="/motoshoppy/imagenes/logo.png" class="loader-logo" alt="Cargando">
        <div class="spinner"></div>
        <p>Cargando...</p>
    </div>
</div>

<div class="wrapper">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="/motoshoppy/imagenes/logo.png" class="logo" alt="Logo">
            <h3>MotoShopp</h3>
        </div>

        <?php
$current_page = basename($_SERVER['PHP_SELF']);
$productos_pages = ['index.php', 'categorias.php', 'marcas.php', 'listar_productos.php'];
$is_productos_active = in_array($current_page, $productos_pages);
?>

<?php
$current_page = basename($_SERVER['PHP_SELF']);
$productos_pages = ['index.php', 'categorias.php', 'marcas.php', 'listar_productos.php'];
$is_productos_active = in_array($current_page, $productos_pages);
?>

<nav class="nav-links">
    <!-- DASHBOARD -->
    <a href="/motoshoppy/index1.php" class="<?= $current_page === 'index1.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-gauge"></i> Dashboard
    </a>

    <!-- === MENU PRODUCTOS CON SUBMENU === -->
    <div class="nav-item has-submenu <?= $is_productos_active ? 'active' : '' ?>">
        <button class="submenu-toggle">
            <i class="fa-solid fa-box"></i> Productos
            <i class="fa-solid fa-chevron-down chevron"></i>
        </button>

        <div class="submenu" <?= $is_productos_active ? 'style="display:flex;"' : '' ?>>
            <a href="/motoshoppy/categorias/index.php" class="<?= $current_page === 'categorias.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-tags"></i> Categorías
            </a>
            <a href="/motoshoppy/marcas/index.php" class="<?= $current_page === 'marcas.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-bookmark"></i> Marcas
            </a>
            <a href="/motoshoppy/productos/alta_productos.php" class="<?= $current_page === 'alta_productos.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-plus"></i> Crear Productos
            </a>
            <a href="/motoshoppy/productos/listar_productos.php" class="<?= $current_page === 'listar_productos.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-list"></i> Lista de Productos
            </a>
        </div>
    </div>

    <!-- OTRAS OPCIONES -->
    <a href="#" class="<?= $current_page === 'clientes.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-users"></i> Clientes
    </a>
    <a href="#" class="<?= $current_page === 'ventas.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-receipt"></i> Ventas
    </a>
    <a href="#" class="<?= $current_page === 'configuracion.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-gear"></i> Configuración
    </a>
</nav>



       <!-- ===== USUARIO ACTIVO ===== -->
<div class="sidebar-user">
    <button class="user-info toggle-profile">
        <i class="fa-solid fa-circle-user"></i>
        <div>
            <strong><?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?></strong><br>
            <small><?= htmlspecialchars($_SESSION['rol']); ?></small>
        </div>
        <i class="fa-solid fa-chevron-down chevron"></i>
    </button>

    <div class="user-panel">
        <p><strong>Nombre:</strong> <?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?></p>
        <p><strong>Rol:</strong> <?= htmlspecialchars($_SESSION['rol']); ?></p>
        <hr>
        <a href="#" class="btn-perfil"><i class="fa-solid fa-user-gear"></i> Editar Perfil</a>
        <a href="/motoshoppy/login/login.php?logout=1" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a>
    </div>
</div>



    </aside>

    <!-- ===== ÁREA DE TRABAJO ===== -->
    <main class="main-content">


    <script>
document.querySelector('.toggle-profile').addEventListener('click', function() {
    const panel = document.querySelector('.user-panel');
    const chevron = this.querySelector('.chevron');
    panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
    this.classList.toggle('active');
});
</script>


<!-- submenu script -->
 <script>
document.querySelectorAll('.submenu-toggle').forEach(toggle => {
    toggle.addEventListener('click', function() {
        const parent = this.closest('.nav-item');
        parent.classList.toggle('active');
    });
});
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

