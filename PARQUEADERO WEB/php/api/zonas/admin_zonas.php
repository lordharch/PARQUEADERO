<?php
require_once __DIR__ . '/../../db/db_connect.php';
$conn = getPDO();

$response = ["success" => false];

// Verificar método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Obtener datos según el método
if ($method === 'GET') {
    $data = $_GET;
} else {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if (!$data) {
        $data = $_POST;
    }
}

try {
    switch ($method) {
        case 'GET':
            // READ - Obtener zonas
            $id_zona = $data['id'] ?? $data['id_zona'] ?? null;
            $incluir_espacios = $data['incluir_espacios'] ?? false;
            $limit = $data['limit'] ?? 50;
            $offset = $data['offset'] ?? 0;

            $limit = min(max((int)$limit, 1), 100);
            $offset = max((int)$offset, 0);

            if ($id_zona) {
                // Obtener zona específica
                $stmt = $conn->prepare("
                    SELECT 
                        z.*,
                        COUNT(e.id_espacio) as total_espacios,
                        COUNT(CASE WHEN e.estado = 'disponible' THEN 1 END) as espacios_disponibles,
                        COUNT(CASE WHEN e.estado = 'ocupado' THEN 1 END) as espacios_ocupados,
                        COUNT(CASE WHEN e.estado = 'mantenimiento' THEN 1 END) as espacios_mantenimiento
                    FROM zonaestacionamiento z
                    LEFT JOIN espacio e ON z.id_zona = e.id_zona
                    WHERE z.id_zona = ?
                    GROUP BY z.id_zona
                ");
                $stmt->execute([$id_zona]);
                $zona = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($zona) {
                    if ($incluir_espacios) {
                        $espaciosStmt = $conn->prepare("
                            SELECT e.*, tv.nombre as tipo_vehiculo
                            FROM espacio e
                            INNER JOIN tipovehiculo tv ON e.id_tipo_vehiculo = tv.id_tipo_vehiculo
                            WHERE e.id_zona = ?
                            ORDER BY e.codigo_espacio
                        ");
                        $espaciosStmt->execute([$id_zona]);
                        $zona['espacios'] = $espaciosStmt->fetchAll(PDO::FETCH_ASSOC);
                    }

                    $response['success'] = true;
                    $response['zona'] = $zona;
                } else {
                    $response['error'] = "Zona no encontrada";
                }
            } else {
                // Obtener todas las zonas
                $stmt = $conn->query("
                    SELECT 
                        z.*,
                        COUNT(e.id_espacio) as total_espacios,
                        COUNT(CASE WHEN e.estado = 'disponible' THEN 1 END) as espacios_disponibles,
                        COUNT(CASE WHEN e.estado = 'ocupado' THEN 1 END) as espacios_ocupados
                    FROM zonaestacionamiento z
                    LEFT JOIN espacio e ON z.id_zona = e.id_zona
                    GROUP BY z.id_zona
                    ORDER BY z.id_zona
                    LIMIT $limit OFFSET $offset
                ");
                $zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $totalStmt = $conn->query("SELECT COUNT(*) as total FROM zonaestacionamiento");
                $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

                $response['success'] = true;
                $response['zonas'] = $zonas;
                $response['total'] = (int)$total;
                $response['paginacion'] = [
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $limit) < $total
                ];
            }
            break;

        case 'POST':
            // CREATE - Crear nueva zona
            $nombre = $data['nombre'] ?? null;
            $capacidad_maxima = $data['capacidad_maxima'] ?? null;

            if (!$nombre || !$capacidad_maxima) {
                $response['error'] = "Nombre y capacidad máxima son obligatorios";
                break;
            }

            if (!is_numeric($capacidad_maxima) || $capacidad_maxima <= 0) {
                $response['error'] = "Capacidad máxima debe ser un número positivo";
                break;
            }

            $stmt = $conn->prepare("
                INSERT INTO zonaestacionamiento (nombre, capacidad_maxima)
                VALUES (?, ?)
            ");
            $stmt->execute([$nombre, $capacidad_maxima]);

            $id_zona = $conn->lastInsertId();

            $response['success'] = true;
            $response['message'] = "Zona creada exitosamente";
            $response['zona'] = [
                'id_zona' => $id_zona,
                'nombre' => $nombre,
                'capacidad_maxima' => (int)$capacidad_maxima
            ];
            break;

        case 'PUT':
        case 'PATCH':
            // UPDATE - Actualizar zona
            $id_zona = $data['id_zona'] ?? null;
            $nombre = $data['nombre'] ?? null;
            $capacidad_maxima = $data['capacidad_maxima'] ?? null;

            if (!$id_zona) {
                $response['error'] = "ID de zona es obligatorio";
                break;
            }

            // Verificar que la zona existe
            $checkStmt = $conn->prepare("SELECT * FROM zonaestacionamiento WHERE id_zona = ?");
            $checkStmt->execute([$id_zona]);
            if (!$checkStmt->fetch()) {
                $response['error'] = "Zona no encontrada";
                break;
            }

            $updateFields = [];
            $params = [];

            if ($nombre !== null) {
                $updateFields[] = "nombre = ?";
                $params[] = $nombre;
            }

            if ($capacidad_maxima !== null) {
                if (!is_numeric($capacidad_maxima) || $capacidad_maxima <= 0) {
                    $response['error'] = "Capacidad máxima debe ser un número positivo";
                    break;
                }
                $updateFields[] = "capacidad_maxima = ?";
                $params[] = $capacidad_maxima;
            }

            if (empty($updateFields)) {
                $response['error'] = "No se proporcionaron campos para actualizar";
                break;
            }

            $params[] = $id_zona;
            $sql = "UPDATE zonaestacionamiento SET " . implode(", ", $updateFields) . " WHERE id_zona = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $response['success'] = true;
            $response['message'] = "Zona actualizada exitosamente";
            $response['campos_actualizados'] = $updateFields;
            break;

        case 'DELETE':
            // DELETE - Eliminar zona
            $id_zona = $data['id_zona'] ?? null;

            if (!$id_zona) {
                $response['error'] = "ID de zona es obligatorio";
                break;
            }

            // Verificar que la zona existe
            $checkStmt = $conn->prepare("SELECT * FROM zonaestacionamiento WHERE id_zona = ?");
            $checkStmt->execute([$id_zona]);
            $zona = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$zona) {
                $response['error'] = "Zona no encontrada";
                break;
            }

            // Verificar que no tiene espacios asociados
            $espaciosStmt = $conn->prepare("SELECT COUNT(*) as total FROM espacio WHERE id_zona = ?");
            $espaciosStmt->execute([$id_zona]);
            $totalEspacios = $espaciosStmt->fetch(PDO::FETCH_ASSOC)['total'];

            if ($totalEspacios > 0) {
                $response['error'] = "No se puede eliminar: la zona tiene espacios asociados";
                break;
            }

            $stmt = $conn->prepare("DELETE FROM zonaestacionamiento WHERE id_zona = ?");
            $stmt->execute([$id_zona]);

            $response['success'] = true;
            $response['message'] = "Zona eliminada exitosamente";
            $response['zona_eliminada'] = $zona;
            break;

        default:
            $response['error'] = "Método no permitido";
            break;
    }

} catch (PDOException $e) {
    $response['error'] = "Error de base de datos: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>