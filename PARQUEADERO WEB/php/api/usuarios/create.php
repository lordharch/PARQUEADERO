<?php
require_once __DIR__ . '/../../db/db_connect.php';
$conn = getPDO();

$response = ["success" => false];

// Obtener el contenido JSON del body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Si no hay datos JSON, intentar con $_POST (para compatibilidad)
if (!$data) {
    $data = $_POST;
}

// Recoger variables con validación
$identificacion = $data['identificacion'] ?? null;
$nombre         = $data['nombre'] ?? null;
$correo         = $data['correo'] ?? null;
$telefono       = $data['telefono'] ?? null;
$tipo           = $data['tipo'] ?? null;
$activo         = $data['activo'] ?? 1;

// Validar que no falten datos
if (!$identificacion || !$nombre || !$correo || !$telefono || !$tipo) {
    $response["error"] = "Faltan datos obligatorios";
    echo json_encode($response);
    exit;
}

// Validaciones adicionales
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $response["error"] = "El correo electrónico no es válido";
    echo json_encode($response);
    exit;
}

if (!is_numeric($tipo)) {
    $response["error"] = "El tipo de usuario debe ser numérico";
    echo json_encode($response);
    exit;
}

try {
    // Verificar si ya existe un usuario con esa identificación
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE identificacion = :identificacion");
    $checkStmt->execute([':identificacion' => $identificacion]);
    
    if ($checkStmt->fetchColumn() > 0) {
        $response["error"] = "Ya existe un usuario con esa identificación";
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO usuario (identificacion, nombre_completo, correo, telefono, id_tipo_usuario, fecha_registro, activo)
        VALUES (:identificacion, :nombre, :correo, :telefono, :tipo, NOW(), :activo)
    ");

    $stmt->execute([
        ':identificacion' => $identificacion,
        ':nombre'         => $nombre,
        ':correo'         => $correo,
        ':telefono'       => $telefono,
        ':tipo'           => (int)$tipo,
        ':activo'         => (int)$activo
    ]);

    $response["success"] = true;
    $response["message"] = "Usuario creado exitosamente";
    $response["user_id"] = $conn->lastInsertId();
    
} catch (PDOException $e) {
    $response["error"] = "Error de base de datos: " . $e->getMessage();
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?>