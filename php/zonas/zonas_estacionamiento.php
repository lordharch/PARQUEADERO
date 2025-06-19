<?php
require '../db/db_connect.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("
        SELECT 
            z.nombre AS nombre_zona,
            z.capacidad_maxima,
            SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles,
            SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) AS ocupados,
            SUM(CASE WHEN e.estado = 'reservado' THEN 1 ELSE 0 END) AS reservados
        FROM zonaestacionamiento z
        LEFT JOIN espacio e ON z.id_zona = e.id_zona
        GROUP BY z.id_zona, z.nombre, z.capacidad_maxima
        ORDER BY z.nombre
    ");
    $stmt->execute();
    $zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "zonas" => $zonas]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Error en base de datos: " . $e->getMessage()]);
}