<?php
require '../db/db_connect.php';

try {
    $stmt = $conn->prepare("
        SELECT 
            t.id_transaccion,
            t.id_acceso,
            t.fecha_transaccion,
            t.tiempo_estadia,
            t.subtotal,
            t.descuentos,
            t.total_pagado,
            t.numero_comprobante
        FROM transaccion t
        ORDER BY t.fecha_transaccion DESC
    ");
    $stmt->execute();
    $transacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "transacciones" => $transacciones]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
