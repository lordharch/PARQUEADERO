<?php
// admin/vehiculos.php
require '../db/db_connect.php';
$conn = getPDO(); // ✅ conexión activa

header('Content-Type: application/json');

try {
    // Consulta para traer vehículos con el nombre del tipo
    $sql = "
        SELECT 
            v.placa,
            v.id_usuario,
            u.nombre_completo AS propietario,
            v.marca,
            v.modelo,
            v.color,
            v.fecha_registro,
            v.autorizado,
            tv.nombre AS tipo_vehiculo,
            tv.tarifa_base
        FROM vehiculo v
        INNER JOIN usuario u ON v.id_usuario = u.id_usuario
        INNER JOIN tipovehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
        ORDER BY v.fecha_registro DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'total' => count($vehiculos),
        'vehiculos' => $vehiculos
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
