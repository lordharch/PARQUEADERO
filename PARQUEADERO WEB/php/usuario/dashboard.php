<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db/db_connect.php';
require_once __DIR__ . '/../db/auth_check.php'; // verifica sesión

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "ID de usuario requerido"]);
    exit;
}

if (!isAuthorized($user_id)) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

try {
    $pdo = getPDO();

    // Datos del usuario
    $stmt = $pdo->prepare("SELECT id_usuario, nombre_completo, correo, telefono FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Cantidad de vehículos
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_vehiculos FROM vehiculo WHERE id_usuario = ?");
    $stmt->execute([$user_id]);
    $vehiculos = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "usuario" => $usuario,
        "total_vehiculos" => $vehiculos['total_vehiculos']
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error en el servidor"]);
}
?>
