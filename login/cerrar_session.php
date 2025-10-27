<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Anti-cache para que no vuelva con el back del navegador
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

header("Location: /motoshoppy/login/login.php?logout=1");
exit;
