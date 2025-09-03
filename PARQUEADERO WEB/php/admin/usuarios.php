<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db/db_connect.php';

try {
    $conn = getPDO();
    $stmt = $conn->query("
        SELECT 
            identificacion, 
            nombre_completo, 
            correo, 
            telefono, 
            fecha_registro, 
            activo 
        FROM usuario
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "usuarios" => $usuarios
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
