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
$incluir_detalles = filter_var($data['incluir_detalles'] ?? false, FILTER_VALIDATE_BOOLEAN);
$incluir_estadisticas = filter_var($data['incluir_estadisticas'] ?? true, FILTER_VALIDATE_BOOLEAN);

try {
    if ($id_zona) {
        // Dashboard para una zona específica
        $stmt = $conn->prepare("
            SELECT 
                z.id_zona, 
                z.nombre, 
                z.capacidad_maxima,
                COUNT(e.id_espacio) AS total_espacios,
                SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) AS ocupados,
                SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles,
                SUM(CASE WHEN e.estado = 'mantenimiento' THEN 1 ELSE 0 END) AS mantenimiento,
                SUM(CASE WHEN e.estado = 'reservado' THEN 1 ELSE 0 END) AS reservados,
                ROUND((SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) / COUNT(e.id_espacio)) * 100, 2) AS porcentaje_ocupacion
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
            $zona['ocupados'] = (int)$zona['ocupados'];
            $zona['disponibles'] = (int)$zona['disponibles'];
            $zona['mantenimiento'] = (int)$zona['mantenimiento'];
            $zona['reservados'] = (int)$zona['reservados'];
            $zona['porcentaje_ocupacion'] = (float)$zona['porcentaje_ocupacion'];

            // Incluir detalles de espacios si se solicita
            if ($incluir_detalles) {
                $detallesStmt = $conn->prepare("
                    SELECT 
                        e.id_espacio,
                        e.codigo_espacio,
                        e.estado,
                        tv.nombre as tipo_vehiculo,
                        tv.tarifa_base,
                        a.placa_vehiculo,
                        a.fecha_entrada,
                        TIMESTAMPDIFF(MINUTE, a.fecha_entrada, NOW()) as minutos_ocupado
                    FROM espacio e
                    INNER JOIN tipovehiculo tv ON e.id_tipo_vehiculo = tv.id_tipo_vehiculo
                    LEFT JOIN acceso a ON e.id_espacio = a.id_espacio AND a.estado = 'activo'
                    WHERE e.id_zona = ?
                    ORDER BY e.codigo_espacio
                ");
                $detallesStmt->execute([$id_zona]);
                $zona['detalles_espacios'] = $detallesStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $response['success'] = true;
            $response['zona'] = $zona;
        } else {
            $response['error'] = "Zona no encontrada";
        }

    } else {
        // Dashboard general de todas las zonas
        $stmt = $conn->prepare("
            SELECT 
                z.id_zona, 
                z.nombre, 
                z.capacidad_maxima,
                COUNT(e.id_espacio) AS total_espacios,
                SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) AS ocupados,
                SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles,
                SUM(CASE WHEN e.estado = 'mantenimiento' THEN 1 ELSE 0 END) AS mantenimiento,
                SUM(CASE WHEN e.estado = 'reservado' THEN 1 ELSE 0 END) AS reservados,
                ROUND((SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) / GREATEST(COUNT(e.id_espacio), 1)) * 100, 2) AS porcentaje_ocupacion
            FROM zonaestacionamiento z
            LEFT JOIN espacio e ON z.id_zona = e.id_zona
            GROUP BY z.id_zona, z.nombre, z.capacidad_maxima
            ORDER BY z.id_zona
        ");
        $stmt->execute();
        $zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir valores numéricos
        foreach ($zonas as &$zona) {
            $zona['id_zona'] = (int)$zona['id_zona'];
            $zona['capacidad_maxima'] = (int)$zona['capacidad_maxima'];
            $zona['total_espacios'] = (int)$zona['total_espacios'];
            $zona['ocupados'] = (int)$zona['ocupados'];
            $zona['disponibles'] = (int)$zona['disponibles'];
            $zona['mantenimiento'] = (int)$zona['mantenimiento'];
            $zona['reservados'] = (int)$zona['reservados'];
            $zona['porcentaje_ocupacion'] = (float)$zona['porcentaje_ocupacion'];
        }

        // Estadísticas generales si se solicitan
        if ($incluir_estadisticas) {
            $statsStmt = $conn->prepare("
                SELECT 
                    COUNT(DISTINCT z.id_zona) as total_zonas,
                    SUM(z.capacidad_maxima) as capacidad_total,
                    COUNT(e.id_espacio) as espacios_totales,
                    SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) as ocupados_totales,
                    SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) as disponibles_totales,
                    ROUND((SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) / GREATEST(COUNT(e.id_espacio), 1)) * 100, 2) AS ocupacion_total_porcentaje
                FROM zonaestacionamiento z
                LEFT JOIN espacio e ON z.id_zona = e.id_zona
            ");
            $statsStmt->execute();
            $estadisticas = $statsStmt->fetch(PDO::FETCH_ASSOC);

            // Convertir valores numéricos
            $estadisticas['total_zonas'] = (int)$estadisticas['total_zonas'];
            $estadisticas['capacidad_total'] = (int)$estadisticas['capacidad_total'];
            $estadisticas['espacios_totales'] = (int)$estadisticas['espacios_totales'];
            $estadisticas['ocupados_totales'] = (int)$estadisticas['ocupados_totales'];
            $estadisticas['disponibles_totales'] = (int)$estadisticas['disponibles_totales'];
            $estadisticas['ocupacion_total_porcentaje'] = (float)$estadisticas['ocupacion_total_porcentaje'];
        }

        $response['success'] = true;
        $response['zonas'] = $zonas;
        
        if ($incluir_estadisticas) {
            $response['estadisticas_totales'] = $estadisticas;
        }
    }

} catch (PDOException $e) {
    $response['error'] = "Error de base de datos: " . $e->getMessage();
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?>