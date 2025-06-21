<?php
require '../db/db_connect.php';

header('Content-Type: application/json');

// Recibir datos JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_usuario'])) {
    echo json_encode(["success" => false, "error" => "ID de usuario no recibido"]);
    exit;
}

$id_usuario = $data['id_usuario'];

try {
    $stmt = $conn->prepare("
        SELECT u.nombre_completo, u.correo, u.telefono, t.nombre AS tipo_usuario
        FROM usuario u
        INNER JOIN tipousuario t ON u.id_tipo_usuario = t.id_tipo_usuario
        WHERE u.id_usuario = ?
    ");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        echo json_encode(["success" => true, "usuario" => $usuario]);
    } else {
        echo json_encode(["success" => false, "error" => "Usuario no encontrado"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Error en la base de datos: " . $e->getMessage()]);
}