<?php
require '../db/db_connect.php';

$response = ['success' => false];

try {
    $stmt = $conn->prepare("
        INSERT INTO vehiculo (placa, id_usuario, id_tipo_vehiculo, marca, modelo, color, fecha_registro)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $_POST['placa'],
        $_POST['id_usuario'],
        $_POST['id_tipo_vehiculo'],
        $_POST['marca'],
        $_POST['modelo'],
        $_POST['color']
    ]);

    $response['success'] = true;
} catch (PDOException $e) {
    $response['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
