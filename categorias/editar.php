<?php
require_once '../settings/bootstrap.php'; // 👈 conexión + sesión + auditoría

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['idCategoria'];

    /* =========================
       OBTENER DATOS ANTES
    ========================= */
    $stmtAntes = $conexion->prepare("SELECT * FROM categoria WHERE idCategoria = ?");
    $stmtAntes->execute([$id]);
    $antes = $stmtAntes->fetch(PDO::FETCH_ASSOC);


    /* =========================
       UPDATE
    ========================= */
    $stmt = $conexion->prepare("
        UPDATE categoria 
        SET nombre_categoria = :nombre,
            descripcion = :descripcion,
            estado = :estado
        WHERE idCategoria = :id
    ");

    $ok = $stmt->execute([
        ':nombre' => $_POST['nombre_categoria'],
        ':descripcion' => $_POST['descripcion'],
        ':estado' => $_POST['estado'],
        ':id' => $id
    ]);


    /* =========================
       AUDITORÍA
    ========================= */
    if ($ok) {

        $despues = [
            'nombre_categoria' => $_POST['nombre_categoria'],
            'descripcion' => $_POST['descripcion'],
            'estado' => $_POST['estado']
        ];

        auditoria(
            $conexion,
            "UPDATE",
            "categorias",
            "categoria",
            $id,
            "Editó categoría: {$antes['nombre_categoria']}",
            json_encode($antes),
            json_encode($despues)
        );
    }

    header("Location: index.php");
    exit;
}
