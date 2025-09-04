<?php
require_once '../../db/db_connect.php';

$response = ['success' => false];

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['error'] = "Método no permitido";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Obtener ID desde diferentes fuentes
$id = null;

// Desde JSON body (si es POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $id = $data['id'] ?? $data['id_usuario'] ?? null;
}

// Fallback: desde GET o POST
if (!$id) {
    $id = $_GET['id'] ?? $_GET['id_usuario'] ?? $_POST['id'] ?? $_POST['id_usuario'] ?? null;
}

if (!$id) {
    $response['error'] = "ID no proporcionado";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Validar que el ID sea numérico
if (!is_numeric($id)) {
    $response['error'] = "ID debe ser numérico";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    $conn = getPDO();
    
    $stmt = $conn->prepare("
        SELECT 
            u.id_usuario,
            u.identificacion,
            u.nombre_completo,
            u.correo,
            u.telefono,
            u.id_tipo_usuario,
            u.fecha_registro,
            u.activo,
            tu.nombre
        FROM usuario u
        LEFT JOIN tipousuario tu ON u.id_tipo_usuario = tu.id_tipo_usuario
        WHERE u.id_usuario = ?
    ");
    
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        // Convertir valores a tipos correctos
        $usuario['id_usuario'] = (int)$usuario['id_usuario'];
        $usuario['id_tipo_usuario'] = (int)$usuario['id_tipo_usuario'];
        $usuario['activo'] = (int)$usuario['activo'];
        
        // Formatear fecha
        if ($usuario['fecha_registro']) {
            $usuario['fecha_registro_formateada'] = date('d/m/Y H:i', strtotime($usuario['fecha_registro']));
        }
        
        // Estado textual
        $usuario['estado_texto'] = $usuario['activo'] ? 'Activo' : 'Inactivo';
        
        $response['success'] = true;
        $response['usuario'] = $usuario;
        $response['message'] = "Usuario encontrado exitosamente";
    } else {
        $response['error'] = "Usuario no encontrado";
    }
    
} catch (PDOException $e) {
    $response['error'] = "Error en la base de datos: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>