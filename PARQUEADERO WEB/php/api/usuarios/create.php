<?php
require '../db/db_connect.php';
$conn = getPDO();

$response = ["success" => false];

// Recoger variables con validación
$identificacion = $_POST['identificacion'] ?? null;
$nombre         = $_POST['nombre'] ?? null;
$correo         = $_POST['correo'] ?? null;
$telefono       = $_POST['telefono'] ?? null;
$tipo           = $_POST['tipo'] ?? null;
$activo         = $_POST['activo'] ?? 1;

// Validar que no falten datos
if (!$identificacion || !$nombre || !$correo || !$telefono || !$tipo) {
    $response["error"] = "Faltan datos obligatorios";
    echo json_encode($response);
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO usuario (identificacion, nombre_completo, correo, telefono, id_tipo_usuario, fecha_registro, activo)
        VALUES (:identificacion, :nombre, :correo, :telefono, :tipo, NOW(), :activo)
    ");

    $stmt->execute([
        ':identificacion' => $identificacion,
        ':nombre'         => $nombre,
        ':correo'         => $correo,
        ':telefono'       => $telefono,
        ':tipo'           => $tipo,
        ':activo'         => $activo
    ]);

    $response["success"] = true;
} catch (PDOException $e) {
    $response["error"] = $e->getMessage();
}

echo json_encode($response);
?>
