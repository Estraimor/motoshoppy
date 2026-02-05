<?php
session_start();
require_once '../conexion/conexion.php';
require_once '../settings/auditoria.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = trim($_POST['clave'] ?? '');

    if ($usuario === '' || $clave === '') {
        $error = "Por favor completa todos los campos";
    } else {

        /* =========================
           1) BUSCAR USUARIO
        ========================= */
        $stmt = $conexion->prepare("
            SELECT 
                idusuario,
                nombre,
                apellido,
                dni,
                celular,
                usuario,
                pass
            FROM usuario
            WHERE usuario = :usuario
            LIMIT 1
        ");

        $stmt->execute([':usuario' => $usuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);


        /* ========= LOGIN SIMPLE (SIN HASH POR AHORA) ========= */

        if ($row && $row['pass'] === $clave) {

            session_regenerate_id(true);


            /* =========================
               2) CARGAR ROLES (N:N)
            ========================= */
            $rolesStmt = $conexion->prepare("
                SELECT r.idroles, r.nombre_rol
                FROM usuario_roles ur
                INNER JOIN roles r ON r.idroles = ur.rol_id
                WHERE ur.usuario_id = ?
            ");
            $rolesStmt->execute([$row['idusuario']]);

            $roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

            $rolesNombres = array_column($roles, 'nombre_rol');
            $rolesIds     = array_column($roles, 'idroles');


            /* =========================
               3) SESIÓN
            ========================= */
            $_SESSION['idusuario'] = $row['idusuario'];
            $_SESSION['usuario']   = $row['usuario'];
            $_SESSION['nombre']    = $row['nombre'];
            $_SESSION['apellido']  = $row['apellido'];
            $_SESSION['dni']       = $row['dni'];
            $_SESSION['celular']   = $row['celular'];

            // 👇 NUEVO SISTEMA
            $_SESSION['roles']     = $rolesNombres; // ['Administrador','Ventas']
            $_SESSION['roles_id']  = $rolesIds;

            $_SESSION['LAST_ACTIVITY'] = time();
            $_SESSION['CREATED']       = time();


            /* =========================
               AUDITORÍA
            ========================= */
            auditoria(
                $conexion,
                "LOGIN",
                "auth",
                null,
                null,
                "Inicio de sesión del usuario " . $row['usuario']
            );


            header("Location: ../index1.php");
            exit;

        } else {
            $error = "Usuario o contraseña incorrectos";
        }
    }
}
?>




<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MotoShoppy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="./stilos_login.css" rel="stylesheet">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <img src="../imagenes/logo.png" class="logo" alt="Logo MotoShoppy">
        <h2><i class="fa-solid fa-motorcycle"></i> MotoShoppy</h2>

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
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});
</script>

</body>
</html>
