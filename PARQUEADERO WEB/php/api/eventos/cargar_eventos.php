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
$id_evento = $data['id_evento'] ?? null;
$id_tipo_evento = $data['id_tipo_evento'] ?? null;
$estado_resolucion = $data['estado_resolucion'] ?? null;
$nivel_prioridad = $data['nivel_prioridad'] ?? null;
$fecha_inicio = $data['fecha_inicio'] ?? null;
$fecha_fin = $data['fecha_fin'] ?? null;
$placa = $data['placa'] ?? null;
$limit = min(max((int)($data['limit'] ?? 50), 1), 100); // Entre 1 y 100
$offset = max((int)($data['offset'] ?? 0), 0);

// Ordenamiento
$orden = 'DESC'; // Valor por defecto
if (isset($data['orden']) && in_array(strtoupper($data['orden']), ['ASC', 'DESC'])) {
    $orden = strtoupper($data['orden']);
}

// Campo de ordenamiento
$orden_por = 'e.fecha_evento'; // Valor por defecto
$campos_orden_permitidos = ['fecha_evento', 'nivel_prioridad', 'estado_resolucion', 'tipo'];
if (isset($data['orden_por']) && in_array($data['orden_por'], $campos_orden_permitidos)) {
    $orden_por = match($data['orden_por']) {
        'nivel_prioridad' => 'te.nivel_prioridad',
        'estado_resolucion' => 'e.estado_resolucion',
        'tipo' => 'te.nombre',
        default => 'e.fecha_evento'
    };
}

try {
    if ($id_evento) {
        // Obtener un evento específico con todos los detalles
        $stmt = $conn->prepare("
            SELECT 
                e.id_evento,
                e.id_tipo_evento,
                te.nombre AS tipo_evento,
                te.nivel_prioridad,
                e.id_usuario_reporta,
                u.nombre_completo AS usuario_reporta,
                e.id_acceso,
                a.placa_vehiculo,
                v.marca,
                v.modelo,
                v.color,
                e.id_camara,
                c.nombre AS nombre_camara,
                c.direccion_ip,
                e.fecha_evento,
                e.descripcion,
                e.accion_tomada,
                e.estado_resolucion,
                TIMESTAMPDIFF(MINUTE, e.fecha_evento, NOW()) as minutos_desde_evento,
                z.nombre as zona_camara,
                e2.codigo_espacio
            FROM eventoseguridad e
            INNER JOIN tipoeventoseguridad te ON e.id_tipo_evento = te.id_tipo_evento
            LEFT JOIN usuario u ON e.id_usuario_reporta = u.id_usuario
            LEFT JOIN acceso a ON e.id_acceso = a.id_acceso
            LEFT JOIN vehiculo v ON a.placa_vehiculo = v.placa
            LEFT JOIN camara c ON e.id_camara = c.id_camara
            LEFT JOIN espacio e2 ON a.id_espacio = e2.id_espacio
            LEFT JOIN zonaestacionamiento z ON c.id_zona = z.id_zona
            WHERE e.id_evento = ?
        ");
        $stmt->execute([$id_evento]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($evento) {
            // Convertir valores numéricos
            $evento['id_evento'] = (int)$evento['id_evento'];
            $evento['id_tipo_evento'] = (int)$evento['id_tipo_evento'];
            $evento['id_usuario_reporta'] = $evento['id_usuario_reporta'] ? (int)$evento['id_usuario_reporta'] : null;
            $evento['id_acceso'] = $evento['id_acceso'] ? (int)$evento['id_acceso'] : null;
            $evento['id_camara'] = (int)$evento['id_camara'];
            $evento['minutos_desde_evento'] = (int)$evento['minutos_desde_evento'];

            $response['success'] = true;
            $response['evento'] = $evento;
        } else {
            $response['error'] = "Evento no encontrado";
        }

    } else {
        // Construir query dinámica con filtros
        $whereConditions = [];
        $params = [];

        if ($id_tipo_evento) {
            $whereConditions[] = "e.id_tipo_evento = ?";
            $params[] = $id_tipo_evento;
        }

        if ($estado_resolucion) {
            $whereConditions[] = "e.estado_resolucion = ?";
            $params[] = $estado_resolucion;
        }

        if ($nivel_prioridad) {
            $whereConditions[] = "te.nivel_prioridad = ?";
            $params[] = $nivel_prioridad;
        }

        if ($fecha_inicio) {
            $whereConditions[] = "e.fecha_evento >= ?";
            $params[] = $fecha_inicio . ' 00:00:00';
        }

        if ($fecha_fin) {
            $whereConditions[] = "e.fecha_evento <= ?";
            $params[] = $fecha_fin . ' 23:59:59';
        }

        if ($placa) {
            $whereConditions[] = "a.placa_vehiculo = ?";
            $params[] = strtoupper($placa);
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        // Query principal
        $sql = "
            SELECT 
                e.id_evento,
                te.nombre AS tipo_evento,
                te.nivel_prioridad,
                e.fecha_evento,
                e.descripcion,
                e.accion_tomada,
                e.estado_resolucion,
                u.nombre_completo AS usuario_reporta,
                a.placa_vehiculo,
                v.marca,
                v.modelo,
                c.nombre AS nombre_camara,
                TIMESTAMPDIFF(MINUTE, e.fecha_evento, NOW()) as minutos_desde_evento,
                z.nombre as zona_camara
            FROM eventoseguridad e
            INNER JOIN tipoeventoseguridad te ON e.id_tipo_evento = te.id_tipo_evento
            LEFT JOIN usuario u ON e.id_usuario_reporta = u.id_usuario
            LEFT JOIN acceso a ON e.id_acceso = a.id_acceso
            LEFT JOIN vehiculo v ON a.placa_vehiculo = v.placa
            LEFT JOIN camara c ON e.id_camara = c.id_camara
            LEFT JOIN zonaestacionamiento z ON c.id_zona = z.id_zona
            $whereClause
            ORDER BY $orden_por $orden
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $conn->prepare($sql);
        
        // Preparar parámetros para la ejecución
        $executeParams = $params;
        $executeParams[] = $limit;
        $executeParams[] = $offset;
        
        $stmt->execute($executeParams);
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir valores numéricos
        foreach ($eventos as &$evento) {
            $evento['id_evento'] = (int)$evento['id_evento'];
            $evento['minutos_desde_evento'] = (int)$evento['minutos_desde_evento'];
        }

        // Contar total de registros para paginación
        $countSql = "
            SELECT COUNT(*) as total
            FROM eventoseguridad e
            INNER JOIN tipoeventoseguridad te ON e.id_tipo_evento = te.id_tipo_evento
            LEFT JOIN acceso a ON e.id_acceso = a.id_acceso
            $whereClause
        ";
        
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);
        $totalRegistros = $totalResult ? (int)$totalResult['total'] : 0;

        // Estadísticas adicionales
        $statsSql = "
            SELECT 
                COUNT(*) as total_eventos,
                COUNT(CASE WHEN e.estado_resolucion = 'reportado' THEN 1 END) as reportados,
                COUNT(CASE WHEN e.estado_resolucion = 'investigacion' THEN 1 END) as en_investigacion,
                COUNT(CASE WHEN e.estado_resolucion = 'resuelto' THEN 1 END) as resueltos,
                COUNT(CASE WHEN e.estado_resolucion = 'escalado' THEN 1 END) as escalados,
                COUNT(CASE WHEN te.nivel_prioridad = 'critico' THEN 1 END) as criticos,
                COUNT(CASE WHEN te.nivel_prioridad = 'alto' THEN 1 END) as altos,
                MIN(e.fecha_evento) as evento_mas_antiguo,
                MAX(e.fecha_evento) as evento_mas_reciente
            FROM eventoseguridad e
            INNER JOIN tipoeventoseguridad te ON e.id_tipo_evento = te.id_tipo_evento
            $whereClause
        ";
        
        $statsStmt = $conn->prepare($statsSql);
        $statsStmt->execute($params);
        $estadisticas = $statsStmt->fetch(PDO::FETCH_ASSOC);

        // Convertir valores numéricos de estadísticas
        $estadisticas['total_eventos'] = (int)$estadisticas['total_eventos'];
        $estadisticas['reportados'] = (int)$estadisticas['reportados'];
        $estadisticas['en_investigacion'] = (int)$estadisticas['en_investigacion'];
        $estadisticas['resueltos'] = (int)$estadisticas['resueltos'];
        $estadisticas['escalados'] = (int)$estadisticas['escalados'];
        $estadisticas['criticos'] = (int)$estadisticas['criticos'];
        $estadisticas['altos'] = (int)$estadisticas['altos'];

        $response['success'] = true;
        $response['eventos'] = $eventos;
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
            if ($id_tipo_evento) $response['filtros']['id_tipo_evento'] = (int)$id_tipo_evento;
            if ($estado_resolucion) $response['filtros']['estado_resolucion'] = $estado_resolucion;
            if ($nivel_prioridad) $response['filtros']['nivel_prioridad'] = $nivel_prioridad;
            if ($fecha_inicio) $response['filtros']['fecha_inicio'] = $fecha_inicio;
            if ($fecha_fin) $response['filtros']['fecha_fin'] = $fecha_fin;
            if ($placa) $response['filtros']['placa'] = $placa;
        }
    }

} catch (PDOException $e) {
    $response['error'] = "Error de base de datos: " . $e->getMessage();
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?>