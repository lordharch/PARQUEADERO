<?php
require '../db/db_connect.php';

try {
    $stmt = $conn->prepare("
        INSERT INTO usuario (identificacion, nombre_completo, correo, telefono, id_tipo_usuario, fecha_registro, activo)
        VALUES (:identificacion, :nombre, :correo, :telefono, :tipo, NOW(), :activo)
    ");

    $stmt->execute([
        ':identificacion' => $_POST['identificacion'],
        ':nombre' => $_POST['nombre'],
        ':correo' => $_POST['correo'],
        ':telefono' => $_POST['telefono'],
        ':tipo' => $_POST['tipo'],
        ':activo' => $_POST['activo']
    ]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
