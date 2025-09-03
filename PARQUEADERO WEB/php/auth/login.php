<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db/db_connect.php';
require_once __DIR__ . '/../db/auth_check.php'; // <- agregar esto

$data = json_decode(file_get_contents('php://input'), true);

$correo = filter_var($data['correo'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $data['password'] ?? '';

if (!$correo || !$password) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT id_usuario, nombre_completo, password FROM usuario WHERE correo = ?");
    $stmt->execute([$correo]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode([
            "status" => "success",
            "data" => [
                "id_usuario" => $user['id_usuario'],
                "nombre" => $user['nombre_completo']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Correo o contraseña incorrectos"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error en el servidor"]);
}
?>