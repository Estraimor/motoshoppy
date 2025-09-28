<?php
session_start();
require_once '../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = trim($_POST['clave'] ?? '');

    // echo $usuario;
    // echo $clave;
    // exit;

    if ($usuario && $clave) {
        $stmt = $conexion->prepare("SELECT idusuario, usuario, pass FROM usuario WHERE usuario = :usuario and pass = :pass LIMIT 1");
        $stmt->execute([':usuario' => $usuario, ':pass' => $clave]);
        $row = $stmt->fetch();

        $stmt = $conexion->prepare("
    SELECT u.idusuario, u.usuario, u.pass, u.nombre, u.apellido, r.nombre_rol 
    FROM usuario u
    INNER JOIN roles r ON u.roles_idroles = r.idroles
    WHERE u.usuario = :usuario AND u.pass = :pass
    LIMIT 1
");
$stmt->execute([':usuario' => $usuario, ':pass' => $clave]);
$row = $stmt->fetch();

if ($row) {
    $_SESSION['idusuario'] = $row['idusuario'];
    $_SESSION['usuario']   = $row['usuario'];
    $_SESSION['nombre']    = $row['nombre'];
    $_SESSION['apellido']  = $row['apellido'];
    $_SESSION['rol']       = $row['nombre_rol']; // 🔑 ahora guardamos el rol
    header("Location: ../index1.php");
    exit;
} else {
    $error = "Usuario o contraseña incorrectos";
}


    } else {
        $error = "Por favor completa todos los campos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MotoShopp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="./stilos_login.css" rel="stylesheet">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <img src="../imagenes/logo.png" class="logo" alt="Logo MotoShop">
        <h2><i class="fa-solid fa-motorcycle"></i> MotoShopp</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger p-2 text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required>
            </div>
            <div class="mb-3 position-relative">
                <label for="clave" class="form-label">Contraseña</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="clave" name="clave" required>
                    <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-login w-100 text-white">
                <i class="fa-solid fa-right-to-bracket"></i> Ingresar
            </button>
        </form>
    </div>
</div>

<script>
document.querySelector('.toggle-password').addEventListener('click', function () {
    const input = document.getElementById('clave');
    const icon = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>

</body>
</html>
