<?php
require '../db/db_connect.php'; // Asegúrate que este archivo tenga la conexión $conn

header('Content-Type: application/json');

// Validar que se reciba la placa
if (!isset($_POST['placa'])) {
    echo json_encode(['success' => false, 'error' => 'Placa no recibida']);
    exit;
}

$placa = $_POST['placa'];

// Preparar y ejecutar eliminación
$sql = "DELETE FROM vehiculo WHERE placa = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Error en la preparación']);
    exit;
}

$stmt->bind_param("s", $placa);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No se pudo eliminar el vehículo']);
}

$stmt->close();
$conn->close();
