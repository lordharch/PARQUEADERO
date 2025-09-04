<?php
require_once __DIR__ . '/../../db/db_connect.php';
$conn = getPDO();

$response = ["success" => false];

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['error'] = "Método no permitido";
    echo json_encode($response);
    exit;
}

// Obtener el contenido JSON del body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Si no hay datos JSON, intentar con $_POST (para compatibilidad)
if (!$data) {
    $data = $_POST;
}

// Recoger variables con validación
$id_usuario     = $data['id_usuario'] ?? $data['id'] ?? null;
$identificacion = $data['identificacion'] ?? null;
$nombre         = $data['nombre'] ?? null;
$correo         = $data['correo'] ?? null;
$telefono       = $data['telefono'] ?? null;
$tipo           = $data['tipo'] ?? null;
$activo         = $data['activo'] ?? 1;

// Validar que no falten datos obligatorios
if (!$id_usuario) {
    $response["error"] = "ID de usuario no proporcionado";
    echo json_encode($response);
    exit;
}

if (!$identificacion || !$nombre || !$correo || !$telefono || !$tipo) {
    $response["error"] = "Faltan datos obligatorios";
    echo json_encode($response);
    exit;
}

// Validar que el ID sea numérico
if (!is_numeric($id_usuario)) {
    $response["error"] = "ID de usuario debe ser numérico";
    echo json_encode($response);
    exit;
}

// Validaciones adicionales
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $response["error"] = "El correo electrónico no es válido";
    echo json_encode($response);
    exit;
}

if (!is_numeric($tipo)) {
    $response["error"] = "El tipo de usuario debe ser numérico";
    echo json_encode($response);
    exit;
}

try {
    // Verificar que el usuario existe
    $checkStmt = $conn->prepare("SELECT nombre_completo FROM usuario WHERE id_usuario = ?");
    $checkStmt->execute([$id_usuario]);
    $usuarioExistente = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuarioExistente) {
        $response["error"] = "Usuario no encontrado";
        echo json_encode($response);
        exit;
    }

    // Verificar si ya existe otro usuario con esa identificación (diferente al actual)
    $checkIdentStmt = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE identificacion = ? AND id_usuario != ?");
    $checkIdentStmt->execute([$identificacion, $id_usuario]);
    
    if ($checkIdentStmt->fetchColumn() > 0) {
        $response["error"] = "Ya existe otro usuario con esa identificación";
        echo json_encode($response);
        exit;
    }

    // Actualizar usuario
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
        ':identificacion' => $identificacion,
        ':nombre'         => $nombre,
        ':correo'         => $correo,
        ':telefono'       => $telefono,
        ':tipo'           => (int)$tipo,
        ':activo'         => (int)$activo,
        ':id'             => (int)$id_usuario
    ]);

    if ($stmt->rowCount() > 0) {
        $response["success"] = true;
        $response["message"] = "Usuario actualizado exitosamente";
        $response["updated_user"] = $nombre;
    } else {
        $response["error"] = "No se realizaron cambios en el usuario";
    }
    
} catch (PDOException $e) {
    $response["error"] = "Error de base de datos: " . $e->getMessage();
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?>