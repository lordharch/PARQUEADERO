<?php
require '../db/db_connect.php';

$response = ['success' => false];

try {
    $stmt = $conn->query("SELECT id_tipo_vehiculo, nombre FROM tipovehiculo");
    $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['success'] = true;
    $response['tipos'] = $tipos;
} catch (PDOException $e) {
    $response['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
