<?php
// php/accesos/cargar_accesos.php (o php/api/accesos/read.php)
require '../db/db_connect.php';
@require_once '../db/auth_check.php'; // opcional: valida sesión
@require_once '../db/role_check.php'; // opcional: valida rol
$conn = getPDO(); // ✅ aquí ya creas la conexión con la función
header('Content-Type: application/json');

try {
    $pdo = getPDO();

    // Admite JSON (fetch) o GET
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) $payload = [];
    $q = array_merge($_GET, $payload);

    // -------- Filtros permitidos --------
    $placa        = isset($q['placa']) ? trim($q['placa']) : null;
    $estado       = isset($q['estado']) ? trim($q['estado']) : null; // 'activo','finalizado','anomalia'
    $fechaDesde   = isset($q['fecha_desde']) ? trim($q['fecha_desde']) : null; // '2025-09-01'
    $fechaHasta   = isset($q['fecha_hasta']) ? trim($q['fecha_hasta']) : null; // '2025-09-02'
    $idZona       = isset($q['id_zona']) ? (int)$q['id_zona'] : null;
    $idEspacio    = isset($q['id_espacio']) ? (int)$q['id_espacio'] : null;
    $soloAbiertos = isset($q['solo_abiertos']) ? (int)!!$q['solo_abiertos'] : null; // 1 = salida NULL o estado 'activo'

    // -------- Paginación y orden --------
    $page     = max(1, (int)($q['page'] ?? 1));
    $pageSize = min(100, max(1, (int)($q['page_size'] ?? 50)));
    $offset   = ($page - 1) * $pageSize;

    // Campos de orden seguros
    $sortBy   = $q['sort_by']  ?? 'fecha_entrada';
    $sortDir  = strtolower($q['sort_dir'] ?? 'desc');
    $allowedSortBy  = ['fecha_entrada','fecha_salida','estado','placa_vehiculo','codigo_espacio'];
    $allowedSortDir = ['asc','desc'];
    if (!in_array($sortBy, $allowedSortBy, true))  $sortBy = 'fecha_entrada';
    if (!in_array($sortDir, $allowedSortDir, true)) $sortDir = 'desc';

    // -------- Rol / dueño (si usas sesiones) --------
    // Ajusta esto según como guardes la sesión:
    // $_SESSION['user_id'] y $_SESSION['role'] = 'admin'|'usuario'
    session_start();
    $userId = $_SESSION['user_id'] ?? null;
    $role   = $_SESSION['role']    ?? 'usuario'; // por defecto usuario
    $isAdmin = ($role === 'admin');

    // -------- SQL base --------
    $sqlBase = "
        FROM acceso a
        INNER JOIN espacio e ON a.id_espacio = e.id_espacio
        INNER JOIN zonaestacionamiento z ON e.id_zona = z.id_zona
        INNER JOIN vehiculo v ON a.placa_vehiculo = v.placa
        /* LEFT JOIN camara c1 ON a.id_camara_entrada = c1.id_camara
           LEFT JOIN camara c2 ON a.id_camara_salida = c2.id_camara */
        WHERE 1=1
    ";

    $params = [];
    // Filtros dinámicos
    if ($placa) {
        $sqlBase .= " AND a.placa_vehiculo = :placa ";
        $params[':placa'] = $placa;
    }
    if ($estado) {
        $sqlBase .= " AND a.estado = :estado ";
        $params[':estado'] = $estado;
    }
    if ($soloAbiertos === 1) {
        $sqlBase .= " AND (a.fecha_salida IS NULL OR a.estado = 'activo') ";
    }
    if ($fechaDesde) {
        $sqlBase .= " AND a.fecha_entrada >= :fdesde ";
        $params[':fdesde'] = $fechaDesde . ' 00:00:00';
    }
    if ($fechaHasta) {
        $sqlBase .= " AND a.fecha_entrada <= :fhasta ";
        $params[':fhasta'] = $fechaHasta . ' 23:59:59';
    }
    if ($idZona) {
        $sqlBase .= " AND z.id_zona = :id_zona ";
        $params[':id_zona'] = $idZona;
    }
    if ($idEspacio) {
        $sqlBase .= " AND e.id_espacio = :id_espacio ";
        $params[':id_espacio'] = $idEspacio;
    }

    // Visión por rol: si NO es admin, limitar a vehículos del usuario
   // if (!$isAdmin) {
     //   if (!$userId) {
       //     http_response_code(401);
         //   echo json_encode(['success' => false, 'error' => 'No autenticado']);
           // exit;
       // }
        //$sqlBase .= " AND v.id_usuario = :id_usuario_dueno ";
       // $params[':id_usuario_dueno'] = (int)$userId;
    //}

    // -------- Conteo total --------
    $sqlCount = "SELECT COUNT(*) " . $sqlBase;
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($params);
    $total = (int)$stmtCount->fetchColumn();

    // -------- Consulta paginada --------
    $sqlSelect = "
        SELECT
            a.id_acceso,
            a.placa_vehiculo,
            e.codigo_espacio,
            z.nombre AS zona,
            a.fecha_entrada,
            a.fecha_salida,
            a.estado,
            -- Duración en minutos (si no ha salido, hasta ahora)
            TIMESTAMPDIFF(
                MINUTE,
                a.fecha_entrada,
                COALESCE(a.fecha_salida, NOW())
            ) AS minutos_estadia
            /* , c1.nombre AS camara_entrada, c2.nombre AS camara_salida */
        " . $sqlBase . "
        ORDER BY $sortBy $sortDir
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $pdo->prepare($sqlSelect);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit',  $pageSize, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
    $stmt->execute();

    $accesos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'meta' => [
            'total'      => $total,
            'page'       => $page,
            'page_size'  => $pageSize,
            'sort_by'    => $sortBy,
            'sort_dir'   => $sortDir,
        ],
        'filters' => [
            'placa'         => $placa,
            'estado'        => $estado,
            'fecha_desde'   => $fechaDesde,
            'fecha_hasta'   => $fechaHasta,
            'id_zona'       => $idZona,
            'id_espacio'    => $idEspacio,
            'solo_abiertos' => $soloAbiertos
        ],
        'accesos' => $accesos
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error del servidor', 'detail' => $e->getMessage()]);
}
