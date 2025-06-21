<?php
require '../db/db_connect.php';
header('Content-Type: application/json');

// Validación
if (!isset($_GET['placa'])) {
    echo json_encode(['success' => false, 'error' => 'Placa no especificada']);
    exit;
}

$placa = $_GET['placa'];

// Consulta
$sql = "SELECT v.placa, v.marca, v.modelo, v.color, v.id_tipo_vehiculo, v.autorizado, u.nombre_completo AS propietario
        FROM vehiculo v
        INNER JOIN usuario u ON v.id_usuario = u.id_usuario
        WHERE v.placa = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta']);
    exit;
}

$stmt->bind_param("s", $placa);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'vehiculo' => $row]);
} else {
    echo json_encode(['success' => false, 'error' => 'Vehículo no encontrado']);
}

$stmt->close();
$conn->close();
