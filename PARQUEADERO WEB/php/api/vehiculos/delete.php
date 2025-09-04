<?php
require_once __DIR__ . '/../../db/db_connect.php';
$conn = getPDO();

$response = ["success" => false];

// Verificar que sea método DELETE o POST
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['error'] = "Método no permitido";
    echo json_encode($response);
    exit;
}

// Obtener placa desde diferentes fuentes
$placa = null;

// Prioridad: JSON body -> GET -> POST
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $placa = $data['placa'] ?? null;
}

if (!$placa) {
    $placa = $_GET['placa'] ?? $_POST['placa'] ?? null;
}

if (!$placa) {
    $response['error'] = "Placa no proporcionada";
    echo json_encode($response);
    exit;
}

// Normalizar placa
$placa = strtoupper(trim($placa));

// Validar formato de placa
if (!preg_match('/^[A-Z0-9]{6,8}$/i', $placa)) {
    $response['error'] = "Formato de placa inválido";
    echo json_encode($response);
    exit;
}

try {
    // Primero verificar si el vehículo existe y obtener sus datos
    $checkStmt = $conn->prepare("
        SELECT v.placa, v.marca, v.modelo, u.nombre_completo, tv.nombre as tipo_vehiculo
        FROM vehiculo v
        INNER JOIN usuario u ON v.id_usuario = u.id_usuario
        INNER JOIN tipovehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
        WHERE v.placa = ?
    ");
    $checkStmt->execute([$placa]);
    $vehiculo = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vehiculo) {
        $response['error'] = "Vehículo no encontrado";
        echo json_encode($response);
        exit;
    }
    
    // Verificar si el vehículo tiene accesos activos
    $accesoActivoStmt = $conn->prepare("
        SELECT COUNT(*) as accesos_activos 
        FROM acceso 
        WHERE placa_vehiculo = ? AND estado = 'activo'
    ");
    $accesoActivoStmt->execute([$placa]);
    $accesoActivo = $accesoActivoStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($accesoActivo['accesos_activos'] > 0) {
        $response['error'] = "No se puede eliminar: el vehículo tiene accesos activos en el parqueadero";
        echo json_encode($response);
        exit;
    }
    
    // Verificar si tiene transacciones (opcional: puedes permitir eliminar o no)
    $transaccionesStmt = $conn->prepare("
        SELECT COUNT(*) as total_transacciones
        FROM transaccion t
        INNER JOIN acceso a ON t.id_acceso = a.id_acceso
        WHERE a.placa_vehiculo = ?
    ");
    $transaccionesStmt->execute([$placa]);
    $transacciones = $transaccionesStmt->fetch(PDO::FETCH_ASSOC);
    
    // Proceder con la eliminación
    $stmt = $conn->prepare("DELETE FROM vehiculo WHERE placa = ?");
    $stmt->execute([$placa]);

    if ($stmt->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = "Vehículo eliminado exitosamente";
        $response['vehiculo_eliminado'] = [
            "placa" => $vehiculo['placa'],
            "propietario" => $vehiculo['nombre_completo'],
            "vehiculo" => $vehiculo['marca'] . " " . $vehiculo['modelo'],
            "tipo" => $vehiculo['tipo_vehiculo'],
            "tenia_transacciones" => $transacciones['total_transacciones'] > 0
        ];
    } else {
        $response['error'] = "No se pudo eliminar el vehículo";
    }
    
} catch (PDOException $e) {
    // Verificar si es error de restricción de clave foránea
    if ($e->getCode() == 23000) {
        $response['error'] = "No se puede eliminar: el vehículo tiene registros asociados en el sistema";
    } else {
        $response['error'] = "Error de base de datos: " . $e->getMessage();
    }
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');
echo json_encode($response);
?>