<?php
require '../db/db_connect.php';
$conn = getPDO();  // ✅ conexión activa con PDO
$response = ['success' => false];

if (!isset($data['id_usuario'])) {
    echo json_encode(["success" => false, "error" => "ID de usuario no recibido"]);
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE usuario SET
            identificacion = :identificacion,
            nombre_completo = :nombre,
            correo = :correo,
            telefono = :telefono,
            id_tipo_usuario = :tipo,
            activo = :activo
        WHERE id_usuario = :id
    ");

    $stmt->execute([
        ':identificacion' => $_POST['identificacion'],
        ':nombre'         => $_POST['nombre'],
        ':correo'         => $_POST['correo'],
        ':telefono'       => $_POST['telefono'],
        ':tipo'           => $_POST['tipo'],
        ':activo'         => $_POST['activo'],
        ':id'             => $_POST['id_usuario']
    ]);

    $response['success'] = true;
} catch (PDOException $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
