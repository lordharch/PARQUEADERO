<?php
require '../db/db_connect.php';

try {
    $stmt = $conn->prepare("
        SELECT 
            e.id_espacio, 
            e.codigo_espacio,
            z.nombre AS zona,
            tv.nombre AS tipo_vehiculo,
            e.estado
        FROM espacio e
        INNER JOIN zonaestacionamiento z ON e.id_zona = z.id_zona
        INNER JOIN tipovehiculo tv ON e.id_tipo_vehiculo = tv.id_tipo_vehiculo
    ");
    $stmt->execute();
    $espacios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "espacios" => $espacios]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
