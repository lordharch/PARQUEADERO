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
            t.nombre AS tipo_usuario,
            t.descripcion,
            t.tiene_permiso_reserva
        FROM usuario u
        INNER JOIN tipousuario t ON u.id_tipo_usuario = t.id_tipo_usuario
        ORDER BY u.fecha_registro DESC
    ");
    
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear los datos
    foreach ($usuarios as &$usuario) {
        $usuario['id_usuario'] = (int)$usuario['id_usuario'];
        $usuario['id_tipo_usuario'] = (int)$usuario['id_tipo_usuario'];
        $usuario['activo'] = (int)$usuario['activo'];
        $usuario['tiene_permiso_reserva'] = (int)$usuario['tiene_permiso_reserva'];
        
        // Formatear fecha
        if ($usuario['fecha_registro']) {
            $usuario['fecha_registro_formateada'] = date('d/m/Y H:i', strtotime($usuario['fecha_registro']));
        }
        
        // Estado textual
        $usuario['estado_texto'] = $usuario['activo'] ? 'Activo' : 'Inactivo';
        $usuario['puede_reservar'] = $usuario['tiene_permiso_reserva'] ? 'Sí' : 'No';
    }
    
    $response['success'] = true;
    $response['usuarios'] = $usuarios;
    $response['total'] = count($usuarios);
    $response['message'] = "Usuarios obtenidos exitosamente";
    
} catch (PDOException $e) {
    $response['error'] = "Error en la base de datos: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>