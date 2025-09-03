<?php
require '../db/db_connect.php';
$conn = getPDO();  // ✅ conexión activa con PDO
$response = ['success' => false];

if (!isset($_GET['id'])) {
    $response['error'] = "ID no especificado";
    echo json_encode($response);
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT * FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $response['success'] = true;
        $response['usuario'] = $usuario;
    } else {
        $response['error'] = "Usuario no encontrado";
    }
} catch (PDOException $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
