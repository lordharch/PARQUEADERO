<?php
require '../db/db_connect.php';

$response = ['success' => false];

$id = $_GET['id'] ?? null;

if (!$id) {
    $response['error'] = "ID no proporcionado";
    echo json_encode($response);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $response['success'] = true;
    } else {
        $response['error'] = "No se eliminó ningún usuario";
    }
} catch (PDOException $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
