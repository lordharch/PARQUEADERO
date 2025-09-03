<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db/db_connect.php';

try {
    $pdo = getPDO();
    
    // Obtener todas las cámaras con el nombre de la zona
    $stmt = $pdo->query("
        SELECT c.id_camara, c.nombre AS nombre_camara, c.direccion_ip, c.estado, 
               z.nombre AS zona
        FROM camara c
        INNER JOIN zonaestacionamiento z ON c.id_zona = z.id_zona
    ");

    $camaras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "total" => count($camaras),
        "camaras" => $camaras
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al cargar las cámaras",
        "error" => $e->getMessage()
    ]);
}
?>
