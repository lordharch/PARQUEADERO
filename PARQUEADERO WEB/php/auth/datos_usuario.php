<?php
header("Content-Type: application/json");
require '../db/db_connect.php';

$conn = getPDO();

$id = $_GET['id'] ?? null;

try {
    if ($id) {
        $stmt = $conn->prepare("
            SELECT u.*, t.nombre as tipo 
            FROM usuario u 
            INNER JOIN tipousuario t 
            ON u.id_tipo_usuario = t.id_tipo_usuario
            WHERE u.id_usuario = :id
        ");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }
    } else {
        $stmt = $conn->prepare("
            SELECT u.*, t.nombre as tipo 
            FROM usuario u 
            INNER JOIN tipousuario t 
            ON u.id_tipo_usuario = t.id_tipo_usuario
        ");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en el servidor', 'detail' => $e->getMessage()]);
}
