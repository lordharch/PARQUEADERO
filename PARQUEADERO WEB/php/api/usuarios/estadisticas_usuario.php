<?php
require_once '../../db/db_connect.php';

$response = ['success' => false];

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['error'] = "Método no permitido";
    echo json_encode($response);
    exit;
}

// Obtener ID del usuario desde diferentes fuentes
$id_usuario = null;

// Desde JSON body
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $id_usuario = $data['id'] ?? $data['id_usuario'] ?? null;
}

// Fallback: desde GET o POST (acepta tanto 'id' como 'id_usuario')
if (!$id_usuario) {
    $id_usuario = $_GET['id'] ?? $_GET['id_usuario'] ?? $_POST['id'] ?? $_POST['id_usuario'] ?? null;
}

if (!$id_usuario) {
    $response['error'] = "ID de usuario no proporcionado";
    echo json_encode($response);
    exit;
}

// Validar que sea numérico
if (!is_numeric($id_usuario)) {
    $response['error'] = "ID de usuario debe ser numérico";
    echo json_encode($response);
    exit;
}

try {
    $conn = getPDO();
    
    // Verificar que el usuario existe
    $userCheck = $conn->prepare("SELECT nombre_completo FROM usuario WHERE id_usuario = ? AND activo = 1");
    $userCheck->execute([$id_usuario]);
    $usuario = $userCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        $response['error'] = "Usuario no encontrado o inactivo";
        echo json_encode($response);
        exit;
    }

    // 1. Total de vehículos
    $stmt = $conn->prepare("SELECT COUNT(*) as total_vehiculos FROM vehiculo WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $vehiculos = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Vehículos actualmente estacionados
    $stmt = $conn->prepare("
        SELECT COUNT(*) as activos 
        FROM acceso a
        INNER JOIN vehiculo v ON a.placa_vehiculo = v.placa
        WHERE v.id_usuario = ? AND a.estado = 'activo'
    ");
    $stmt->execute([$id_usuario]);
    $activos = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Gastos del mes actual
    $stmt = $conn->prepare("
        SELECT SUM(t.total_pagado) as total_gastado
        FROM transaccion t
        INNER JOIN acceso a ON t.id_acceso = a.id_acceso
        INNER JOIN vehiculo v ON a.placa_vehiculo = v.placa
        WHERE v.id_usuario = ?
          AND MONTH(t.fecha_transaccion) = MONTH(CURDATE())
          AND YEAR(t.fecha_transaccion) = YEAR(CURDATE())
    ");
    $stmt->execute([$id_usuario]);
    $gastos = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Total de visitas
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_visitas
        FROM acceso a
        INNER JOIN vehiculo v ON a.placa_vehiculo = v.placa
        WHERE v.id_usuario = ?
    ");
    $stmt->execute([$id_usuario]);
    $visitas = $stmt->fetch(PDO::FETCH_ASSOC);

    // 5. Última visita
    $stmt = $conn->prepare("
        SELECT a.fecha_entrada, v.placa
        FROM acceso a
        INNER JOIN vehiculo v ON a.placa_vehiculo = v.placa
        WHERE v.id_usuario = ?
        ORDER BY a.fecha_entrada DESC
        LIMIT 1
    ");
    $stmt->execute([$id_usuario]);
    $ultimaVisita = $stmt->fetch(PDO::FETCH_ASSOC);

    // 6. Promedio de tiempo de estacionamiento
    $stmt = $conn->prepare("
        SELECT AVG(TIMESTAMPDIFF(MINUTE, a.fecha_entrada, a.fecha_salida)) as promedio_minutos
        FROM acceso a
        INNER JOIN vehiculo v ON a.placa_vehiculo = v.placa
        WHERE v.id_usuario = ? AND a.fecha_salida IS NOT NULL
    ");
    $stmt->execute([$id_usuario]);
    $promedio = $stmt->fetch(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['usuario'] = $usuario['nombre_completo'];
    $response['estadisticas'] = [
        "total_vehiculos"      => (int)$vehiculos['total_vehiculos'],
        "vehiculos_activos"    => (int)$activos['activos'],
        "gastos_mes_actual"    => round((float)($gastos['total_gastado'] ?? 0), 2),
        "total_visitas"        => (int)$visitas['total_visitas'],
        "ultima_visita"        => $ultimaVisita ? [
            "fecha" => $ultimaVisita['fecha_entrada'],
            "placa" => $ultimaVisita['placa']
        ] : null,
        "promedio_estacionamiento" => $promedio['promedio_minutos'] ? 
            round((float)$promedio['promedio_minutos'], 0) . " minutos" : "Sin datos"
    ];
    
} catch (PDOException $e) {
    $response['error'] = "Error en la base de datos: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>