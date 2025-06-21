<?php
require '../db/db_connect.php';

$data = [];
$data['usuarios'] = $conn->query("SELECT COUNT(*) FROM usuario")->fetchColumn();
$data['vehiculos'] = $conn->query("SELECT COUNT(*) FROM vehiculo WHERE autorizado = 1")->fetchColumn();
$data['zonas'] = $conn->query("SELECT COUNT(*) FROM zonaestacionamiento")->fetchColumn();
$data['espacios_disponibles'] = $conn->query("SELECT COUNT(*) FROM espacio WHERE estado = 'disponible'")->fetchColumn();
$data['transacciones_hoy'] = $conn->query("SELECT SUM(total_pagado) FROM transaccion WHERE DATE(fecha_transaccion) = CURDATE()")->fetchColumn() ?: 0;
$data['camaras_activas'] = $conn->query("SELECT COUNT(*) FROM camara WHERE estado = 'activa'")->fetchColumn();

echo json_encode($data);
