<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if(!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos inválidos"]);
    exit;
}

// Tipos de usuario permitidos
$tiposPermitidos = [1, 2, 3]; // Regular, Premium, Empleado
$idTipo = intval($data['usuario']['id_tipo_usuario'] ?? 0);
if (!in_array($idTipo, $tiposPermitidos)) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Tipo de usuario no permitido."]);
    exit;
}

// Sanitizar y validar datos de usuario
$usuario = $data['usuario'];
$vehiculo = $data['vehiculo'];

$identificacion = htmlspecialchars(trim($usuario['identificacion'] ?? ''));
$nombre = htmlspecialchars(trim($usuario['nombre_completo'] ?? ''));
$correo = filter_var($usuario['correo'] ?? '', FILTER_VALIDATE_EMAIL);
$telefono = htmlspecialchars(trim($usuario['telefono'] ?? ''));
$password = $usuario['password'] ?? '';

if(!$identificacion || !$nombre || !$correo || !$telefono || !$password) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos de usuario incompletos"]);
    exit;
}

// Sanitizar y validar datos de vehículo
$placa = htmlspecialchars(trim($vehiculo['placa'] ?? ''));
$id_tipo_vehiculo = intval($vehiculo['id_tipo_vehiculo'] ?? 0);
$marca = htmlspecialchars(trim($vehiculo['marca'] ?? ''));
$modelo = htmlspecialchars(trim($vehiculo['modelo'] ?? ''));
$color = htmlspecialchars(trim($vehiculo['color'] ?? ''));
$autorizado = !empty($vehiculo['autorizado']) ? 1 : 0;

if(!$placa || !$id_tipo_vehiculo || !$marca || !$modelo || !$color) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos de vehículo incompletos"]);
    exit;
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction(); // iniciar transacción

    // Verificar si el correo o identificación ya existen
    $stmt = $pdo->prepare("SELECT 1 FROM usuario WHERE correo = ? OR identificacion = ?");
    $stmt->execute([$correo, $identificacion]);
    if ($stmt->fetch()) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Correo o identificación ya registrados"]);
        exit;
    }

    // Registrar usuario
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuario (
        identificacion, nombre_completo, correo, telefono, id_tipo_usuario, password, fecha_registro, activo
    ) VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)");
    $stmt->execute([$identificacion, $nombre, $correo, $telefono, $idTipo, $passwordHash]);
    $user_id = $pdo->lastInsertId();

    // Registrar vehículo
    $stmt = $pdo->prepare("INSERT INTO vehiculo (
        placa, id_usuario, id_tipo_vehiculo, marca, modelo, color, fecha_registro, autorizado
    ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->execute([$placa, $user_id, $id_tipo_vehiculo, $marca, $modelo, $color, $autorizado]);

    $pdo->commit(); // confirmar transacción

    // Respuesta completa
    echo json_encode([
        "status" => "success",
        "usuario" => [
            "id_usuario" => $user_id,
            "identificacion" => $identificacion,
            "nombre_completo" => $nombre,
            "correo" => $correo,
            "telefono" => $telefono,
            "id_tipo_usuario" => $idTipo,
            "activo" => 1
        ],
        "vehiculo" => [
            "placa" => $placa,
            "id_usuario" => $user_id,
            "id_tipo_vehiculo" => $id_tipo_vehiculo,
            "marca" => $marca,
            "modelo" => $modelo,
            "color" => $color,
            "autorizado" => $autorizado
        ]
    ]);

} catch(PDOException $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error en el servidor"]);
}
?>
