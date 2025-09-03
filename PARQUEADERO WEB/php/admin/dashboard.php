<?php
require '../db/db_connect.php';
header('Content-Type: application/json');

try {
    $conn = getPDO(); // ✅ usamos siempre la función centralizada

    // 1. Total usuarios
    $totalUsuarios = $conn->query("SELECT COUNT(*) FROM usuario")->fetchColumn();

    // 2. Total vehículos
    $totalVehiculos = $conn->query("SELECT COUNT(*) FROM vehiculo")->fetchColumn();

    // 3. Espacios totales
    $espaciosTotales = $conn->query("SELECT COUNT(*) FROM espacio")->fetchColumn();

    // 4. Espacios ocupados (estado = 'ocupado')
    $espaciosOcupados = $conn->query("SELECT COUNT(*) FROM espacio WHERE estado = 'ocupado'")->fetchColumn();

    // 5. Accesos activos (vehículos que no han salido)
    $accesosActivos = $conn->query("
        SELECT COUNT(*) 
        FROM acceso 
        WHERE fecha_salida IS NULL OR estado = 'activo'
    ")->fetchColumn();

    // 6. Transacciones registradas
    $totalTransacciones = $conn->query("SELECT COUNT(*) FROM transaccion")->fetchColumn();

    echo json_encode([
        "success" => true,
        "dashboard" => [
            "usuarios"        => $totalUsuarios,
            "vehiculos"       => $totalVehiculos,
            "espacios" => [
                "totales"     => $espaciosTotales,
                "ocupados"    => $espaciosOcupados,
                "disponibles" => $espaciosTotales - $espaciosOcupados
            ],
            "accesos_activos" => $accesosActivos,
            "transacciones"   => $totalTransacciones
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

