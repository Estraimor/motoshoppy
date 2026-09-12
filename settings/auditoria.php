<?php

function auditoria(
    PDO $conexion,
    string $accion,
    string $modulo,
    ?string $tabla = null,
    $registroId = null,
    string $descripcion = '',
    $antes = null,
    $despues = null,
    ?string $afectadoTabla = null,
    $afectadoId = null
) {
    // 🔒 Seguridad básica
    if (!isset($_SESSION['idusuario'])) {
        return;
    }

    /* =========================
       LIMPIEZA DE DATOS
    ========================= */

    $antes   = normalizarDatosAuditoria($antes);
    $despues = normalizarDatosAuditoria($despues);

    /* =========================
       INSERT
    ========================= */
    $sql = "
        INSERT INTO auditoria
        (
            usuario_id,
            accion,
            modulo,
            tabla_afectada,
            registro_id,
            descripcion,
            datos_antes,
            datos_despues,
            ip,
            afectado_tabla,
            afectado_id
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        $_SESSION['idusuario'],
        strtoupper($accion),
        $modulo,
        $tabla,
        $registroId,
        $descripcion ?: descripcionAutomatica($accion, $tabla, $registroId),
        $antes !== null ? json_encode($antes, JSON_UNESCAPED_UNICODE) : null,
        $despues !== null ? json_encode($despues, JSON_UNESCAPED_UNICODE) : null,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $afectadoTabla,
        $afectadoId
    ]);
}

/* =====================================================
   NORMALIZADOR DE DATOS (MUY IMPORTANTE)
===================================================== */
function normalizarDatosAuditoria($data)
{
    if ($data === null) return null;

    // Si viene como string JSON
    if (is_string($data)) {
        $json = json_decode($data, true);
        return json_last_error() === JSON_ERROR_NONE ? $json : null;
    }

    // Si es objeto → array
    if (is_object($data)) {
        $data = (array) $data;
    }

    // Si no es array, no sirve
    if (!is_array($data)) {
        return null;
    }

    // ❌ Campos sensibles que NUNCA se auditan
    $bloqueados = [
        'password',
        'pass',
        'clave',
        'token'
    ];

    foreach ($bloqueados as $campo) {
        unset($data[$campo]);
    }

    // 🔁 Normalización especial de arrays (roles, ids, etc.)
    foreach ($data as $k => $v) {
        if (is_array($v)) {
            sort($v); // importante para diff
            $data[$k] = array_values($v);
        }
    }

    return $data;
}

/* =====================================================
   DESCRIPCIÓN AUTOMÁTICA (BACKUP)
===================================================== */
function descripcionAutomatica($accion, $tabla, $id)
{
    switch ($accion) {
        case 'INSERT':
            return "Creó registro en {$tabla}" . ($id ? " (ID {$id})" : '');
        case 'UPDATE':
            return "Actualizó registro en {$tabla}" . ($id ? " (ID {$id})" : '');
        case 'DELETE':
            return "Eliminó registro en {$tabla}" . ($id ? " (ID {$id})" : '');
        case 'LOGIN':
            return "Inicio de sesión";
        default:
            return "Acción {$accion}";
    }
}
