<?php
require_once __DIR__ . '/../../db/db_connect.php';

$conn = getPDO(); // ✅ aquí ya creas la conexión con la función

$stmt = $conn->query("
    SELECT u.*, t.nombre as tipo 
    FROM usuario u 
    INNER JOIN tipousuario t ON u.id_tipo_usuario = t.id_tipo_usuario
");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
