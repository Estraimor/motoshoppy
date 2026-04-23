<?php
// ===============================
// CONEXIÓN SEGURA A "motoshoppy"
// ===============================
$host = 'localhost';      // Cambia si tu servidor no es local
$db   = 'u966473590_motoshoppy';     // Nombre de tu base de datos
$user = 'u966473590_jorgeandino';     // Usuario de MySQL
$pass = '#Lucianobarros820012.';    // Contraseña de MySQL
$charset = 'utf8mb4';     // Codificación recomendada

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Errores por excepción
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch en array asociativo
    PDO::ATTR_EMULATE_PREPARES   => false,                 // Prepared statements nativos
];

try {
    $conexion = new PDO($dsn, $user, $pass, $options);
    // echo "✅ Conexión establecida correctamente";
    // echo "Conexión exitosa";
} catch (PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("❌ Error al conectar a la base de datos.");
}
