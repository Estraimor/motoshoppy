<?php
include '../dashboard/nav.php';
require_once '../conexion/conexion.php';

$idusuario    = $_SESSION['idusuario'] ?? 0;
$nombreUsuario   = $_SESSION['nombre']   ?? 'Usuario';
$apellidoUsuario = $_SESSION['apellido'] ?? '';
$rol             = $_SESSION['roles']    ? implode(', ', $_SESSION['roles']) : ($_SESSION['rol'] ?? 'Sin rol');
$usuario         = $_SESSION['usuario']  ?? '';
$dni             = $_SESSION['dni']      ?? '';
$celular         = $_SESSION['celular']  ?? '';
$avatar          = $_SESSION['avatar']   ?? '';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/perfil/perfil_stilos.css">

<div id="perfil-saas">
  <div class="container-fluid px-4 py-4">

    <!-- HEADER -->
    <div class="perfil-header mb-4">
      <h1><i class="fa-solid fa-user-circle me-2"></i>Mi Perfil</h1>
      <p>Administrá la información de tu cuenta</p>
    </div>

    <div class="row g-4 align-items-start">

      <!-- ===== COLUMNA IZQUIERDA: AVATAR + INFO ===== -->
      <div class="col-xl-4 col-lg-5">

        <!-- TARJETA AVATAR -->
        <div class="card perfil-card text-center mb-4">

          <div class="perfil-avatar mx-auto mb-3">
            <?php if (!empty($avatar) && file_exists(__DIR__ . '/../' . $avatar)): ?>
              <img src="<?= BASE_URL ?>/<?= htmlspecialchars($avatar) ?>?v=<?= time() ?>" alt="Avatar">
            <?php else: ?>
              <i class="fa-solid fa-user"></i>
            <?php endif; ?>
          </div>

          <h3><?= htmlspecialchars($nombreUsuario . ' ' . $apellidoUsuario) ?></h3>
          <span class="perfil-rol d-inline-block"><?= htmlspecialchars($rol) ?></span>

          <div class="mt-3">
            <span class="text-muted small"><i class="fa-solid fa-at me-1"></i><?= htmlspecialchars($usuario) ?></span>
          </div>

          <input type="file" id="inputAvatar" accept="image/*" hidden>
          <button id="btnAvatar" class="btn btn-outline-warning w-100 mt-4">
            <i class="fa-solid fa-camera me-1"></i> Cambiar foto
          </button>
        </div>

        <!-- TARJETA DATOS FIJOS -->
        <div class="card perfil-card">
          <h6 class="perfil-section-title"><i class="fa-solid fa-shield-halved me-2"></i>Acceso</h6>
          <div class="info-pill">
            <span class="info-label">Usuario</span>
            <span class="info-value"><?= htmlspecialchars($usuario) ?></span>
          </div>
          <div class="info-pill">
            <span class="info-label">Rol</span>
            <span class="info-value"><?= htmlspecialchars($rol) ?></span>
          </div>
        </div>

      </div>

      <!-- ===== COLUMNA DERECHA ===== -->
      <div class="col-xl-8 col-lg-7">

        <!-- INFORMACIÓN PERSONAL -->
        <div class="card perfil-section mb-4" id="perfil-datos">

          <div class="perfil-section-header">
            <h5 class="perfil-section-title mb-0">
              <i class="fa-solid fa-id-card me-2"></i>Información personal
            </h5>
            <button id="btnEditarPerfil" class="btn btn-sm btn-outline-warning">
              <i class="fa-solid fa-pen me-1"></i>Editar
            </button>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-sm-6">
              <label class="perfil-label">Nombre</label>
              <input type="text" id="nombre" class="perfil-input"
                value="<?= htmlspecialchars($nombreUsuario) ?>" disabled>
            </div>
            <div class="col-sm-6">
              <label class="perfil-label">Apellido</label>
              <input type="text" id="apellido" class="perfil-input"
                value="<?= htmlspecialchars($apellidoUsuario) ?>" disabled>
            </div>
            <div class="col-sm-6">
              <label class="perfil-label">DNI</label>
              <input type="text" id="dni" class="perfil-input"
                value="<?= htmlspecialchars($dni) ?>" disabled>
            </div>
            <div class="col-sm-6">
              <label class="perfil-label">Celular</label>
              <input type="text" id="celular" class="perfil-input"
                value="<?= htmlspecialchars($celular) ?>" disabled>
            </div>
          </div>

          <!-- Botones guardar/cancelar (ocultos al inicio) -->
          <div id="btnsGuardar" class="d-flex gap-2 mt-3" style="display:none !important">
            <button id="btnGuardar" class="btn btn-warning btn-sm">
              <i class="fa-solid fa-check me-1"></i>Guardar cambios
            </button>
            <button id="btnCancelar" class="btn btn-outline-secondary btn-sm">
              Cancelar
            </button>
          </div>

        </div>

        <!-- CAMBIAR CONTRASEÑA -->
        <div class="card perfil-section" id="seccion-pass">

          <div class="perfil-section-header">
            <h5 class="perfil-section-title mb-0">
              <i class="fa-solid fa-lock me-2"></i>Seguridad
            </h5>
            <button id="btnTogglePass" class="btn btn-sm btn-outline-secondary">
              <i class="fa-solid fa-key me-1"></i>Cambiar contraseña
            </button>
          </div>

          <div id="formPass" class="mt-3" style="display:none">
            <div class="row g-3">

              <div class="col-12">
                <label class="perfil-label">Contraseña actual</label>
                <div class="input-pass-wrap">
                  <input type="password" id="passActual" class="perfil-input" placeholder="••••••••">
                  <button type="button" class="toggle-eye" data-target="passActual">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                </div>
              </div>

              <div class="col-sm-6">
                <label class="perfil-label">Nueva contraseña</label>
                <div class="input-pass-wrap">
                  <input type="password" id="passNueva" class="perfil-input" placeholder="Mínimo 8 caracteres">
                  <button type="button" class="toggle-eye" data-target="passNueva">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                </div>
                <div id="passStrength" class="pass-strength mt-1"></div>
              </div>

              <div class="col-sm-6">
                <label class="perfil-label">Confirmar contraseña</label>
                <div class="input-pass-wrap">
                  <input type="password" id="passConfirm" class="perfil-input" placeholder="Repetí la nueva">
                  <button type="button" class="toggle-eye" data-target="passConfirm">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                </div>
              </div>

              <div class="col-12 d-flex gap-2">
                <button id="btnGuardarPass" class="btn btn-warning btn-sm">
                  <i class="fa-solid fa-check me-1"></i>Actualizar contraseña
                </button>
                <button id="btnCancelarPass" class="btn btn-outline-secondary btn-sm">
                  Cancelar
                </button>
              </div>

            </div>
          </div>

          <!-- Estado cuando no está editando -->
          <div id="passResumen" class="pass-resumen mt-3">
            <i class="fa-solid fa-circle-check text-success me-2"></i>
            <span>Tu contraseña está configurada. Te recomendamos cambiarla periódicamente.</span>
          </div>

        </div>

      </div>
    </div>
  </div>
</div>

<?php include '../dashboard/footer.php'; ?>

<script>
/* ===========================
   EDITAR PERFIL
=========================== */
const btnEditar  = document.getElementById('btnEditarPerfil');
const btnGuardar = document.getElementById('btnGuardar');
const btnCancelar = document.getElementById('btnCancelar');
const btnsGuardar = document.getElementById('btnsGuardar');
const inputsEdit  = document.querySelectorAll('#perfil-datos input');

const valoresOriginales = {};
inputsEdit.forEach(i => { valoresOriginales[i.id] = i.value; });

btnEditar.addEventListener('click', () => {
  inputsEdit.forEach(i => { i.disabled = false; i.classList.add('editing'); });
  btnEditar.style.display = 'none';
  btnsGuardar.style.display = 'flex';
});

btnCancelar.addEventListener('click', () => {
  inputsEdit.forEach(i => {
    i.value = valoresOriginales[i.id];
    i.disabled = true;
    i.classList.remove('editing');
  });
  btnsGuardar.style.display = 'none';
  btnEditar.style.display = '';
});

btnGuardar.addEventListener('click', async () => {
  const nombre   = document.getElementById('nombre').value.trim();
  const apellido = document.getElementById('apellido').value.trim();
  const dni      = document.getElementById('dni').value.trim();
  const celular  = document.getElementById('celular').value.trim();

  if (!nombre || !apellido) {
    Swal.fire({ icon: 'warning', title: 'Campos requeridos', text: 'Nombre y Apellido son obligatorios.' });
    return;
  }

  btnGuardar.disabled = true;
  btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Guardando…';

  try {
    const r = await fetch('guardar_perfil.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nombre, apellido, dni, celular })
    });
    const j = await r.json();
    if (!j.ok) throw new Error();

    Object.assign(valoresOriginales, { nombre, apellido, dni, celular });

    inputsEdit.forEach(i => { i.disabled = true; i.classList.remove('editing'); });
    btnsGuardar.style.display = 'none';
    btnEditar.style.display   = '';

    Swal.fire({ icon: 'success', title: 'Perfil actualizado', timer: 1500, showConfirmButton: false });
  } catch {
    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron guardar los cambios.' });
  } finally {
    btnGuardar.disabled = false;
    btnGuardar.innerHTML = '<i class="fa-solid fa-check me-1"></i>Guardar cambios';
  }
});

/* ===========================
   AVATAR
=========================== */
document.getElementById('btnAvatar').addEventListener('click', () =>
  document.getElementById('inputAvatar').click()
);

document.getElementById('inputAvatar').addEventListener('change', async function () {
  const file = this.files[0];
  if (!file) return;

  const fd = new FormData();
  fd.append('avatar', file);

  try {
    const r = await fetch('subir_avatar.php', { method: 'POST', body: fd });
    const j = await r.json();
    if (!j.ok) throw new Error();

    Swal.fire({ icon: 'success', title: 'Foto actualizada', timer: 1200, showConfirmButton: false })
      .then(() => location.reload());
  } catch {
    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo subir la imagen.' });
  }
});

/* ===========================
   CAMBIAR CONTRASEÑA
=========================== */
const formPass     = document.getElementById('formPass');
const passResumen  = document.getElementById('passResumen');
const btnToggle    = document.getElementById('btnTogglePass');
const btnCancelP   = document.getElementById('btnCancelarPass');
const btnGuardarP  = document.getElementById('btnGuardarPass');

btnToggle.addEventListener('click', () => {
  const abierto = formPass.style.display !== 'none';
  formPass.style.display    = abierto ? 'none' : 'block';
  passResumen.style.display = abierto ? '' : 'none';
  btnToggle.innerHTML = abierto
    ? '<i class="fa-solid fa-key me-1"></i>Cambiar contraseña'
    : '<i class="fa-solid fa-times me-1"></i>Cancelar';
});

btnCancelP.addEventListener('click', () => {
  formPass.style.display    = 'none';
  passResumen.style.display = '';
  btnToggle.innerHTML = '<i class="fa-solid fa-key me-1"></i>Cambiar contraseña';
  ['passActual','passNueva','passConfirm'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('passStrength').innerHTML = '';
});

/* Medidor de seguridad */
document.getElementById('passNueva').addEventListener('input', function () {
  const v = this.value;
  const el = document.getElementById('passStrength');
  if (!v) { el.innerHTML = ''; return; }

  let score = 0;
  if (v.length >= 8)  score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;

  const niveles = [
    { label: 'Muy débil', cls: 'strength-1' },
    { label: 'Débil',     cls: 'strength-2' },
    { label: 'Regular',   cls: 'strength-3' },
    { label: 'Fuerte',    cls: 'strength-4' },
  ];
  const n = niveles[score - 1] || niveles[0];
  el.innerHTML = `<span class="badge ${n.cls}">${n.label}</span>`;
});

/* Guardar contraseña */
btnGuardarP.addEventListener('click', async () => {
  const actual   = document.getElementById('passActual').value;
  const nueva    = document.getElementById('passNueva').value;
  const confirm  = document.getElementById('passConfirm').value;

  if (!actual || !nueva || !confirm) {
    Swal.fire({ icon: 'warning', title: 'Completá todos los campos' }); return;
  }
  if (nueva.length < 8) {
    Swal.fire({ icon: 'warning', title: 'Contraseña muy corta', text: 'Mínimo 8 caracteres.' }); return;
  }
  if (nueva !== confirm) {
    Swal.fire({ icon: 'warning', title: 'Las contraseñas no coinciden' }); return;
  }

  btnGuardarP.disabled = true;
  btnGuardarP.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Guardando…';

  try {
    const r = await fetch('cambiar_password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ actual, nueva })
    });
    const j = await r.json();

    if (!j.ok) {
      Swal.fire({ icon: 'error', title: 'Error', text: j.msg || 'No se pudo cambiar la contraseña.' });
      return;
    }

    Swal.fire({ icon: 'success', title: '¡Contraseña actualizada!', timer: 1600, showConfirmButton: false });
    btnCancelP.click();
  } catch {
    Swal.fire({ icon: 'error', title: 'Error de conexión' });
  } finally {
    btnGuardarP.disabled = false;
    btnGuardarP.innerHTML = '<i class="fa-solid fa-check me-1"></i>Actualizar contraseña';
  }
});

/* Mostrar/ocultar contraseña */
document.querySelectorAll('.toggle-eye').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.target);
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    btn.querySelector('i').className = isPass ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
  });
});
</script>
