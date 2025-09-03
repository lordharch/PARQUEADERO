<?php
require '../db/db_connect.php';
$conn = getPDO();  // ✅ conexión activa con PDO

header('Content-Type: application/json');

// Recibir datos JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_usuario'])) {
    echo json_encode(["success" => false, "error" => "ID de usuario no recibido"]);
    exit;
}

$id_usuario = $data['id_usuario'];

try {
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
    $gastos['total_gastado'] = $gastos['total_gastado'] ?? 0;

    // 4. Total de visitas
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_visitas
        FROM acceso a
        INNER JOIN vehiculo v ON a.placa_vehiculo = v.placa
        WHERE v.id_usuario = ?
    ");
    $stmt->execute([$id_usuario]);
    $visitas = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "estadisticas" => [
            "total_vehiculos"    => (int)$vehiculos['total_vehiculos'],
            "vehiculos_activos"  => (int)$activos['activos'],
            "gastos_mes"         => (float)$gastos['total_gastado'],
            "total_visitas"      => (int)$visitas['total_visitas']
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Error en la base de datos: " . $e->getMessage()]);
}
