<?php
include './dashboard/nav.php';
?>

<div class="content-header">
    <h2>Bienvenido, <?= htmlspecialchars($_SESSION['nombre']); ?> 👋</h2>
</div>

<div class="content-body">
    <div class="modulo">
        <h3>Módulo de prueba</h3>
        <p>Este es el espacio de trabajo donde cargarás tus herramientas.</p>
    </div>
</div>

<?php
include './dashboard/footer.php';
?>
