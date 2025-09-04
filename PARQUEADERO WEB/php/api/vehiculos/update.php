<?php
require_once __DIR__ . '/../../db/db_connect.php';
$conn = getPDO();

$response = ["success" => false];

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['error'] = "Método no permitido. Use POST";
    echo json_encode($response);
    exit;
}

// Obtener datos desde JSON o POST
$json = file_get_contents('php://input');
$data = json_decode($json, true);
if (!$data) {
    $data = $_POST;
}

// Recoger variables
$placa = $data['placa'] ?? null;
$marca = $data['marca'] ?? null;
$modelo = $data['modelo'] ?? null;
$color = $data['color'] ?? null;
$autorizado = $data['autorizado'] ?? null;
$id_tipo_vehiculo = $data['id_tipo_vehiculo'] ?? null;

// Validaciones
if (!$placa) {
    $response["error"] = "Placa es obligatoria";
    echo json_encode($response);
    exit;
}

// Validar formato de placa
if (!preg_match('/^[A-Z0-9]{6,8}$/i', $placa)) {
    $response["error"] = "Formato de placa inválido";
    echo json_encode($response);
    exit;
}

// Verificar que hay al menos un campo para actualizar
$camposActualizables = ['marca', 'modelo', 'color', 'autorizado', 'id_tipo_vehiculo'];
$hayCamposParaActualizar = false;

foreach ($camposActualizables as $campo) {
    if (isset($data[$campo])) {
        $hayCamposParaActualizar = true;
        break;
    }
}

if (!$hayCamposParaActualizar) {
    $response["error"] = "No se proporcionaron campos para actualizar";
    echo json_encode($response);
    exit;
}

try {
    // Primero verificar que el vehículo existe
    $checkStmt = $conn->prepare("
        SELECT placa, marca, modelo, color, autorizado, id_tipo_vehiculo 
        FROM vehiculo 
        WHERE placa = ?
    ");
    $checkStmt->execute([strtoupper($placa)]);
    $vehiculoActual = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vehiculoActual) {
        $response["error"] = "Vehículo no encontrado";
        echo json_encode($response);
        exit;
    }

    // Si se quiere cambiar el tipo de vehículo, verificar que existe
    if ($id_tipo_vehiculo) {
        $tipoCheck = $conn->prepare("SELECT nombre FROM tipovehiculo WHERE id_tipo_vehiculo = ?");
        $tipoCheck->execute([$id_tipo_vehiculo]);
        $tipoVehiculo = $tipoCheck->fetch(PDO::FETCH_ASSOC);
        
        if (!$tipoVehiculo) {
            $response["error"] = "Tipo de vehículo no encontrado";
            echo json_encode($response);
            exit;
        }
    }

    // Construir query dinámico
    $updateFields = [];
    $params = [];
    
    if ($marca !== null) {
        $updateFields[] = "marca = ?";
        $params[] = ucfirst(strtolower($marca));
    }
    
    if ($modelo !== null) {
        $updateFields[] = "modelo = ?";
        $params[] = ucfirst(strtolower($modelo));
    }
    
    if ($color !== null) {
        $updateFields[] = "color = ?";
        $params[] = ucfirst(strtolower($color));
    }
    
    if ($autorizado !== null) {
        $updateFields[] = "autorizado = ?";
        $params[] = (int)$autorizado;
    }
    
    if ($id_tipo_vehiculo !== null) {
        $updateFields[] = "id_tipo_vehiculo = ?";
        $params[] = (int)$id_tipo_vehiculo;
    }
    
    $params[] = strtoupper($placa); // Para el WHERE
    
    $sql = "UPDATE vehiculo SET " . implode(", ", $updateFields) . " WHERE placa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    if ($stmt->rowCount() > 0) {
        // Obtener datos actualizados
        $updatedStmt = $conn->prepare("
            SELECT v.*, tv.nombre as tipo_vehiculo, u.nombre_completo as propietario
            FROM vehiculo v
            INNER JOIN tipovehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
            INNER JOIN usuario u ON v.id_usuario = u.id_usuario
            WHERE v.placa = ?
        ");
        $updatedStmt->execute([strtoupper($placa)]);
        $vehiculoActualizado = $updatedStmt->fetch(PDO::FETCH_ASSOC);
        
        $response["success"] = true;
        $response["message"] = "Vehículo actualizado exitosamente";
        $response["vehiculo"] = $vehiculoActualizado;
        $response["campos_actualizados"] = array_keys(array_filter([
            'marca' => $marca !== null,
            'modelo' => $modelo !== null,
            'color' => $color !== null,
            'autorizado' => $autorizado !== null,
            'id_tipo_vehiculo' => $id_tipo_vehiculo !== null
        ]));
        
    } else {
        $response["error"] = "No se realizaron cambios o el vehículo no existe";
    }
    
} catch (PDOException $e) {
    $response["error"] = "Error de base de datos: " . $e->getMessage();
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?>