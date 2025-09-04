<?php
require_once '../../db/db_connect.php';

$response = ['success' => false];

// Verificar que sea método DELETE o POST
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['error'] = "Método no permitido";
    echo json_encode($response);
    exit;
}

// Obtener ID desde diferentes fuentes
$id = null;

// Prioridad: JSON body -> GET -> POST
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $id = $data['id'] ?? null;
}

if (!$id) {
    $id = $_GET['id'] ?? $_POST['id'] ?? null;
}

if (!$id) {
    $response['error'] = "ID no proporcionado";
    echo json_encode($response);
    exit;
}

// Validar que el ID sea numérico
if (!is_numeric($id)) {
    $response['error'] = "ID debe ser numérico";
    echo json_encode($response);
    exit;
}

try {
    $conn = getPDO();
    
    // Primero verificar si el usuario existe
    $checkStmt = $conn->prepare("SELECT nombre_completo FROM usuario WHERE id_usuario = ?");
    $checkStmt->execute([$id]);
    $usuario = $checkStmt->fetch();
    
    if (!$usuario) {
        $response['error'] = "Usuario no encontrado";
        echo json_encode($response);
        exit;
    }
    
    // Proceder con la eliminación
    $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = "Usuario eliminado exitosamente";
        $response['deleted_user'] = $usuario['nombre_completo'];
    } else {
        $response['error'] = "No se pudo eliminar el usuario";
    }
    
} catch (PDOException $e) {
    $response['error'] = "Error de base de datos: " . $e->getMessage();
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?>