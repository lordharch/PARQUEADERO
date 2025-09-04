<?php
require_once __DIR__ . '/../../db/db_connect.php';
$conn = getPDO();

$response = ["success" => false];

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['error'] = "Método no permitido";
    echo json_encode($response);
    exit;
}

// Obtener parámetros opcionales
$id_tipo = $_GET['id'] ?? $_GET['id_tipo_vehiculo'] ?? $_POST['id'] ?? $_POST['id_tipo_vehiculo'] ?? null;
$incluir_estadisticas = $_GET['estadisticas'] ?? $_POST['estadisticas'] ?? false;

// Validar parámetros
if ($id_tipo && !is_numeric($id_tipo)) {
    $response['error'] = "ID de tipo debe ser numérico";
    echo json_encode($response);
    exit;
}

try {
    if ($id_tipo) {
        // Obtener un tipo específico
        $stmt = $conn->prepare("
            SELECT 
                tv.id_tipo_vehiculo,
                tv.nombre,
                tv.tarifa_base,
                COUNT(v.placa) as vehiculos_registrados,
                COUNT(CASE WHEN v.autorizado = 1 THEN 1 END) as vehiculos_autorizados,
                (SELECT COUNT(*) FROM espacio e WHERE e.id_tipo_vehiculo = tv.id_tipo_vehiculo) as espacios_disponibles
            FROM tipovehiculo tv
            LEFT JOIN vehiculo v ON tv.id_tipo_vehiculo = v.id_tipo_vehiculo
            WHERE tv.id_tipo_vehiculo = ?
            GROUP BY tv.id_tipo_vehiculo, tv.nombre, tv.tarifa_base
        ");
        $stmt->execute([$id_tipo]);
        $tipo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tipo) {
            // Convertir valores numéricos
            $tipo['id_tipo_vehiculo'] = (int)$tipo['id_tipo_vehiculo'];
            $tipo['tarifa_base'] = (float)$tipo['tarifa_base'];
            $tipo['vehiculos_registrados'] = (int)$tipo['vehiculos_registrados'];
            $tipo['vehiculos_autorizados'] = (int)$tipo['vehiculos_autorizados'];
            $tipo['espacios_disponibles'] = (int)$tipo['espacios_disponibles'];
            
            $response['success'] = true;
            $response['tipo'] = $tipo;
        } else {
            $response['error'] = "Tipo de vehículo no encontrado";
        }
        
    } else {
        // Obtener todos los tipos
        if ($incluir_estadisticas) {
            // Con estadísticas detalladas
            $stmt = $conn->query("
                SELECT 
                    tv.id_tipo_vehiculo,
                    tv.nombre,
                    tv.tarifa_base,
                    COUNT(v.placa) as vehiculos_registrados,
                    COUNT(CASE WHEN v.autorizado = 1 THEN 1 END) as vehiculos_autorizados,
                    (SELECT COUNT(*) FROM espacio e WHERE e.id_tipo_vehiculo = tv.id_tipo_vehiculo) as espacios_totales,
                    (SELECT COUNT(*) FROM espacio e WHERE e.id_tipo_vehiculo = tv.id_tipo_vehiculo AND e.estado = 'disponible') as espacios_disponibles,
                    (SELECT COUNT(*) FROM espacio e WHERE e.id_tipo_vehiculo = tv.id_tipo_vehiculo AND e.estado = 'ocupado') as espacios_ocupados
                FROM tipovehiculo tv
                LEFT JOIN vehiculo v ON tv.id_tipo_vehiculo = v.id_tipo_vehiculo
                GROUP BY tv.id_tipo_vehiculo, tv.nombre, tv.tarifa_base
                ORDER BY tv.id_tipo_vehiculo
            ");
        } else {
            // Solo información básica
            $stmt = $conn->query("
                SELECT 
                    tv.id_tipo_vehiculo,
                    tv.nombre,
                    tv.tarifa_base,
                    COUNT(v.placa) as vehiculos_registrados
                FROM tipovehiculo tv
                LEFT JOIN vehiculo v ON tv.id_tipo_vehiculo = v.id_tipo_vehiculo
                GROUP BY tv.id_tipo_vehiculo, tv.nombre, tv.tarifa_base
                ORDER BY tv.id_tipo_vehiculo
            ");
        }
        
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir valores numéricos
        foreach ($tipos as &$tipo) {
            $tipo['id_tipo_vehiculo'] = (int)$tipo['id_tipo_vehiculo'];
            $tipo['tarifa_base'] = (float)$tipo['tarifa_base'];
            $tipo['vehiculos_registrados'] = (int)$tipo['vehiculos_registrados'];
            
            if ($incluir_estadisticas) {
                $tipo['vehiculos_autorizados'] = (int)$tipo['vehiculos_autorizados'];
                $tipo['espacios_totales'] = (int)$tipo['espacios_totales'];
                $tipo['espacios_disponibles'] = (int)$tipo['espacios_disponibles'];
                $tipo['espacios_ocupados'] = (int)$tipo['espacios_ocupados'];
            }
        }
        
        $response['success'] = true;
        $response['total'] = count($tipos);
        $response['tipos'] = $tipos;
        
        if ($incluir_estadisticas) {
            $response['resumen'] = [
                'total_tipos' => count($tipos),
                'total_vehiculos_sistema' => array_sum(array_column($tipos, 'vehiculos_registrados')),
                'tipos_con_vehiculos' => count(array_filter($tipos, function($t) { return $t['vehiculos_registrados'] > 0; }))
            ];
        }
    }
    
} catch (PDOException $e) {
    $response['error'] = "Error de base de datos: " . $e->getMessage();
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?>