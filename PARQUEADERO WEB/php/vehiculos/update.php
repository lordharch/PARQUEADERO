<?php
require '../db/db_connect.php';
header('Content-Type: application/json');

// Validación
if (
    !isset($_POST['placa']) ||
    !isset($_POST['marca']) ||
    !isset($_POST['modelo']) ||
    !isset($_POST['color']) ||
    !isset($_POST['id_tipo_vehiculo']) ||
    !isset($_POST['autorizado'])
) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// Recibir datos
$placa = $_POST['placa'];
$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$color = $_POST['color'];
$id_tipo_vehiculo = intval($_POST['id_tipo_vehiculo']);
$autorizado = intval($_POST['autorizado']);

// Preparar consulta
$sql = "UPDATE vehiculo SET marca = ?, modelo = ?, color = ?, id_tipo_vehiculo = ?, autorizado = ? WHERE placa = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Error al preparar consulta']);
    exit;
}

$stmt->bind_param("sssdis", $marca, $modelo, $color, $id_tipo_vehiculo, $autorizado, $placa);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No se pudo actualizar el vehículo']);
}

$stmt->close();
$conn->close();
