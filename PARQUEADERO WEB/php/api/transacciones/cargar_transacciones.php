<?php
require_once __DIR__ . '/../../db/db_connect.php';
$conn = getPDO();

$response = ["success" => false];

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['error'] = "Método no permitido. Use GET";
    echo json_encode($response);
    exit;
}

// Obtener parámetros opcionales
$data = $_GET;
$id_transaccion = $data['id_transaccion'] ?? null;
$id_acceso = $data['id_acceso'] ?? null;
$placa = $data['placa'] ?? null;
$fecha_inicio = $data['fecha_inicio'] ?? null;
$fecha_fin = $data['fecha_fin'] ?? null;
$limit = min(max((int)($data['limit'] ?? 50), 1), 100); // Entre 1 y 100
$offset = max((int)($data['offset'] ?? 0), 0);

// Corregir: Verificar si existe la key 'orden' antes de usarla
$orden = 'DESC'; // Valor por defecto
if (isset($data['orden']) && in_array(strtoupper($data['orden']), ['ASC', 'DESC'])) {
    $orden = strtoupper($data['orden']);
}

try {
    if ($id_transaccion) {
        // Obtener una transacción específica
        $stmt = $conn->prepare("
            SELECT 
                t.id_transaccion,
                t.id_acceso,
                t.id_tarifa,
                t.id_metodo_pago,
                t.fecha_transaccion,
                t.tiempo_estadia,
                t.subtotal,
                t.descuentos,
                t.iva,
                t.total_pagado,
                t.numero_comprobante,
                t.usuario_registro,
                a.placa_vehiculo,
                a.fecha_entrada,
                a.fecha_salida,
                TIMESTAMPDIFF(MINUTE, a.fecha_entrada, a.fecha_salida) as tiempo_total_minutos,
                u.nombre_completo as nombre_usuario,
                tv.nombre as tipo_vehiculo,
                e.codigo_espacio,
                z.nombre as zona_estacionamiento
            FROM transaccion t
            INNER JOIN acceso a ON t.id_acceso = a.id_acceso
            INNER JOIN vehiculo v ON a.placa_vehiculo = v.placa
            INNER JOIN usuario u ON v.id_usuario = u.id_usuario
            INNER JOIN tipovehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
            INNER JOIN espacio e ON a.id_espacio = e.id_espacio
            INNER JOIN zonaestacionamiento z ON e.id_zona = z.id_zona
            WHERE t.id_transaccion = ?
        ");
        $stmt->execute([$id_transaccion]);
        $transaccion = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($transaccion) {
            // Convertir valores numéricos
            $transaccion['id_transaccion'] = (int)$transaccion['id_transaccion'];
            $transaccion['id_acceso'] = (int)$transaccion['id_acceso'];
            $transaccion['id_tarifa'] = (int)$transaccion['id_tarifa'];
            $transaccion['id_metodo_pago'] = (int)$transaccion['id_metodo_pago'];
            $transaccion['tiempo_estadia'] = (int)$transaccion['tiempo_estadia'];
            $transaccion['tiempo_total_minutos'] = (int)$transaccion['tiempo_total_minutos'];
            $transaccion['subtotal'] = (float)$transaccion['subtotal'];
            $transaccion['descuentos'] = (float)$transaccion['descuentos'];
            $transaccion['iva'] = (float)$transaccion['iva'];
            $transaccion['total_pagado'] = (float)$transaccion['total_pagado'];

            $response['success'] = true;
            $response['transaccion'] = $transaccion;
        } else {
            $response['error'] = "Transacción no encontrada";
        }

    } else {
        // Construir query dinámica con filtros
        $whereConditions = [];
        $params = [];

        if ($id_acceso) {
            $whereConditions[] = "t.id_acceso = ?";
            $params[] = $id_acceso;
        }

        if ($placa) {
            $whereConditions[] = "a.placa_vehiculo = ?";
            $params[] = strtoupper($placa);
        }

        if ($fecha_inicio) {
            $whereConditions[] = "t.fecha_transaccion >= ?";
            $params[] = $fecha_inicio . ' 00:00:00';
        }

        if ($fecha_fin) {
            $whereConditions[] = "t.fecha_transaccion <= ?";
            $params[] = $fecha_fin . ' 23:59:59';
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        // Query principal
        $sql = "
            SELECT 
                t.id_transaccion,
                t.id_acceso,
                t.fecha_transaccion,
                t.tiempo_estadia,
                t.subtotal,
                t.descuentos,
                t.iva,
                t.total_pagado,
                t.numero_comprobante,
                t.usuario_registro,
                a.placa_vehiculo,
                a.fecha_entrada,
                a.fecha_salida,
                u.nombre_completo as nombre_usuario,
                tv.nombre as tipo_vehiculo,
                e.codigo_espacio,
                z.nombre as zona_estacionamiento,
                TIMESTAMPDIFF(MINUTE, a.fecha_entrada, a.fecha_salida) as tiempo_total_minutos
            FROM transaccion t
            INNER JOIN acceso a ON t.id_acceso = a.id_acceso
            INNER JOIN vehiculo v ON a.placa_vehiculo = v.placa
            INNER JOIN usuario u ON v.id_usuario = u.id_usuario
            INNER JOIN tipovehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
            INNER JOIN espacio e ON a.id_espacio = e.id_espacio
            INNER JOIN zonaestacionamiento z ON e.id_zona = z.id_zona
            $whereClause
            ORDER BY t.fecha_transaccion $orden
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $conn->prepare($sql);
        
        // Preparar parámetros para la ejecución
        $executeParams = $params;
        $executeParams[] = $limit;
        $executeParams[] = $offset;
        
        $stmt->execute($executeParams);
        $transacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir valores numéricos
        foreach ($transacciones as &$transaccion) {
            $transaccion['id_transaccion'] = (int)$transaccion['id_transaccion'];
            $transaccion['id_acceso'] = (int)$transaccion['id_acceso'];
            $transaccion['tiempo_estadia'] = (int)$transaccion['tiempo_estadia'];
            $transaccion['tiempo_total_minutos'] = (int)$transaccion['tiempo_total_minutos'];
            $transaccion['subtotal'] = (float)$transaccion['subtotal'];
            $transaccion['descuentos'] = (float)$transaccion['descuentos'];
            $transaccion['iva'] = (float)$transaccion['iva'];
            $transaccion['total_pagado'] = (float)$transaccion['total_pagado'];
        }

        // Contar total de registros para paginación
        $countSql = "
            SELECT COUNT(*) as total
            FROM transaccion t
            INNER JOIN acceso a ON t.id_acceso = a.id_acceso
            $whereClause
        ";
        
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);
        $totalRegistros = $totalResult ? (int)$totalResult['total'] : 0;

        // Estadísticas adicionales
        $statsSql = "
            SELECT 
                COUNT(*) as total_transacciones,
                COALESCE(SUM(t.total_pagado), 0) as ingresos_totales,
                COALESCE(AVG(t.total_pagado), 0) as promedio_por_transaccion,
                MIN(t.fecha_transaccion) as fecha_mas_antigua,
                MAX(t.fecha_transaccion) as fecha_mas_reciente
            FROM transaccion t
            $whereClause
        ";
        
        $statsStmt = $conn->prepare($statsSql);
        $statsStmt->execute($params);
        $estadisticas = $statsStmt->fetch(PDO::FETCH_ASSOC);

        // Convertir valores numéricos de estadísticas
        $estadisticas['total_transacciones'] = (int)$estadisticas['total_transacciones'];
        $estadisticas['ingresos_totales'] = (float)$estadisticas['ingresos_totales'];
        $estadisticas['promedio_por_transaccion'] = (float)$estadisticas['promedio_por_transaccion'];

        $response['success'] = true;
        $response['transacciones'] = $transacciones;
        $response['total'] = $totalRegistros;
        $response['paginacion'] = [
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $totalRegistros
        ];
        $response['estadisticas'] = $estadisticas;

        // Agregar información de filtros aplicados
        if (!empty($whereConditions)) {
            $response['filtros'] = [];
            if ($id_acceso) $response['filtros']['id_acceso'] = (int)$id_acceso;
            if ($placa) $response['filtros']['placa'] = $placa;
            if ($fecha_inicio) $response['filtros']['fecha_inicio'] = $fecha_inicio;
            if ($fecha_fin) $response['filtros']['fecha_fin'] = $fecha_fin;
        }
    }

} catch (PDOException $e) {
    $response['error'] = "Error de base de datos: " . $e->getMessage();
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?>