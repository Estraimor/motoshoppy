<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';

$nombreUsuario   = $_SESSION['nombre']   ?? 'Usuario';
$apellidoUsuario = $_SESSION['apellido'] ?? '';
$rol             = $_SESSION['rol']      ?? 'Sin rol';
$usuario         = $_SESSION['usuario']  ?? '';
$dni             = $_SESSION['dni']      ?? '';
$celular         = $_SESSION['celular']  ?? '';
$avatar          = $_SESSION['avatar']   ?? '';
?>

<link rel="stylesheet" href="./perfil_stilos.css">

<div id="perfil-saas">
  <div class="container-fluid px-4 py-4 perfil-page">

    <!-- HEADER -->
    <div class="perfil-header mb-4">
      <h1>Perfil</h1>
      <p>Administrá la información de tu cuenta</p>
    </div>

    <div class="row g-4">

  <!-- ================= TARJETA PERFIL ================= -->
  <div class="col-xl-4 col-lg-5">
    <div class="card perfil-card text-center">

      <!-- AVATAR -->
      <div class="perfil-avatar">
        <?php if (!empty($_SESSION['avatar']) && file_exists($_SESSION['avatar'])): ?>
          <img src="<?= htmlspecialchars($_SESSION['avatar']) ?>" alt="Avatar">
        <?php else: ?>
          <i class="fa-solid fa-user"></i>
        <?php endif; ?>
      </div>

      <!-- NOMBRE -->
      <h3 class="mt-2">
        <?= htmlspecialchars($nombreUsuario . ' ' . $apellidoUsuario) ?>
      </h3>

      <!-- ROL -->
      <span class="perfil-rol">
        <?= htmlspecialchars($rol) ?>
      </span>

      <!-- INPUT FILE OCULTO -->
      <input type="file" id="inputAvatar" accept="image/*" hidden>

      <!-- BOTÓN CAMBIAR AVATAR -->
      <button id="btnAvatar" class="btn btn-outline-warning w-100 mt-3">
        <i class="fa-solid fa-camera"></i> Cambiar avatar
      </button>

    </div>
  </div>

  <!-- ================= COLUMNA DERECHA (DATOS) ================= -->
  <div class="col-xl-8 col-lg-7">
    <!-- acá va tu card de Información de la cuenta y Seguridad -->
  </div>

</div>


      <!-- ================= DATOS ================= -->
      <div class="col-xl-8 col-lg-7">

        <div class="card perfil-section" id="perfil-datos">

          <div class="perfil-section-header">
            <h4><i class="fa-solid fa-id-card"></i> Información de la cuenta</h4>

            <button id="btnEditarPerfil" class="btn btn-outline-warning btn-sm">
              <i class="fa-solid fa-pen"></i> Editar
            </button>
          </div>

          <div class="perfil-row">
            <label>Nombre</label>
            <input type="text" id="nombre"
              value="<?= htmlspecialchars($nombreUsuario) ?>" disabled>
          </div>

          <div class="perfil-row">
            <label>Apellido</label>
            <input type="text" id="apellido"
              value="<?= htmlspecialchars($apellidoUsuario) ?>" disabled>
          </div>

          <div class="perfil-row">
            <label>DNI</label>
            <input type="text" id="dni"
              value="<?= htmlspecialchars($dni) ?>" disabled>
          </div>

          <div class="perfil-row">
            <label>Celular</label>
            <input type="text" id="celular"
              value="<?= htmlspecialchars($celular) ?>" disabled>
          </div>

          <div class="perfil-row">
            <label>Usuario</label>
            <input type="text"
              value="<?= htmlspecialchars($usuario) ?>"
              disabled class="readonly">
          </div>

          <div class="perfil-row">
            <label>Rol</label>
            <input type="text"
              value="<?= htmlspecialchars($rol) ?>"
              disabled class="readonly">
          </div>

        </div>

        <!-- ================= SEGURIDAD ================= -->
        <div class="card perfil-section mt-4">
          <h4><i class="fa-solid fa-shield-halved"></i> Seguridad</h4>

          <div class="seguridad-item">
            <div>
              <strong>Contraseña</strong>
              <p>Cambiá tu contraseña regularmente para mayor seguridad</p>
            </div>
            <button class="btn btn-warning btn-sm" disabled>
              Cambiar
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php include '../dashboard/footer.php'; ?>

<!-- ================= JS PERFIL ================= -->
<script>
const btnEditar = document.getElementById('btnEditarPerfil');
const inputsEditables = document.querySelectorAll(
  '#perfil-datos input:not(.readonly)'
);

let editando = false;

btnEditar.addEventListener('click', async () => {

  if (!editando) {
    inputsEditables.forEach(i => i.disabled = false);
    btnEditar.innerHTML = '<i class="fa-solid fa-check"></i> Guardar cambios';
    editando = true;
    return;
  }

  const data = {
    nombre:   document.getElementById('nombre').value.trim(),
    apellido: document.getElementById('apellido').value.trim(),
    dni:      document.getElementById('dni').value.trim(),
    celular:  document.getElementById('celular').value.trim()
  };

  if (!data.nombre || !data.apellido) {
    Swal.fire({
      icon: 'warning',
      title: 'Datos incompletos',
      text: 'Nombre y Apellido son obligatorios'
    });
    return;
  }

  try {
    const r = await fetch('guardar_perfil.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    const j = await r.json();
    if (!j.ok) throw new Error();

    Swal.fire({
      icon: 'success',
      title: 'Perfil actualizado',
      timer: 1500,
      showConfirmButton: false
    });

    inputsEditables.forEach(i => i.disabled = true);
    btnEditar.innerHTML = '<i class="fa-solid fa-pen"></i> Editar';
    editando = false;

  } catch {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudieron guardar los cambios'
    });
  }
});

/* ================= AVATAR ================= */
const btnAvatar = document.getElementById('btnAvatar');
const inputAvatar = document.getElementById('inputAvatar');

btnAvatar.addEventListener('click', () => inputAvatar.click());

inputAvatar.addEventListener('change', async () => {
  const file = inputAvatar.files[0];
  if (!file) return;

  const fd = new FormData();
  fd.append('avatar', file);

  try {
    const r = await fetch('subir_avatar.php', {
      method: 'POST',
      body: fd
    });

    const j = await r.json();
    if (!j.ok) throw new Error();

    Swal.fire({
      icon: 'success',
      title: 'Avatar actualizado',
      timer: 1200,
      showConfirmButton: false
    }).then(() => location.reload());

  } catch {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudo actualizar el avatar'
    });
  }
});
</script>
