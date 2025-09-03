<?php
header('Content-Type: application/json');
require '../db/db_connect.php';

$id_camara = intval($_GET['id'] ?? 0);

if(!$id_camara){
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "ID inválido"]);
    exit;
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM camara WHERE id_camara=?");
    $stmt->execute([$id_camara]);
    echo json_encode(["status" => "success", "message" => "Cámara eliminada"]);
} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
