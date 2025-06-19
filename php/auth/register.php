<?php
require '../db/db_connect.php';

// Recibir datos JSON
$data = json_decode(file_get_contents('php://input'), true);

// Debug opcional para revisar entradas
file_put_contents('debug_input.txt', file_get_contents('php://input'));

if(!$data) {
    echo json_encode(["success" => false, "error" => "Datos inválidos"]);
    exit;
}

// 🚫 Validar tipo de usuario permitido
$tiposPermitidos = [1, 2, 3]; // Regular, Premium, Empleado
$idTipo = intval($data['usuario']['id_tipo_usuario']);
if (!in_array($idTipo, $tiposPermitidos)) {
    echo json_encode(["success" => false, "error" => "Tipo de usuario no permitido."]);
    exit;
}

try {
    // 1. Registrar USUARIO
    $passwordHash = password_hash($data['usuario']['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuario (
        identificacion, 
        nombre_completo, 
        correo, 
        telefono, 
        id_tipo_usuario,
        password,
        fecha_registro,
        activo
    ) VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)");

    $stmt->execute([
        $data['usuario']['identificacion'],
        $data['usuario']['nombre_completo'],
        $data['usuario']['correo'],
        $data['usuario']['telefono'],
        $idTipo,
        $passwordHash
    ]);
    
    $user_id = $conn->lastInsertId();
    
    // 2. Registrar VEHICULO
    $stmt = $conn->prepare("INSERT INTO vehiculo (
        placa, 
        id_usuario, 
        id_tipo_vehiculo, 
        marca, 
        modelo, 
        color, 
        fecha_registro,
        autorizado
    ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
    
    $stmt->execute([
        $data['vehiculo']['placa'],
        $user_id,
        $data['vehiculo']['id_tipo_vehiculo'],
        $data['vehiculo']['marca'],
        $data['vehiculo']['modelo'],
        $data['vehiculo']['color'],
        $data['vehiculo']['autorizado'] ? 1 : 0
    ]);
    
    echo json_encode([
        "success" => true,
        "user_id" => $user_id
    ]);
    
} catch(PDOException $e) {
    if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'identificacion')) {
        echo json_encode([
            "success" => false,
            "error" => "La identificación ingresada ya está registrada."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Error en registro: " . $e->getMessage()
        ]);
    }
}