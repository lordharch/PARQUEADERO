<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db/db_connect.php';
require_once __DIR__ . '/../db/auth_check.php';

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

    // Listar vehículos
    $stmt = $pdo->prepare("SELECT * FROM vehiculo WHERE id_usuario = ?");
    $stmt->execute([$user_id]);
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "vehiculos" => $vehiculos]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error en el servidor"]);
}
?>
