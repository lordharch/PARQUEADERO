<?php
require '../db/db_connect.php';

header('Content-Type: application/json');

// Recibir datos JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_usuario'])) {
    echo json_encode(["success" => false, "error" => "ID de usuario no recibido"]);
    exit;
}

$id_usuario = $data['id_usuario'];

try {
    $stmt = $conn->prepare("
        SELECT 
            a.fecha_entrada, 
            a.fecha_salida, 
            a.placa_vehiculo, 
            e.codigo_espacio, 
            a.estado, 
            t.total_pagado
        FROM acceso a
        INNER JOIN vehiculo v ON a.placa_vehiculo = v.placa
        LEFT JOIN espacio e ON a.id_espacio = e.id_espacio
        LEFT JOIN transaccion t ON a.id_acceso = t.id_acceso
        WHERE v.id_usuario = ?
        ORDER BY a.fecha_entrada DESC
        LIMIT 10
    ");
    $stmt->execute([$id_usuario]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "historial" => $historial
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Error en base de datos: " . $e->getMessage()]);
}
