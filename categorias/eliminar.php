<?php
require_once '../settings/bootstrap.php'; // 👈 sesión + conexión + auditoría

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    /* =========================
       DATOS ANTES
    ========================= */
    $stmtAntes = $conexion->prepare("SELECT * FROM categoria WHERE idCategoria = ?");
    $stmtAntes->execute([$id]);
    $antes = $stmtAntes->fetch(PDO::FETCH_ASSOC);

    if ($antes) {

        /* =========================
           DELETE
        ========================= */
        $stmt = $conexion->prepare("DELETE FROM categoria WHERE idCategoria = ?");
        $ok = $stmt->execute([$id]);

        /* =========================
           AUDITORÍA
        ========================= */
        if ($ok) {
            auditoria(
                $conexion,
                "DELETE",
                "categorias",
                "categoria",
                $id,
                "Eliminó categoría: {$antes['nombre_categoria']}",
                json_encode($antes),
                null
            );
        }
    }
}

header("Location: index.php");
exit;
