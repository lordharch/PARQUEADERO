<?php
require '../db/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['correo']) || !isset($data['password'])) {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
    exit;
}

$correo = $data['correo'];
$password = $data['password'];

try {
    $stmt = $conn->prepare("SELECT id_usuario, nombre_completo, password FROM usuario WHERE correo = ?");
    $stmt->execute([$correo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode([
            "success" => true,
            "id_usuario" => $user['id_usuario'],
            "nombre" => $user['nombre_completo']
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "Correo o contraseña incorrectos"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Error al verificar: " . $e->getMessage()]);
}
?>
