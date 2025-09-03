<?php
header('Content-Type: application/json');
require '../db/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$nombre = htmlspecialchars(trim($data['nombre'] ?? ''));
$direccion_ip = htmlspecialchars(trim($data['direccion_ip'] ?? ''));
$id_zona = intval($data['id_zona'] ?? 0);
$estado = htmlspecialchars(trim($data['estado'] ?? ''));

if(!$nombre || !$direccion_ip || !$id_zona || !$estado){
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("INSERT INTO camara (nombre, direccion_ip, id_zona, estado, ultima_verificacion) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$nombre, $direccion_ip, $id_zona, $estado]);
    echo json_encode(["status" => "success", "id_camara" => $pdo->lastInsertId()]);
} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
