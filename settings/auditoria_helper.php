<?php

$MAPAS_AUDITORIA = require __DIR__ . '/auditoria_mapas.php';

/* =====================================================
   INTERPRETAR AUDITORÍA (MAPEADA + FK DINÁMICA)
===================================================== */
function interpretarAuditoria($tabla, $antesJson, $despuesJson)
{
    global $MAPAS_AUDITORIA;

    $antes   = json_decode($antesJson ?? '', true) ?? [];
    $despues = json_decode($despuesJson ?? '', true) ?? [];

    // Normalizar mapa aunque venga incompleto
    $mapa = array_merge(
        ['campos' => [], 'valores' => [], 'especial' => []],
        $MAPAS_AUDITORIA[$tabla] ?? []
    );

    /* 🔕 Campos técnicos que no se auditan */
    $camposIgnorados = [
        'id',
        'idcategoria',
        'idCliente',
        'idDetalle',
        'idProducto',
        'fecha',
        'fecha_alta',
        'created_at',
        'updated_at'
    ];

    $campos = array_unique(array_merge(
        array_keys($antes),
        array_keys($despues)
    ));

    $resultado = [];

    foreach ($campos as $campo) {

        if (in_array($campo, $camposIgnorados, true)) {
            continue;
        }

        $va = normalizarValor($antes[$campo] ?? null);

        if (!array_key_exists($campo, $despues)) {
            $vd = $va;
        } else {
            $vd = normalizarValor($despues[$campo]);
        }

        if ($va === $vd) {
            continue;
        }

        $resultado[] = [
            'campo'   => $mapa['campos'][$campo] ?? ucfirst($campo),
            'antes'   => traducirValor($campo, $va, $mapa),
            'despues' => traducirValor($campo, $vd, $mapa),
            'tipo'    => tipoCambio($va, $vd)
        ];
    }

    return $resultado;
}

/* =====================================================
   TRADUCIR VALOR (ESTÁTICO + FK DINÁMICA)
===================================================== */
function traducirValor($campo, $valor, $mapa)
{
    $valores  = $mapa['valores']  ?? [];
    $especial = $mapa['especial'] ?? [];

    /* 🔴 Sin valor */
    if ($valor === null || $valor === '' || $valor === '-') {
        return $campo === 'roles' ? 'Sin rol' : '—';
    }

    /* 🔁 Valores estáticos */
    if (isset($valores[$campo])) {
        if (array_key_exists((string)$valor, $valores[$campo])) {
            return $valores[$campo][(string)$valor];
        }
        if (is_numeric($valor) && array_key_exists((int)$valor, $valores[$campo])) {
            return $valores[$campo][(int)$valor];
        }
    }

    /* =====================================================
       🔥 CASO ESPECIAL DEFINITIVO: ROLES
       (NO depende de tabla_afectada)
    ===================================================== */
    if ($campo === 'roles') {

        // normalizar a array
        if (is_numeric($valor)) {
            $valor = [(int)$valor];
        }

        if (is_string($valor)) {
            $t = trim($valor);
            if ($t !== '' && $t[0] === '[') {
                $valor = json_decode($t, true) ?? [];
            }
        }

        if (!is_array($valor) || !count($valor)) {
            return 'Sin rol';
        }

        $nombres = [];
        foreach ($valor as $id) {
            if (!$id) continue;

            $nombres[] = resolverReferencia([
                'tabla' => 'roles',
                'pk'    => 'idroles',
                'campo' => 'nombre_rol'
            ], (int)$id);
        }

        return implode(', ', $nombres);
    }

    /* =====================================================
       🔗 FK DINÁMICA NORMAL (OTROS CASOS)
    ===================================================== */
    if (isset($especial[$campo])) {

        if (is_numeric($valor)) {
            $valor = [(int)$valor];
        }

        if (is_string($valor)) {
            $t = trim($valor);
            if ($t !== '' && $t[0] === '[') {
                $valor = json_decode($t, true) ?? [];
            }
        }

        if (is_array($valor)) {
            if (!count($valor)) return '—';

            $nombres = [];
            foreach ($valor as $id) {
                if (!$id) continue;
                $nombres[] = resolverReferencia($especial[$campo], (int)$id);
            }

            return implode(', ', $nombres);
        }
    }

    /* fallback */
    if (is_array($valor)) return implode(', ', $valor);

    return (string)$valor;
}

/* =====================================================
   RESOLVER FK DINÁMICA (CON CACHE)
===================================================== */
function resolverReferencia($config, $id)
{
    static $cache = [];

    $tabla = $config['tabla'];
    $pk    = $config['pk'];
    $campo = $config['campo'];

    $key = $tabla . ':' . $id;
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    global $conexion;

    $sql = "SELECT {$campo} FROM {$tabla} WHERE {$pk} = ? LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);

    $valor = $stmt->fetchColumn();

    $cache[$key] = $valor ?: $id;

    return $cache[$key];
}

/* =====================================================
   TIPO DE CAMBIO
===================================================== */
function tipoCambio($antes, $despues)
{
    if ($antes === null && $despues !== null) return 'agregado';
    if ($antes !== null && $despues === null) return 'eliminado';
    return 'modificado';
}

/* =====================================================
   NORMALIZAR VALORES
===================================================== */
function normalizarValor($valor)
{
    if ($valor === '' || $valor === '-' || $valor === null) return null;
    return $valor;
}
