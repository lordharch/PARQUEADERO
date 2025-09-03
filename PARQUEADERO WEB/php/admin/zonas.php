<?php
// php/admin/zonas.php
require '../db/db_connect.php';
$conn = getPDO();
header('Content-Type: application/json');

try {
    // Consulta para traer todas las zonas y contar ocupados/disponibles
    $sql = "
        SELECT 
            z.id_zona,
            z.nombre,
            z.capacidad_maxima,
            COUNT(e.id_espacio) AS total_espacios,
            SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) AS ocupados,
            SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles
        FROM zonaestacionamiento z
        LEFT JOIN espacio e ON e.id_zona = z.id_zona
        GROUP BY z.id_zona, z.nombre, z.capacidad_maxima
        ORDER BY z.id_zona ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "total" => count($zonas),
        "zonas" => $zonas
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
