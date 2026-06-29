<?php
require_once __DIR__ . '/../login/session_bootstrap.php';

$nombreUsuario   = $_SESSION['nombre']   ?? 'Usuario';
$apellidoUsuario = $_SESSION['apellido'] ?? '';
$rolesArray      = $_SESSION['roles']    ?? [];
$rol             = !empty($rolesArray) ? implode(', ', $rolesArray) : 'Sin rol';
$avatarSesion    = $_SESSION['avatar']   ?? '';

function tieneRol($roles, $rolBuscado) {
  return in_array($rolBuscado, $roles);
}

$uri  = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$base = BASE_URL;

$is_dashboard_active     = ($uri === "$base/index1.php");
$is_analisis_active      = str_starts_with($uri, "$base/analisis_comercial");
$is_productos_lista      = str_starts_with($uri, "$base/productos/listar");
$is_productos_alta       = str_starts_with($uri, "$base/productos/alta");
$is_categorias_active    = str_starts_with($uri, "$base/categorias");
$is_marcas_active        = str_starts_with($uri, "$base/marcas");
$is_movimientos_active   = str_starts_with($uri, "$base/movimientos_stock");
$is_reponer_stock_active = str_starts_with($uri, "$base/reponer_stock");
$is_inventario_active    = $is_productos_lista || $is_productos_alta || $is_categorias_active
                         || $is_marcas_active  || $is_movimientos_active || $is_reponer_stock_active;
$is_proveedores_active   = str_starts_with($uri, "$base/proveedores");
$is_punto_venta_active   = str_starts_with($uri, "$base/ventas/index");
$is_historial_active     = str_starts_with($uri, "$base/historial_ventas");
$is_ventas_active        = $is_punto_venta_active || $is_historial_active;
$is_config_active        = str_starts_with($uri, "$base/settings") || str_starts_with($uri, "$base/configuracion");
$is_carrito_active       = str_contains($uri, "$base/ventas/carrito.php");
$is_clientes_active      = str_starts_with($uri, "$base/clientes");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Motoshoppy</title>
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/imagenes/logo_motosshoppy.png">
  <link rel="shortcut icon" type="image/png" href="<?= BASE_URL ?>/imagenes/logo_motosshoppy.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/dashboard/estilos_dashboard.css">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
</head>
<body>

<!-- HAMBURGER (mobile) -->
<button class="hamburger-btn" id="hamburgerBtn" aria-label="Menú">
  <i class="fa-solid fa-bars"></i>
</button>

<!-- OVERLAY (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- PRELOADER -->
<div id="preloader">
  <div class="loader-content">
    <img src="<?= BASE_URL ?>/imagenes/logo.png" class="loader-logo">
    <div class="spinner"></div>
    <p>Cargando...</p>
  </div>
</div>

<div class="wrapper">

  <aside class="sidebar">

    <div class="sidebar-header">
      <img src="<?= BASE_URL ?>/imagenes/logo.png" class="logo">
      <h3>MotoShopp</h3>
    </div>

    <nav class="nav-links">

      <!-- DASHBOARD -->
      <a href="<?= BASE_URL ?>/index1.php" class="<?= $is_dashboard_active ? 'active' : '' ?>">
        <i class="fa-solid fa-gauge"></i> Dashboard
      </a>

      <!-- ANÁLISIS (ADMIN + VENTAS) -->
      <?php if (tieneRol($rolesArray, 'Administrador') || tieneRol($rolesArray, 'Ventas')): ?>
      <a href="<?= BASE_URL ?>/analisis_comercial/index.php" class="<?= $is_analisis_active ? 'active' : '' ?>">
        <i class="fa-solid fa-chart-line"></i> Análisis Comercial
      </a>
      <?php endif; ?>

      <!-- INVENTARIO (ADMIN + REPONEDOR) -->
      <?php if (tieneRol($rolesArray, 'Administrador') || tieneRol($rolesArray, 'Reponedor')): ?>
      <div class="nav-item has-submenu <?= $is_inventario_active ? 'active' : '' ?>">
        <button class="submenu-toggle">
          <i class="fa-solid fa-warehouse"></i> Inventario
          <i class="fa-solid fa-chevron-down chevron"></i>
        </button>
        <div class="submenu" style="<?= $is_inventario_active ? 'display:flex' : '' ?>">
          <a href="<?= BASE_URL ?>/productos/listar_productos.php" class="<?= $is_productos_lista ? 'active' : '' ?>">
            <i class="fa-solid fa-box"></i> Productos
          </a>
          <?php if (tieneRol($rolesArray, 'Administrador')): ?>
          <a href="<?= BASE_URL ?>/productos/alta_productos.php" class="<?= $is_productos_alta ? 'active' : '' ?>">
            <i class="fa-solid fa-plus"></i> Crear Producto
          </a>
          <?php endif; ?>
          <a href="<?= BASE_URL ?>/categorias/index.php" class="<?= $is_categorias_active ? 'active' : '' ?>">
            <i class="fa-solid fa-tags"></i> Categorías
          </a>
          <a href="<?= BASE_URL ?>/marcas/index.php" class="<?= $is_marcas_active ? 'active' : '' ?>">
            <i class="fa-solid fa-bookmark"></i> Marcas
          </a>
          <a href="<?= BASE_URL ?>/movimientos_stock/index.php" class="<?= $is_movimientos_active ? 'active' : '' ?>">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> Movimiento de Stock
          </a>
          <a href="<?= BASE_URL ?>/reponer_stock/index.php" class="<?= $is_reponer_stock_active ? 'active' : '' ?>">
            <i class="fa-solid fa-boxes-stacked"></i> Reponer Stock
          </a>
          <a href="<?= BASE_URL ?>/settings/ubicaciones/index.php" class="<?= str_starts_with($uri, "$base/settings/ubicaciones") ? 'active' : '' ?>">
            <i class="fa-solid fa-map-pin"></i> Ubicaciones
          </a>
        </div>
      </div>
      <?php endif; ?>

      <!-- PROVEEDORES (ADMIN + REPONEDOR) -->
      <?php if (tieneRol($rolesArray, 'Administrador') || tieneRol($rolesArray, 'Reponedor')): ?>
      <div class="nav-item has-submenu <?= $is_proveedores_active ? 'active' : '' ?>">
        <button class="submenu-toggle">
          <i class="fa-solid fa-truck-field"></i> Proveedores
          <i class="fa-solid fa-chevron-down chevron"></i>
        </button>
        <div class="submenu" style="<?= $is_proveedores_active ? 'display:flex' : '' ?>">
          <a href="<?= BASE_URL ?>/proveedores/index.php">
            <i class="fa-solid fa-address-book"></i> Ver Proveedores
          </a>
        </div>
      </div>
      <?php endif; ?>

      <!-- CLIENTES (ADMIN + VENTAS) -->
      <?php if (tieneRol($rolesArray, 'Administrador') || tieneRol($rolesArray, 'Ventas')): ?>
      <a href="<?= BASE_URL ?>/clientes/index.php" class="<?= $is_clientes_active ? 'active' : '' ?>">
        <i class="fa-solid fa-users"></i> Clientes
      </a>
      <?php endif; ?>

      <!-- VENTAS (ADMIN + VENTAS) -->
      <?php if (tieneRol($rolesArray, 'Administrador') || tieneRol($rolesArray, 'Ventas')): ?>
      <div class="nav-item has-submenu <?= $is_ventas_active ? 'active' : '' ?>">
        <button class="submenu-toggle">
          <i class="fa-solid fa-receipt"></i> Ventas
          <i class="fa-solid fa-chevron-down chevron"></i>
        </button>
        <div class="submenu" style="<?= $is_ventas_active ? 'display:flex' : '' ?>">
          <a href="<?= BASE_URL ?>/ventas/index.php" class="<?= $is_punto_venta_active ? 'active' : '' ?>">
            <i class="fa-solid fa-cash-register"></i> Punto de Venta
          </a>
          <a href="<?= BASE_URL ?>/historial_ventas/index.php" class="<?= $is_historial_active ? 'active' : '' ?>">
            <i class="fa-solid fa-clock-rotate-left"></i> Historial
          </a>
        </div>
      </div>
      <?php endif; ?>

      <!-- CONFIG (SOLO ADMIN) -->
      <?php if (tieneRol($rolesArray, 'Administrador')): ?>
      <div class="nav-item has-submenu <?= $is_config_active ? 'active' : '' ?>">
        <button class="submenu-toggle">
          <i class="fa-solid fa-gear"></i> Configuración
          <i class="fa-solid fa-chevron-down chevron"></i>
        </button>
        <div class="submenu" style="<?= $is_config_active ? 'display:flex' : '' ?>">
          <a href="<?= BASE_URL ?>/settings/index.php" class="<?= str_starts_with($uri, "$base/settings/index") ? 'active' : '' ?>">
            <i class="fa-solid fa-wrench"></i> Ajustes
          </a>
          <a href="<?= BASE_URL ?>/settings/usuarios/index.php" class="<?= str_starts_with($uri, "$base/settings/usuarios") ? 'active' : '' ?>">
            <i class="fa-solid fa-user-gear"></i> Usuarios
          </a>
          <a href="<?= BASE_URL ?>/settings/roles.php" class="<?= str_starts_with($uri, "$base/settings/roles") ? 'active' : '' ?>">
            <i class="fa-solid fa-id-card"></i> Roles
          </a>
        </div>
      </div>
      <?php endif; ?>

      <!-- CARRITO (ADMIN + VENTAS) -->
      <?php if (tieneRol($rolesArray, 'Administrador') || tieneRol($rolesArray, 'Ventas')): ?>
      <a href="<?= BASE_URL ?>/ventas/carrito.php" class="nav-cart <?= $is_carrito_active ? 'active' : '' ?>">
        <i class="fa-solid fa-cart-shopping"></i>
        <span>Carrito</span>
        <span id="cartCountSide" class="cart-badge">0</span>
      </a>
      <?php endif; ?>

      <!-- LOGOUT -->
      <a href="<?= BASE_URL ?>/login/cerrar_session.php" class="logout-link">
        <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
      </a>

    </nav>

    <div class="sidebar-user">
      <div class="user-info">
        <div class="user-avatar">
          <?php
            $avatarPath = __DIR__ . '/../' . $avatarSesion;
            if ($avatarSesion && file_exists($avatarPath)):
          ?>
            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($avatarSesion) ?>?v=<?= filemtime($avatarPath) ?>"
                 alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
          <?php else: ?>
            <i class="fa-solid fa-user"></i>
          <?php endif; ?>
        </div>
        <div class="user-data">
          <span class="user-name"><?= htmlspecialchars("$nombreUsuario $apellidoUsuario") ?></span>
          <span class="user-role"><?= htmlspecialchars($rol) ?></span>
        </div>
      </div>
      <a href="<?= BASE_URL ?>/perfil/index.php" class="user-profile-link">
        <i class="fa-solid fa-id-badge"></i> Ver perfil
      </a>
    </div>

  </aside>

  <main class="main-content">

    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.querySelector('.toggle-profile');
        if (!toggle) return;
        toggle.addEventListener('click', function() {
          const panel = document.querySelector('.user-panel');
          panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
          this.classList.toggle('active');
        });
      });
    </script>

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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script>
      // === HAMBURGER MENU MOBILE ===
      document.addEventListener('DOMContentLoaded', () => {
        const btn     = document.getElementById('hamburgerBtn');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (!btn) return;

        function openSidebar() {
          sidebar.classList.add('open');
          overlay.classList.add('active');
          btn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
        }
        function closeSidebar() {
          sidebar.classList.remove('open');
          overlay.classList.remove('active');
          btn.innerHTML = '<i class="fa-solid fa-bars"></i>';
        }

        btn.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
        overlay.addEventListener('click', closeSidebar);
      });
    </script>

    <script>
      document.addEventListener("DOMContentLoaded", () => {
        const cartCountSide = document.getElementById("cartCountSide");
        function actualizarContadores() {
          const carrito = JSON.parse(localStorage.getItem("carrito")) || [];
          const total = carrito.reduce((acc, p) => acc + (p.cantidad || 1), 0);
          if (cartCountSide) cartCountSide.textContent = total > 0 ? total : "0";
        }
        window.addEventListener("storage", (e) => { if (e.key === "carrito") actualizarContadores(); });
        actualizarContadores();
      });
    </script>
