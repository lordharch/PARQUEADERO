<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

try {
    $pdo = getPDO();
    echo json_encode(['status' => 'success', 'message' => 'Conexión exitosa a la base de datos']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo conectar a la base de datos']);
}
?>