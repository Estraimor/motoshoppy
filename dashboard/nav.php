<?php
require_once __DIR__ . '/../login/session_bootstrap.php';

// Si querés leer el rol para mostrar distinto menú:
$rol = $_SESSION['rol'] ?? 'Administrador';
?>

<?php
$current_page = basename($_SERVER['PHP_SELF']);
$uri = $_SERVER['REQUEST_URI'] ?? '';

// === GRUPOS DE PÁGINAS PARA ACTIVAR SUBMENÚ ===
$productos_pages = ['categorias.php', 'marcas.php', 'alta_productos.php', 'listar_productos.php'];
$ventas_pages = ['index.php', 'carrito.php', 'historial.php', 'detalle_venta.php'];
$config_pages = ['index.php', 'usuarios.php', 'roles.php', 'ajustes.php'];
$proveedores_pages = ['index.php'];

// === CHECK ACTIVE ===
$is_productos_active = array_reduce($productos_pages, fn($carry, $p) => $carry || strpos($uri, "/motoshoppy/$p") !== false, false);
$is_ventas_active = strpos($uri, "/motoshoppy/ventas/") !== false;
$is_config_active = strpos($uri, "/motoshoppy/configuracion/") !== false;
$is_proveedores_active = strpos($uri, "/motoshoppy/proveedores/") !== false;
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css">

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
        $productos_pages = ['index.php', 'categorias.php', 'marcas.php', 'alta_productos.php', 'listar_productos.php'];
        $is_productos_active = in_array($current_page, $productos_pages);
        ?>

<nav class="nav-links">
    <!-- DASHBOARD -->
    <a href="/motoshoppy/index1.php" class="<?= $current_page === 'index1.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-gauge"></i> Dashboard
    </a>

    <!-- === PRODUCTOS (SUBMENÚ) === -->
    <div class="nav-item has-submenu <?= $is_productos_active ? 'active' : '' ?>">
        <button class="submenu-toggle">
            <i class="fa-solid fa-box"></i> Productos
            <i class="fa-solid fa-chevron-down chevron"></i>
        </button>

        <div class="submenu" style="<?= $is_productos_active ? 'display:flex;' : 'display:none;' ?>">
            <a href="/motoshoppy/categorias/index.php"><i class="fa-solid fa-tags"></i> Categorías</a>
            <a href="/motoshoppy/marcas/index.php"><i class="fa-solid fa-bookmark"></i> Marcas</a>
            <a href="/motoshoppy/productos/alta_productos.php"><i class="fa-solid fa-plus"></i> Crear Productos</a>
            <a href="/motoshoppy/productos/listar_productos.php"><i class="fa-solid fa-list"></i> Lista de Productos</a>
            
        </div>
    </div>


    <!-- === PROVEEDORES (SUBMENÚ) === -->
<div class="nav-item has-submenu <?= $is_proveedores_active ? 'active' : '' ?>">
    <button class="submenu-toggle">
        <i class="fa-solid fa-truck-field"></i> Proveedores
        <i class="fa-solid fa-chevron-down chevron"></i>
    </button>

    <div class="submenu" style="<?= $is_proveedores_active ? 'display:flex;' : 'display:none;' ?>">
        <a href="/motoshoppy/proveedores/index.php">
            <i class="fa-solid fa-address-book"></i> Ver Proveedores
        </a>
        <a href="/motoshoppy/reponer_stock/index.php"><i class="fa-solid fa-boxes-stacked"></i> Reponer stock</a>
        <a href="/motoshoppy/productos/#"><i class="fa-solid fa-right-left"></i> Movimiento Stock</a>

    </div>
</div>

    <!-- === CLIENTES (SE QUEDA NORMAL) === -->
    <a href="/motoshoppy/clientes/index.php" class="<?= $current_page === 'clientes.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-users"></i> Clientes
    </a>

    <!-- === VENTAS (SUBMENÚ) === -->
    <?php $is_ventas_active = strpos($uri, '/motoshoppy/ventas/') !== false && strpos($uri, 'carrito.php') === false; ?>
    <div class="nav-item has-submenu <?= $is_ventas_active ? 'active' : '' ?>">
        <button class="submenu-toggle">
            <i class="fa-solid fa-receipt"></i> Ventas
            <i class="fa-solid fa-chevron-down chevron"></i>
        </button>

        <div class="submenu" style="<?= $is_ventas_active ? 'display:flex;' : 'display:none;' ?>">
            <a href="/motoshoppy/ventas/index.php"><i class="fa-solid fa-cash-register"></i> Punto de Venta</a>
            <a href="/motoshoppy/historial_ventas/index.php"><i class="fa-solid fa-clock-rotate-left"></i> Historial</a>
        </div>
    </div>

    <!-- === CONFIGURACIÓN (SUBMENÚ) === -->
    <?php $is_config_active = strpos($uri, '/motoshoppy/configuracion/') !== false; ?>
    <div class="nav-item has-submenu <?= $is_config_active ? 'active' : '' ?>">
        <button class="submenu-toggle">
            <i class="fa-solid fa-gear"></i> Configuración
            <i class="fa-solid fa-chevron-down chevron"></i>
        </button>

        <div class="submenu" style="<?= $is_config_active ? 'display:flex;' : 'display:none;' ?>">
            <a href="/motoshoppy/configuracion/index.php"><i class="fa-solid fa-wrench"></i> Ajustes</a>
            <a href="/motoshoppy/configuracion/usuarios.php"><i class="fa-solid fa-user-gear"></i> Usuarios</a>
            <a href="/motoshoppy/configuracion/roles.php"><i class="fa-solid fa-id-card"></i> Roles</a>
        </div>
    </div>

    <!-- === CARRITO (SE QUEDA AFUERA Y DESTACADO) === -->
    <?php $is_carrito_active = strpos($uri, '/motoshoppy/ventas/carrito.php') !== false; ?>
    <a href="/motoshoppy/ventas/carrito.php" class="nav-cart <?= $is_carrito_active ? 'active' : '' ?>">
       <i class="fa-solid fa-cart-shopping"></i> 
       <span>Carrito</span>
       <span id="cartCountSide" class="cart-badge">0</span>
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
        <a href="/motoshoppy/login/cerrar_session.php?logout=1" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a>
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
        const submenu = parent.querySelector('.submenu');
        parent.classList.toggle('active');
        submenu.style.display = submenu.style.display === 'flex' ? 'none' : 'flex';
    });
});
</script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Cargar Select2 DESPUÉS de jQuery para evitar errores y permitir scroll en dropdown -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const cartCountTop = document.getElementById("cartCountTop");
    const cartCountSide = document.getElementById("cartCountSide");

    function actualizarContadores() {
        const carrito = JSON.parse(localStorage.getItem("carrito")) || [];
        const total = carrito.reduce((acc, p) => acc + (p.cantidad || 1), 0);
        [cartCountTop, cartCountSide].forEach(el => {
            if (el) el.textContent = total > 0 ? total : "0";
        });
    }

    window.addEventListener("storage", (e) => {
        if (e.key === "carrito") actualizarContadores();
    });

    actualizarContadores();
});
</script>
