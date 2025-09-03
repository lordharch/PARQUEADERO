<?php
require '../db/db_connect.php';
$conn = getPDO();  // ✅ conexión activa con PDOs

try {
    $stmt = $conn->prepare("SELECT u.id_usuario, u.identificacion, u.nombre_completo, u.correo, u.telefono, 
                                   t.nombre AS tipo_usuario, u.activo 
                            FROM usuario u
                            INNER JOIN tipousuario t ON u.id_tipo_usuario = t.id_tipo_usuario");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "usuarios" => $usuarios]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
