<?php
require_once '../../db/db_connect.php';

$response = ['success' => false];

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['error'] = "Método no permitido";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Obtener ID del usuario desde diferentes fuentes
$id_usuario = null;

// Desde JSON body
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $id_usuario = $data['id_usuario'] ?? $data['id'] ?? null;
}

// Fallback: desde GET o POST
if (!$id_usuario) {
    $id_usuario = $_GET['id_usuario'] ?? $_GET['id'] ?? $_POST['id_usuario'] ?? $_POST['id'] ?? null;
}

if (!$id_usuario) {
    $response['error'] = "ID de usuario no proporcionado";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Validar que sea numérico
if (!is_numeric($id_usuario)) {
    $response['error'] = "ID de usuario debe ser numérico";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Parámetros de paginación
$limit = $_GET['limit'] ?? $_POST['limit'] ?? 10;
$offset = $_GET['offset'] ?? $_POST['offset'] ?? 0;

// Validar límites
$limit = max(1, min(50, (int)$limit)); // Entre 1 y 50
$offset = max(0, (int)$offset);

try {
    $conn = getPDO();
    
    // Verificar que el usuario existe
    $userCheck = $conn->prepare("SELECT nombre_completo FROM usuario WHERE id_usuario = ? AND activo = 1");
    $userCheck->execute([$id_usuario]);
    $usuario = $userCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        $response['error'] = "Usuario no encontrado o inactivo";
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Obtener el historial con más detalles
    $stmt = $conn->prepare("
        SELECT 
            a.id_acceso,
            a.fecha_entrada, 
            a.fecha_salida, 
            a.placa_vehiculo, 
            a.estado,
            a.observaciones,
            e.codigo_espacio,
            z.nombre AS zona_nombre,
            v.marca,
            v.modelo,
            v.color,
            tv.nombre AS tipo_vehiculo,
            t.total_pagado,
            t.tiempo_estadia,
            t.fecha_transaccion,
            CASE 
                WHEN a.fecha_salida IS NULL THEN NULL
                ELSE TIMESTAMPDIFF(MINUTE, a.fecha_entrada, a.fecha_salida)
            END AS duracion_minutos
        FROM acceso a
        INNER JOIN vehiculo v ON a.placa_vehiculo = v.placa
        INNER JOIN tipovehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
        LEFT JOIN espacio e ON a.id_espacio = e.id_espacio
        LEFT JOIN zonaestacionamiento z ON e.id_zona = z.id_zona
        LEFT JOIN transaccion t ON a.id_acceso = t.id_acceso
        WHERE v.id_usuario = ?
        ORDER BY a.fecha_entrada DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$id_usuario, $limit, $offset]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear los datos
    foreach ($historial as &$registro) {
        $registro['id_acceso'] = (int)$registro['id_acceso'];
        $registro['total_pagado'] = $registro['total_pagado'] ? (float)$registro['total_pagado'] : 0;
        $registro['tiempo_estadia'] = $registro['tiempo_estadia'] ? (int)$registro['tiempo_estadia'] : 0;
        $registro['duracion_minutos'] = $registro['duracion_minutos'] ? (int)$registro['duracion_minutos'] : null;
        
        // Formatear fechas
        if ($registro['fecha_entrada']) {
            $registro['fecha_entrada_formateada'] = date('d/m/Y H:i', strtotime($registro['fecha_entrada']));
        }
        
        if ($registro['fecha_salida']) {
            $registro['fecha_salida_formateada'] = date('d/m/Y H:i', strtotime($registro['fecha_salida']));
        }
        
        if ($registro['fecha_transaccion']) {
            $registro['fecha_transaccion_formateada'] = date('d/m/Y H:i', strtotime($registro['fecha_transaccion']));
        }
        
        // Estado textual
        $estados = [
            'activo' => 'En parqueadero',
            'finalizado' => 'Finalizado',
            'anomalia' => 'Con anomalía'
        ];
        $registro['estado_texto'] = $estados[$registro['estado']] ?? $registro['estado'];
        
        // Duración textual
        if ($registro['duracion_minutos']) {
            $horas = floor($registro['duracion_minutos'] / 60);
            $minutos = $registro['duracion_minutos'] % 60;
            $registro['duracion_texto'] = $horas > 0 ? "{$horas}h {$minutos}m" : "{$minutos}m";
        } else {
            $registro['duracion_texto'] = $registro['estado'] === 'activo' ? 'En curso' : 'No disponible';
        }
    }
    
    // Contar total de registros para paginación
    $countStmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM acceso a
        INNER JOIN vehiculo v ON a.placa_vehiculo = v.placa
        WHERE v.id_usuario = ?
    ");
    $countStmt->execute([$id_usuario]);
    $totalRegistros = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $response['success'] = true;
    $response['usuario'] = $usuario['nombre_completo'];
    $response['historial'] = $historial;
    $response['paginacion'] = [
        'total_registros' => (int)$totalRegistros,
        'registros_mostrados' => count($historial),
        'limit' => $limit,
        'offset' => $offset,
        'tiene_mas' => ($offset + $limit) < $totalRegistros
    ];
    $response['message'] = "Historial obtenido exitosamente";
    
} catch (PDOException $e) {
    $response['error'] = "Error en la base de datos: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>