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
$id_zona = $data['id_zona'] ?? null;
$incluir_estadisticas_detalladas = filter_var($data['estadisticas_detalladas'] ?? false, FILTER_VALIDATE_BOOLEAN);
$incluir_tipos_vehiculo = filter_var($data['tipos_vehiculo'] ?? false, FILTER_VALIDATE_BOOLEAN);

try {
    if ($id_zona) {
        // Dashboard para una zona específica con más detalles
        $stmt = $conn->prepare("
            SELECT 
                z.id_zona,
                z.nombre AS nombre_zona,
                z.capacidad_maxima,
                COUNT(e.id_espacio) AS total_espacios,
                SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles,
                SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) AS ocupados,
                SUM(CASE WHEN e.estado = 'reservado' THEN 1 ELSE 0 END) AS reservados,
                SUM(CASE WHEN e.estado = 'mantenimiento' THEN 1 ELSE 0 END) AS mantenimiento,
                ROUND((SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) / GREATEST(COUNT(e.id_espacio), 1)) * 100, 2) AS porcentaje_ocupacion,
                ROUND((SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) / GREATEST(COUNT(e.id_espacio), 1)) * 100, 2) AS porcentaje_disponible,
                (z.capacidad_maxima - COUNT(e.id_espacio)) AS capacidad_sin_utilizar
            FROM zonaestacionamiento z
            LEFT JOIN espacio e ON z.id_zona = e.id_zona
            WHERE z.id_zona = ?
            GROUP BY z.id_zona, z.nombre, z.capacidad_maxima
        ");
        $stmt->execute([$id_zona]);
        $zona = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($zona) {
            // Convertir valores numéricos
            $zona['id_zona'] = (int)$zona['id_zona'];
            $zona['capacidad_maxima'] = (int)$zona['capacidad_maxima'];
            $zona['total_espacios'] = (int)$zona['total_espacios'];
            $zona['disponibles'] = (int)$zona['disponibles'];
            $zona['ocupados'] = (int)$zona['ocupados'];
            $zona['reservados'] = (int)$zona['reservados'];
            $zona['mantenimiento'] = (int)$zona['mantenimiento'];
            $zona['capacidad_sin_utilizar'] = (int)$zona['capacidad_sin_utilizar'];
            $zona['porcentaje_ocupacion'] = (float)$zona['porcentaje_ocupacion'];
            $zona['porcentaje_disponible'] = (float)$zona['porcentaje_disponible'];

            // Incluir estadísticas por tipo de vehículo si se solicita
            if ($incluir_tipos_vehiculo) {
                $tiposStmt = $conn->prepare("
                    SELECT 
                        tv.nombre AS tipo_vehiculo,
                        COUNT(e.id_espacio) AS total_espacios,
                        SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles,
                        SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) AS ocupados,
                        SUM(CASE WHEN e.estado = 'reservado' THEN 1 ELSE 0 END) AS reservados
                    FROM espacio e
                    INNER JOIN tipovehiculo tv ON e.id_tipo_vehiculo = tv.id_tipo_vehiculo
                    WHERE e.id_zona = ?
                    GROUP BY tv.id_tipo_vehiculo, tv.nombre
                    ORDER BY tv.nombre
                ");
                $tiposStmt->execute([$id_zona]);
                $zona['espacios_por_tipo'] = $tiposStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $response['success'] = true;
            $response['zona'] = $zona;
        } else {
            $response['error'] = "Zona no encontrada";
        }

    } else {
        // Dashboard general de todas las zonas (versión mejorada)
        $stmt = $conn->prepare("
            SELECT 
                z.id_zona,
                z.nombre AS nombre_zona,
                z.capacidad_maxima,
                COUNT(e.id_espacio) AS total_espacios,
                SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles,
                SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) AS ocupados,
                SUM(CASE WHEN e.estado = 'reservado' THEN 1 ELSE 0 END) AS reservados,
                SUM(CASE WHEN e.estado = 'mantenimiento' THEN 1 ELSE 0 END) AS mantenimiento,
                ROUND((SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) / GREATEST(COUNT(e.id_espacio), 1)) * 100, 2) AS porcentaje_ocupacion,
                ROUND((SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) / GREATEST(COUNT(e.id_espacio), 1)) * 100, 2) AS porcentaje_disponible
            FROM zonaestacionamiento z
            LEFT JOIN espacio e ON z.id_zona = e.id_zona
            GROUP BY z.id_zona, z.nombre, z.capacidad_maxima
            ORDER BY z.nombre
        ");
        $stmt->execute();
        $zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir valores numéricos
        foreach ($zonas as &$zona) {
            $zona['id_zona'] = (int)$zona['id_zona'];
            $zona['capacidad_maxima'] = (int)$zona['capacidad_maxima'];
            $zona['total_espacios'] = (int)$zona['total_espacios'];
            $zona['disponibles'] = (int)$zona['disponibles'];
            $zona['ocupados'] = (int)$zona['ocupados'];
            $zona['reservados'] = (int)$zona['reservados'];
            $zona['mantenimiento'] = (int)$zona['mantenimiento'];
            $zona['porcentaje_ocupacion'] = (float)$zona['porcentaje_ocupacion'];
            $zona['porcentaje_disponible'] = (float)$zona['porcentaje_disponible'];
        }

        // Estadísticas generales detalladas si se solicitan
        if ($incluir_estadisticas_detalladas) {
            $statsStmt = $conn->prepare("
                SELECT 
                    COUNT(DISTINCT z.id_zona) as total_zonas,
                    SUM(z.capacidad_maxima) as capacidad_total_sistema,
                    COUNT(e.id_espacio) as espacios_totales_creados,
                    SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) as disponibles_totales,
                    SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) as ocupados_totales,
                    SUM(CASE WHEN e.estado = 'reservado' THEN 1 ELSE 0 END) as reservados_totales,
                    SUM(CASE WHEN e.estado = 'mantenimiento' THEN 1 ELSE 0 END) as mantenimiento_totales,
                    ROUND((SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) / GREATEST(COUNT(e.id_espacio), 1)) * 100, 2) AS ocupacion_total_porcentaje,
                    ROUND((SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) / GREATEST(COUNT(e.id_espacio), 1)) * 100, 2) AS disponible_total_porcentaje,
                    (SUM(z.capacidad_maxima) - COUNT(e.id_espacio)) AS capacidad_sin_utilizar_total
                FROM zonaestacionamiento z
                LEFT JOIN espacio e ON z.id_zona = e.id_zona
            ");
            $statsStmt->execute();
            $estadisticas = $statsStmt->fetch(PDO::FETCH_ASSOC);

            // Convertir valores numéricos
            $estadisticas['total_zonas'] = (int)$estadisticas['total_zonas'];
            $estadisticas['capacidad_total_sistema'] = (int)$estadisticas['capacidad_total_sistema'];
            $estadisticas['espacios_totales_creados'] = (int)$estadisticas['espacios_totales_creados'];
            $estadisticas['disponibles_totales'] = (int)$estadisticas['disponibles_totales'];
            $estadisticas['ocupados_totales'] = (int)$estadisticas['ocupados_totales'];
            $estadisticas['reservados_totales'] = (int)$estadisticas['reservados_totales'];
            $estadisticas['mantenimiento_totales'] = (int)$estadisticas['mantenimiento_totales'];
            $estadisticas['capacidad_sin_utilizar_total'] = (int)$estadisticas['capacidad_sin_utilizar_total'];
            $estadisticas['ocupacion_total_porcentaje'] = (float)$estadisticas['ocupacion_total_porcentaje'];
            $estadisticas['disponible_total_porcentaje'] = (float)$estadisticas['disponible_total_porcentaje'];

            // Estadísticas por tipo de vehículo
            $tiposStmt = $conn->prepare("
                SELECT 
                    tv.nombre AS tipo_vehiculo,
                    COUNT(e.id_espacio) AS total_espacios,
                    SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles,
                    SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) AS ocupados,
                    SUM(CASE WHEN e.estado = 'reservado' THEN 1 ELSE 0 END) AS reservados,
                    ROUND((SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) / GREATEST(COUNT(e.id_espacio), 1)) * 100, 2) AS porcentaje_ocupacion
                FROM espacio e
                INNER JOIN tipovehiculo tv ON e.id_tipo_vehiculo = tv.id_tipo_vehiculo
                GROUP BY tv.id_tipo_vehiculo, tv.nombre
                ORDER BY tv.nombre
            ");
            $tiposStmt->execute();
            $estadisticas['espacios_por_tipo_vehiculo'] = $tiposStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $response['success'] = true;
        $response['zonas'] = $zonas;
        
        if ($incluir_estadisticas_detalladas) {
            $response['estadisticas_detalladas'] = $estadisticas;
        }
    }

} catch (PDOException $e) {
    $response['error'] = "Error de base de datos: " . $e->getMessage();
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?>