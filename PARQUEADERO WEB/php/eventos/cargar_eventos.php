<?php
require '../db/db_connect.php';

try {
    $stmt = $conn->prepare("
        SELECT 
            e.id_evento,
            te.nombre AS tipo,
            e.fecha_evento,
            e.descripcion,
            e.estado_resolucion,
            te.nivel_prioridad
        FROM eventoseguridad e
        INNER JOIN tipoeventoseguridad te ON e.id_tipo_evento = te.id_tipo_evento
        ORDER BY e.fecha_evento DESC
    ");
    $stmt->execute();
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "eventos" => $eventos]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
