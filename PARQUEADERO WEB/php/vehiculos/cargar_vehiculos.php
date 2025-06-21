<?php
require '../db/db_connect.php';

try {
    $stmt = $conn->prepare("
        SELECT v.placa, v.marca, v.modelo, v.color, v.autorizado,
               u.nombre_completo AS propietario,
               tv.nombre AS tipo_vehiculo
        FROM vehiculo v
        INNER JOIN usuario u ON v.id_usuario = u.id_usuario
        INNER JOIN tipovehiculo tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo
    ");
    $stmt->execute();
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "vehiculos" => $vehiculos]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
