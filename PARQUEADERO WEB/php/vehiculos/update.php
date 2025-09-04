<?php
require '../db/db_connect.php';
$conn = getPDO();

header('Content-Type: application/json');
$response = ["success" => false];

// Leer JSON de la petición
$data = json_decode(file_get_contents("php://input"), true);

// Validación
$placa           = $data['placa'] ?? null;
$marca           = $data['marca'] ?? null;
$modelo          = $data['modelo'] ?? null;
$color           = $data['color'] ?? null;
$id_tipo_vehiculo = $data['id_tipo_vehiculo'] ?? null;
$autorizado      = $data['autorizado'] ?? null;

if (!$placa || !$marca || !$modelo || !$color || !$id_tipo_vehiculo || $autorizado === null) {
    $response["error"] = "Datos incompletos";
    echo json_encode($response);
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE vehiculo 
        SET marca = :marca, modelo = :modelo, color = :color, 
            id_tipo_vehiculo = :id_tipo_vehiculo, autorizado = :autorizado 
        WHERE placa = :placa
    ");

    $stmt->execute([
        ":marca" => $marca,
        ":modelo" => $modelo,
        ":color" => $color,
        ":id_tipo_vehiculo" => $id_tipo_vehiculo,
        ":autorizado" => $autorizado,
        ":placa" => $placa
    ]);

    if ($stmt->rowCount() > 0) {
        $response["success"] = true;
    } else {
        $response["error"] = "No se encontró el vehículo o no hubo cambios";
    }
} catch (PDOException $e) {
    $response["error"] = $e->getMessage();
}

echo json_encode($response);
