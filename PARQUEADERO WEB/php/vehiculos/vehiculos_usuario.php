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
        SELECT v.placa, tv.nombre AS tipo, v.marca, v.modelo, v.color, v.autorizado
        FROM vehiculo v
        INNER JOIN tipovehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
        WHERE v.id_usuario = ?
    ");
    $stmt->execute([$id_usuario]);
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "vehiculos" => $vehiculos
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => "Error en la base de datos: " . $e->getMessage()
    ]);
}
