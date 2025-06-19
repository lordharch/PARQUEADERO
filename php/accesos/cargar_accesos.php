<?php
require '../db/db_connect.php';

try {
    $stmt = $conn->prepare("
        SELECT 
            a.id_acceso,
            a.placa_vehiculo,
            e.codigo_espacio,
            a.fecha_entrada,
            a.fecha_salida,
            a.estado
        FROM acceso a
        INNER JOIN espacio e ON a.id_espacio = e.id_espacio
        ORDER BY a.fecha_entrada DESC
    ");
    $stmt->execute();
    $accesos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "accesos" => $accesos]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
