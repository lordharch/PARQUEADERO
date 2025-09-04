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

// Obtener parámetros desde diferentes fuentes
$placa = $_GET['placa'] ?? $_POST['placa'] ?? null;
$id_usuario = $_GET['id_usuario'] ?? $_POST['id_usuario'] ?? null;
$id_tipo = $_GET['id_tipo_vehiculo'] ?? $_POST['id_tipo_vehiculo'] ?? null;
$limit = $_GET['limit'] ?? $_POST['limit'] ?? 50;
$offset = $_GET['offset'] ?? $_POST['offset'] ?? 0;

// Normalizar placa si se proporciona
if ($placa) {
    $placa = strtoupper(trim($placa));
}

// Validar parámetros numéricos
if ($id_usuario && !is_numeric($id_usuario)) {
    $response['error'] = "ID de usuario debe ser numérico";
    echo json_encode($response);
    exit;
}

if ($id_tipo && !is_numeric($id_tipo)) {
    $response['error'] = "ID de tipo de vehículo debe ser numérico";
    echo json_encode($response);
    exit;
}

// Validar limit y offset
$limit = min(max((int)$limit, 1), 100); // Entre 1 y 100
$offset = max((int)$offset, 0);

try {
    if ($placa) {
        // Buscar un vehículo específico por placa
        $stmt = $conn->prepare("
            SELECT 
                v.placa,
                v.marca,
                v.modelo,
                v.color,
                v.fecha_registro,
                v.autorizado,
                u.id_usuario,
                u.nombre_completo as propietario,
                u.telefono as telefono_propietario,
                tv.nombre as tipo_vehiculo,
                tv.tarifa_base,
                (SELECT COUNT(*) FROM acceso a WHERE a.placa_vehiculo = v.placa) as total_visitas,
                (SELECT COUNT(*) FROM acceso a WHERE a.placa_vehiculo = v.placa AND a.estado = 'activo') as accesos_activos
            FROM vehiculo v
            INNER JOIN usuario u ON v.id_usuario = u.id_usuario
            INNER JOIN tipovehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
            WHERE v.placa = ?
        ");
        $stmt->execute([$placa]);
        $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vehiculo) {
            $response['success'] = true;
            $response['vehiculo'] = $vehiculo;
        } else {
            $response['error'] = "Vehículo no encontrado";
        }
        
    } else {
        // Construir query dinámico para filtros
        $whereConditions = [];
        $params = [];
        
        if ($id_usuario) {
            $whereConditions[] = "v.id_usuario = ?";
            $params[] = $id_usuario;
        }
        
        if ($id_tipo) {
            $whereConditions[] = "v.id_tipo_vehiculo = ?";
            $params[] = $id_tipo;
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        // Query principal con paginación
        $stmt = $conn->prepare("
            SELECT 
                v.placa,
                v.marca,
                v.modelo,
                v.color,
                v.fecha_registro,
                v.autorizado,
                u.id_usuario,
                u.nombre_completo as propietario,
                tv.nombre as tipo_vehiculo,
                tv.tarifa_base,
                (SELECT COUNT(*) FROM acceso a WHERE a.placa_vehiculo = v.placa) as total_visitas,
                (SELECT COUNT(*) FROM acceso a WHERE a.placa_vehiculo = v.placa AND a.estado = 'activo') as esta_en_parqueadero
            FROM vehiculo v
            INNER JOIN usuario u ON v.id_usuario = u.id_usuario
            INNER JOIN tipovehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
            $whereClause
            ORDER BY v.fecha_registro DESC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Contar total de registros para paginación
        $countStmt = $conn->prepare("
            SELECT COUNT(*) as total
            FROM vehiculo v
            INNER JOIN usuario u ON v.id_usuario = u.id_usuario
            INNER JOIN tipovehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
            $whereClause
        ");
        $countParams = array_slice($params, 0, -2); // Remover limit y offset
        $countStmt->execute($countParams);
        $totalRegistros = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $response['success'] = true;
        $response['total'] = (int)$totalRegistros;
        $response['showing'] = count($vehiculos);
        $response['limit'] = $limit;
        $response['offset'] = $offset;
        $response['has_more'] = ($offset + $limit) < $totalRegistros;
        $response['vehiculos'] = $vehiculos;
        
        // Agregar filtros aplicados en la respuesta
        if (!empty($whereConditions)) {
            $response['filtros'] = [];
            if ($id_usuario) $response['filtros']['id_usuario'] = (int)$id_usuario;
            if ($id_tipo) $response['filtros']['id_tipo_vehiculo'] = (int)$id_tipo;
        }
    }
    
} catch (PDOException $e) {
    $response['error'] = "Error de base de datos: " . $e->getMessage();
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?>