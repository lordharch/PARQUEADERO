<?php
require_once __DIR__ . '/../../db/db_connect.php';
$conn = getPDO();

$response = ["success" => false];

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
$placa          = $data['placa'] ?? null;
$id_usuario     = $data['id_usuario'] ?? null;
$id_tipo        = $data['id_tipo_vehiculo'] ?? null;
$marca          = $data['marca'] ?? null;
$modelo         = $data['modelo'] ?? null;
$color          = $data['color'] ?? null;
$autorizado     = $data['autorizado'] ?? 1;

// Validar que no falten datos obligatorios
if (!$placa || !$id_usuario || !$id_tipo || !$marca || !$modelo || !$color) {
    $response["error"] = "Faltan datos obligatorios";
    echo json_encode($response);
    exit;
}

// Validaciones adicionales
if (!is_numeric($id_usuario)) {
    $response["error"] = "ID de usuario debe ser numérico";
    echo json_encode($response);
    exit;
}

if (!is_numeric($id_tipo)) {
    $response["error"] = "ID de tipo de vehículo debe ser numérico";
    echo json_encode($response);
    exit;
}

// Validar formato de placa (puedes ajustar el patrón según tu país)
if (!preg_match('/^[A-Z0-9]{6,8}$/i', $placa)) {
    $response["error"] = "Formato de placa inválido";
    echo json_encode($response);
    exit;
}

try {
    // Verificar que el usuario existe y está activo
    $userCheck = $conn->prepare("SELECT nombre_completo FROM usuario WHERE id_usuario = ? AND activo = 1");
    $userCheck->execute([$id_usuario]);
    $usuario = $userCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        $response["error"] = "Usuario no encontrado o inactivo";
        echo json_encode($response);
        exit;
    }

    // Verificar que el tipo de vehículo existe
    $tipoCheck = $conn->prepare("SELECT nombre FROM tipovehiculo WHERE id_tipo_vehiculo = ?");
    $tipoCheck->execute([$id_tipo]);
    $tipoVehiculo = $tipoCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$tipoVehiculo) {
        $response["error"] = "Tipo de vehículo no encontrado";
        echo json_encode($response);
        exit;
    }

    // Verificar si ya existe un vehículo con esa placa
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM vehiculo WHERE placa = ?");
    $checkStmt->execute([strtoupper($placa)]);
    
    if ($checkStmt->fetchColumn() > 0) {
        $response["error"] = "Ya existe un vehículo con esa placa";
        echo json_encode($response);
        exit;
    }

    // Insertar vehículo
    $stmt = $conn->prepare("
        INSERT INTO vehiculo 
        (placa, id_usuario, id_tipo_vehiculo, marca, modelo, color, fecha_registro, autorizado)
        VALUES (:placa, :id_usuario, :id_tipo, :marca, :modelo, :color, NOW(), :autorizado)
    ");

    $stmt->execute([
        ':placa'      => strtoupper($placa), // Convertir a mayúsculas
        ':id_usuario' => (int)$id_usuario,
        ':id_tipo'    => (int)$id_tipo,
        ':marca'      => ucfirst(strtolower($marca)), // Primera letra mayúscula
        ':modelo'     => ucfirst(strtolower($modelo)),
        ':color'      => ucfirst(strtolower($color)),
        ':autorizado' => (int)$autorizado
    ]);

    $response["success"] = true;
    $response["message"] = "Vehículo creado exitosamente";
    $response["vehiculo"] = [
        "placa" => strtoupper($placa),
        "propietario" => $usuario['nombre_completo'],
        "tipo" => $tipoVehiculo['nombre'],
        "marca" => ucfirst(strtolower($marca)),
        "modelo" => ucfirst(strtolower($modelo))
    ];
    
} catch (PDOException $e) {
    // Verificar si es error de clave duplicada
    if ($e->getCode() == 23000) {
        $response["error"] = "Ya existe un vehículo con esa placa";
    } else {
        $response["error"] = "Error de base de datos: " . $e->getMessage();
    }
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?><?php
require_once __DIR__ . '/../../db/db_connect.php';
$conn = getPDO();

$response = ["success" => false];

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
$placa          = $data['placa'] ?? null;
$id_usuario     = $data['id_usuario'] ?? null;
$id_tipo        = $data['id_tipo_vehiculo'] ?? null;
$marca          = $data['marca'] ?? null;
$modelo         = $data['modelo'] ?? null;
$color          = $data['color'] ?? null;
$autorizado     = $data['autorizado'] ?? 1;

// Validar que no falten datos obligatorios
if (!$placa || !$id_usuario || !$id_tipo || !$marca || !$modelo || !$color) {
    $response["error"] = "Faltan datos obligatorios";
    echo json_encode($response);
    exit;
}

// Validaciones adicionales
if (!is_numeric($id_usuario)) {
    $response["error"] = "ID de usuario debe ser numérico";
    echo json_encode($response);
    exit;
}

if (!is_numeric($id_tipo)) {
    $response["error"] = "ID de tipo de vehículo debe ser numérico";
    echo json_encode($response);
    exit;
}

// Validar formato de placa (puedes ajustar el patrón según tu país)
if (!preg_match('/^[A-Z0-9]{6,8}$/i', $placa)) {
    $response["error"] = "Formato de placa inválido";
    echo json_encode($response);
    exit;
}

try {
    // Verificar que el usuario existe y está activo
    $userCheck = $conn->prepare("SELECT nombre_completo FROM usuario WHERE id_usuario = ? AND activo = 1");
    $userCheck->execute([$id_usuario]);
    $usuario = $userCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        $response["error"] = "Usuario no encontrado o inactivo";
        echo json_encode($response);
        exit;
    }

    // Verificar que el tipo de vehículo existe
    $tipoCheck = $conn->prepare("SELECT nombre FROM tipovehiculo WHERE id_tipo_vehiculo = ?");
    $tipoCheck->execute([$id_tipo]);
    $tipoVehiculo = $tipoCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$tipoVehiculo) {
        $response["error"] = "Tipo de vehículo no encontrado";
        echo json_encode($response);
        exit;
    }

    // Verificar si ya existe un vehículo con esa placa
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM vehiculo WHERE placa = ?");
    $checkStmt->execute([strtoupper($placa)]);
    
    if ($checkStmt->fetchColumn() > 0) {
        $response["error"] = "Ya existe un vehículo con esa placa";
        echo json_encode($response);
        exit;
    }

    // Insertar vehículo
    $stmt = $conn->prepare("
        INSERT INTO vehiculo 
        (placa, id_usuario, id_tipo_vehiculo, marca, modelo, color, fecha_registro, autorizado)
        VALUES (:placa, :id_usuario, :id_tipo, :marca, :modelo, :color, NOW(), :autorizado)
    ");

    $stmt->execute([
        ':placa'      => strtoupper($placa), // Convertir a mayúsculas
        ':id_usuario' => (int)$id_usuario,
        ':id_tipo'    => (int)$id_tipo,
        ':marca'      => ucfirst(strtolower($marca)), // Primera letra mayúscula
        ':modelo'     => ucfirst(strtolower($modelo)),
        ':color'      => ucfirst(strtolower($color)),
        ':autorizado' => (int)$autorizado
    ]);

    $response["success"] = true;
    $response["message"] = "Vehículo creado exitosamente";
    $response["vehiculo"] = [
        "placa" => strtoupper($placa),
        "propietario" => $usuario['nombre_completo'],
        "tipo" => $tipoVehiculo['nombre'],
        "marca" => ucfirst(strtolower($marca)),
        "modelo" => ucfirst(strtolower($modelo))
    ];
    
} catch (PDOException $e) {
    // Verificar si es error de clave duplicada
    if ($e->getCode() == 23000) {
        $response["error"] = "Ya existe un vehículo con esa placa";
    } else {
        $response["error"] = "Error de base de datos: " . $e->getMessage();
    }
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?>