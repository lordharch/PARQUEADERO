<?php
require '../db/db_connect.php';

try {
    $stmt = $conn->prepare("
        SELECT 
            c.id_camara,
            c.nombre,
            c.direccion_ip,
            c.ultima_verificacion,
            c.estado,
            z.nombre AS zona
        FROM camara c
        INNER JOIN zonaestacionamiento z ON c.id_zona = z.id_zona
    ");
    $stmt->execute();
    $camaras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "camaras" => $camaras]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
