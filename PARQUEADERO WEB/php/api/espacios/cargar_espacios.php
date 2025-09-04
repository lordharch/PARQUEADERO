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
$id_espacio = $data['id_espacio'] ?? null;
$id_zona = $data['id_zona'] ?? null;
$id_tipo_vehiculo = $data['id_tipo_vehiculo'] ?? null;
$estado = $data['estado'] ?? null;
$disponibles_only = filter_var($data['disponibles_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
$ocupados_only = filter_var($data['ocupados_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
$limit = min(max((int)($data['limit'] ?? 100), 1), 200); // Entre 1 y 200
$offset = max((int)($data['offset'] ?? 0), 0);

// Ordenamiento
$orden = 'ASC'; // Valor por defecto
if (isset($data['orden']) && in_array(strtoupper($data['orden']), ['ASC', 'DESC'])) {
    $orden = strtoupper($data['orden']);
}

// Campo de ordenamiento
$orden_por = 'z.nombre, e.codigo_espacio'; // Valor por defecto
$campos_orden_permitidos = ['zona', 'tipo_vehiculo', 'estado', 'codigo_espacio'];
if (isset($data['orden_por']) && in_array($data['orden_por'], $campos_orden_permitidos)) {
    $orden_por = match($data['orden_por']) {
        'zona' => 'z.nombre',
        'tipo_vehiculo' => 'tv.nombre',
        'estado' => 'e.estado',
        'codigo_espacio' => 'e.codigo_espacio',
        default => 'z.nombre, e.codigo_espacio'
    };
}

try {
    if ($id_espacio) {
        // Obtener un espacio específico con todos los detalles
        $stmt = $conn->prepare("
            SELECT 
                e.id_espacio,
                e.codigo_espacio,
                e.estado,
                e.id_zona,
                z.nombre AS nombre_zona,
                z.capacidad_maxima,
                e.id_tipo_vehiculo,
                tv.nombre AS tipo_vehiculo,
                tv.tarifa_base,
                a.id_acceso,
                a.placa_vehiculo,
                a.fecha_entrada,
                TIMESTAMPDIFF(MINUTE, a.fecha_entrada, NOW()) as minutos_ocupado,
                v.marca,
                v.modelo,
                v.color,
                u.nombre_completo AS propietario,
                u.telefono,
                COUNT(ae.id_acceso) as total_usos_historicos
            FROM espacio e
            INNER JOIN zonaestacionamiento z ON e.id_zona = z.id_zona
            INNER JOIN tipovehiculo tv ON e.id_tipo_vehiculo = tv.id_tipo_vehiculo
            LEFT JOIN acceso a ON e.id_espacio = a.id_espacio AND a.estado = 'activo'
            LEFT JOIN vehiculo v ON a.placa_vehiculo = v.placa
            LEFT JOIN usuario u ON v.id_usuario = u.id_usuario
            LEFT JOIN acceso ae ON e.id_espacio = ae.id_espacio
            WHERE e.id_espacio = ?
            GROUP BY e.id_espacio, e.codigo_espacio, e.estado, z.nombre, z.capacidad_maxima, 
                     tv.nombre, tv.tarifa_base, a.id_acceso, a.placa_vehiculo, a.fecha_entrada,
                     v.marca, v.modelo, v.color, u.nombre_completo, u.telefono
        ");
        $stmt->execute([$id_espacio]);
        $espacio = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($espacio) {
            // Convertir valores numéricos
            $espacio['id_espacio'] = (int)$espacio['id_espacio'];
            $espacio['id_zona'] = (int)$espacio['id_zona'];
            $espacio['id_tipo_vehiculo'] = (int)$espacio['id_tipo_vehiculo'];
            $espacio['capacidad_maxima'] = (int)$espacio['capacidad_maxima'];
            $espacio['tarifa_base'] = (float)$espacio['tarifa_base'];
            $espacio['minutos_ocupado'] = (int)$espacio['minutos_ocupado'];
            $espacio['total_usos_historicos'] = (int)$espacio['total_usos_historicos'];
            $espacio['id_acceso'] = $espacio['id_acceso'] ? (int)$espacio['id_acceso'] : null;
            $espacio['ocupado_actualmente'] = !empty($espacio['placa_vehiculo']);

            $response['success'] = true;
            $response['espacio'] = $espacio;
        } else {
            $response['error'] = "Espacio no encontrado";
        }

    } else {
        // Construir query dinámica con filtros
        $whereConditions = [];
        $params = [];

        if ($id_zona) {
            $whereConditions[] = "e.id_zona = ?";
            $params[] = $id_zona;
        }

        if ($id_tipo_vehiculo) {
            $whereConditions[] = "e.id_tipo_vehiculo = ?";
            $params[] = $id_tipo_vehiculo;
        }

        if ($estado) {
            $whereConditions[] = "e.estado = ?";
            $params[] = $estado;
        }

        if ($disponibles_only) {
            $whereConditions[] = "e.estado = 'disponible'";
        }

        if ($ocupados_only) {
            $whereConditions[] = "e.estado = 'ocupado'";
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        // Query principal
        $sql = "
            SELECT 
                e.id_espacio,
                e.codigo_espacio,
                e.estado,
                z.id_zona,
                z.nombre AS zona,
                z.capacidad_maxima,
                tv.id_tipo_vehiculo,
                tv.nombre AS tipo_vehiculo,
                tv.tarifa_base,
                a.placa_vehiculo,
                a.fecha_entrada,
                TIMESTAMPDIFF(MINUTE, a.fecha_entrada, NOW()) as minutos_ocupado,
                v.marca,
                v.modelo,
                v.color,
                u.nombre_completo AS propietario,
                COUNT(ae.id_acceso) as total_usos_historicos,
                CASE WHEN a.placa_vehiculo IS NOT NULL THEN true ELSE false END as ocupado_actualmente
            FROM espacio e
            INNER JOIN zonaestacionamiento z ON e.id_zona = z.id_zona
            INNER JOIN tipovehiculo tv ON e.id_tipo_vehiculo = tv.id_tipo_vehiculo
            LEFT JOIN acceso a ON e.id_espacio = a.id_espacio AND a.estado = 'activo'
            LEFT JOIN vehiculo v ON a.placa_vehiculo = v.placa
            LEFT JOIN usuario u ON v.id_usuario = u.id_usuario
            LEFT JOIN acceso ae ON e.id_espacio = ae.id_espacio
            $whereClause
            GROUP BY e.id_espacio, e.codigo_espacio, e.estado, z.id_zona, z.nombre, z.capacidad_maxima, 
                     tv.id_tipo_vehiculo, tv.nombre, tv.tarifa_base, a.placa_vehiculo, a.fecha_entrada,
                     v.marca, v.modelo, v.color, u.nombre_completo
            ORDER BY $orden_por $orden
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $conn->prepare($sql);
        
        // Preparar parámetros para la ejecución
        $executeParams = $params;
        $executeParams[] = $limit;
        $executeParams[] = $offset;
        
        $stmt->execute($executeParams);
        $espacios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir valores numéricos
        foreach ($espacios as &$espacio) {
            $espacio['id_espacio'] = (int)$espacio['id_espacio'];
            $espacio['id_zona'] = (int)$espacio['id_zona'];
            $espacio['id_tipo_vehiculo'] = (int)$espacio['id_tipo_vehiculo'];
            $espacio['capacidad_maxima'] = (int)$espacio['capacidad_maxima'];
            $espacio['tarifa_base'] = (float)$espacio['tarifa_base'];
            $espacio['minutos_ocupado'] = (int)$espacio['minutos_ocupado'];
            $espacio['total_usos_historicos'] = (int)$espacio['total_usos_historicos'];
            $espacio['ocupado_actualmente'] = (bool)$espacio['ocupado_actualmente'];
        }

        // Contar total de registros para paginación
        $countSql = "
            SELECT COUNT(*) as total
            FROM espacio e
            $whereClause
        ";
        
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);
        $totalRegistros = $totalResult ? (int)$totalResult['total'] : 0;

        // Estadísticas adicionales
        $statsSql = "
            SELECT 
                COUNT(*) as total_espacios,
                COUNT(CASE WHEN e.estado = 'disponible' THEN 1 END) as disponibles,
                COUNT(CASE WHEN e.estado = 'ocupado' THEN 1 END) as ocupados,
                COUNT(CASE WHEN e.estado = 'reservado' THEN 1 END) as reservados,
                COUNT(CASE WHEN e.estado = 'mantenimiento' THEN 1 END) as mantenimiento,
                COUNT(DISTINCT e.id_zona) as zonas_con_espacios,
                COUNT(DISTINCT e.id_tipo_vehiculo) as tipos_vehiculo_con_espacios
            FROM espacio e
            $whereClause
        ";
        
        $statsStmt = $conn->prepare($statsSql);
        $statsStmt->execute($params);
        $estadisticas = $statsStmt->fetch(PDO::FETCH_ASSOC);

        // Convertir valores numéricos de estadísticas
        $estadisticas['total_espacios'] = (int)$estadisticas['total_espacios'];
        $estadisticas['disponibles'] = (int)$estadisticas['disponibles'];
        $estadisticas['ocupados'] = (int)$estadisticas['ocupados'];
        $estadisticas['reservados'] = (int)$estadisticas['reservados'];
        $estadisticas['mantenimiento'] = (int)$estadisticas['mantenimiento'];
        $estadisticas['zonas_con_espacios'] = (int)$estadisticas['zonas_con_espacios'];
        $estadisticas['tipos_vehiculo_con_espacios'] = (int)$estadisticas['tipos_vehiculo_con_espacios'];

        $response['success'] = true;
        $response['espacios'] = $espacios;
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
            if ($id_zona) $response['filtros']['id_zona'] = (int)$id_zona;
            if ($id_tipo_vehiculo) $response['filtros']['id_tipo_vehiculo'] = (int)$id_tipo_vehiculo;
            if ($estado) $response['filtros']['estado'] = $estado;
            if ($disponibles_only) $response['filtros']['disponibles_only'] = true;
            if ($ocupados_only) $response['filtros']['ocupados_only'] = true;
        }
    }

} catch (PDOException $e) {
    $response['error'] = "Error de base de datos: " . $e->getMessage();
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?>