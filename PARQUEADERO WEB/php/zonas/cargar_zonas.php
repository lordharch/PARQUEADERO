<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../db/db_connect.php';

$response = ['success' => false, 'zonas' => []];

try {
    $stmt = $conn->prepare("
        SELECT 
            z.id_zona, 
            z.nombre, 
            z.capacidad_maxima,
            COUNT(e.id_espacio) AS total_espacios,
            SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) AS ocupados,
            SUM(CASE WHEN e.estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles
        FROM zonaestacionamiento z
        LEFT JOIN espacio e ON z.id_zona = e.id_zona
        GROUP BY z.id_zona, z.nombre, z.capacidad_maxima
    ");
    
    $stmt->execute();
    $zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['zonas'] = $zonas;
    $response['success'] = true;

} catch (PDOException $e) {
    $response['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
