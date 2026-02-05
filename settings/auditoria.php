<?php

function auditoria($conexion, $accion, $modulo, $tabla = null, $registroId = null, $descripcion = '', $antes = null, $despues = null)
{
    if (!isset($_SESSION['idusuario'])) return;

    $sql = "INSERT INTO auditoria 
            (usuario_id, accion, modulo, tabla_afectada, registro_id, descripcion, datos_antes, datos_despues, ip)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        $_SESSION['idusuario'],
        $accion,
        $modulo,
        $tabla,
        $registroId,
        $descripcion,
        $antes ? json_encode($antes, JSON_UNESCAPED_UNICODE) : null,
        $despues ? json_encode($despues, JSON_UNESCAPED_UNICODE) : null,
        $_SERVER['REMOTE_ADDR']
    ]);
}
