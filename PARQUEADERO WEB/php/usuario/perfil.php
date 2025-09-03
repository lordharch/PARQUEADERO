<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db/db_connect.php';
require_once __DIR__ . '/../db/auth_check.php'; // <- añade esto

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

    // Ver perfil
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare("SELECT id_usuario, nombre_completo, correo, telefono FROM usuario WHERE id_usuario = ?");
        $stmt->execute([$user_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(["status" => "success", "usuario" => $usuario]);
    }

    // Actualizar perfil
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $nombre = htmlspecialchars(trim($data['nombre_completo'] ?? ''));
        $correo = filter_var($data['correo'] ?? '', FILTER_VALIDATE_EMAIL);
        $telefono = htmlspecialchars(trim($data['telefono'] ?? ''));

        if (!$nombre || !$correo || !$telefono) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE usuario SET nombre_completo = ?, correo = ?, telefono = ? WHERE id_usuario = ?");
        $stmt->execute([$nombre, $correo, $telefono, $user_id]);

        echo json_encode(["status" => "success", "message" => "Perfil actualizado"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error en el servidor"]);
}
?>
