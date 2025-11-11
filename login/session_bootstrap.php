<?php
/* ===== CONFIG DE SESIÓN ===== */
$IDLE_TIMEOUT   = 30 * 60;   // 30 minutos
$ABS_EXPIRATION = 0;         // 0 = cookie de sesión (se borra al cerrar navegador). 
// Si quieres que caduque aunque el navegador siga abierto, pon por ej. 1800.

ini_set('session.gc_maxlifetime', $IDLE_TIMEOUT); // limpieza del lado servidor
session_set_cookie_params([
    'lifetime' => $ABS_EXPIRATION,  // o $IDLE_TIMEOUT si prefieres expiración absoluta
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => isset($_SERVER['HTTPS']) // true si usas https
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ===== NO CACHE en páginas protegidas ===== */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

/* ===== TIMEOUT POR INACTIVIDAD ===== */
$now = time();
if (isset($_SESSION['LAST_ACTIVITY']) && ($now - $_SESSION['LAST_ACTIVITY'] > $IDLE_TIMEOUT)) {
    // Inactivo: destruir y mandar a login
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', $now - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header("Location: /motoshoppy/login/login.php?timeout=1");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = $now;

/* ===== REGENERAR ID periódicamente para seguridad ===== */
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = $now;
} elseif ($now - $_SESSION['CREATED'] > 300) { // cada 5 minutos
    session_regenerate_id(true);
    $_SESSION['CREATED'] = $now;
}

/* ===== GUARDIA: exigir login ===== */
if (empty($_SESSION['idusuario'])) {
    header("Location: /motoshoppy/login/login.php");
    exit;
}
