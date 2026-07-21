<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db/db.php';

function respond_json(bool $success, string $message, array $extra = []): never
{
    echo json_encode(
        array_merge([
            'success' => $success,
            'message' => $message,
        ], $extra),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function ensure_delivery_locations_table(mysqli $conn): void
{
    $sql = "
        CREATE TABLE IF NOT EXISTS delivery_locations (
            location_id INT(11) NOT NULL AUTO_INCREMENT,
            member_id INT(11) DEFAULT NULL,
            latitude DECIMAL(10,7) NOT NULL,
            longitude DECIMAL(10,7) NOT NULL,
            address_note TEXT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (location_id),
            KEY idx_delivery_locations_member_id (member_id),
            KEY idx_delivery_locations_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ";

    if (!$conn->query($sql)) {
        throw new RuntimeException($conn->error);
    }
}

function point_on_segment(float $x, float $y, float $x1, float $y1, float $x2, float $y2): bool
{
    $cross = ($y - $y1) * ($x2 - $x1) - ($x - $x1) * ($y2 - $y1);
    if (abs($cross) > 0.0000001) {
        return false;
    }

    return $x >= min($x1, $x2) - 0.0000001
        && $x <= max($x1, $x2) + 0.0000001
        && $y >= min($y1, $y2) - 0.0000001
        && $y <= max($y1, $y2) + 0.0000001;
}

function point_in_ring(float $longitude, float $latitude, array $ring): bool
{
    $inside = false;
    $count = count($ring);

    if ($count < 4) {
        return false;
    }

    for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
        $xi = (float) ($ring[$i][0] ?? 0);
        $yi = (float) ($ring[$i][1] ?? 0);
        $xj = (float) ($ring[$j][0] ?? 0);
        $yj = (float) ($ring[$j][1] ?? 0);

        if (point_on_segment($longitude, $latitude, $xi, $yi, $xj, $yj)) {
            return true;
        }

        $intersects = (($yi > $latitude) !== ($yj > $latitude))
            && ($longitude < (($xj - $xi) * ($latitude - $yi) / (($yj - $yi) ?: 0.0000001) + $xi));

        if ($intersects) {
            $inside = !$inside;
        }
    }

    return $inside;
}

function point_in_polygon_coordinates(float $longitude, float $latitude, array $polygon): bool
{
    if (empty($polygon[0]) || !point_in_ring($longitude, $latitude, $polygon[0])) {
        return false;
    }

    $holeCount = count($polygon);
    for ($i = 1; $i < $holeCount; $i++) {
        if (point_in_ring($longitude, $latitude, $polygon[$i])) {
            return false;
        }
    }

    return true;
}

function point_in_delivery_area(float $latitude, float $longitude): bool
{
    $geoJsonPath = __DIR__ . '/Map/DeliveryArea.geojson';
    if (!is_file($geoJsonPath)) {
        throw new RuntimeException('Delivery area file not found');
    }

    $geoJson = json_decode((string) file_get_contents($geoJsonPath), true);
    if (!is_array($geoJson)) {
        throw new RuntimeException('Delivery area file is invalid');
    }

    $features = $geoJson['type'] === 'FeatureCollection'
        ? ($geoJson['features'] ?? [])
        : [$geoJson];

    foreach ($features as $feature) {
        $geometry = $feature['type'] === 'Feature'
            ? ($feature['geometry'] ?? null)
            : $feature;

        if (!is_array($geometry)) {
            continue;
        }

        $type = $geometry['type'] ?? '';
        $coordinates = $geometry['coordinates'] ?? [];

        if ($type === 'Polygon' && point_in_polygon_coordinates($longitude, $latitude, $coordinates)) {
            return true;
        }

        if ($type === 'MultiPolygon') {
            foreach ($coordinates as $polygon) {
                if (point_in_polygon_coordinates($longitude, $latitude, $polygon)) {
                    return true;
                }
            }
        }
    }

    return false;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond_json(false, 'รองรับเฉพาะการบันทึกแบบ POST เท่านั้น');
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_POST;
}

if (
    empty($payload['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals((string) $_SESSION['csrf_token'], (string) $payload['csrf_token'])
) {
    http_response_code(403);
    respond_json(false, 'การร้องขอไม่ถูกต้อง กรุณาโหลดหน้าใหม่แล้วลองอีกครั้ง');
}

$latitude = isset($payload['latitude']) ? (float) $payload['latitude'] : null;
$longitude = isset($payload['longitude']) ? (float) $payload['longitude'] : null;
$addressNote = trim((string) ($payload['address_note'] ?? ''));

if ($latitude === null || $longitude === null || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
    http_response_code(422);
    respond_json(false, 'พิกัดตำแหน่งไม่ถูกต้อง');
}

if (mb_strlen($addressNote, 'UTF-8') > 500) {
    http_response_code(422);
    respond_json(false, 'รายละเอียดจุดส่งต้องไม่เกิน 500 ตัวอักษร');
}

try {
    if (!point_in_delivery_area($latitude, $longitude)) {
        http_response_code(422);
        respond_json(false, 'ตำแหน่งนี้อยู่นอกพื้นที่จัดส่ง');
    }
} catch (Throwable $e) {
    error_log('Delivery area validation failed: ' . $e->getMessage());
    http_response_code(500);
    respond_json(false, 'ไม่สามารถตรวจสอบพื้นที่จัดส่งได้');
}

$memberId = isset($_SESSION['member_id']) ? (int) $_SESSION['member_id'] : null;
$ipAddress = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
$userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    ensure_delivery_locations_table($conn);

    $stmt = $conn->prepare("
        INSERT INTO delivery_locations (
            member_id,
            latitude,
            longitude,
            address_note,
            ip_address,
            user_agent
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'iddsss',
        $memberId,
        $latitude,
        $longitude,
        $addressNote,
        $ipAddress,
        $userAgent
    );
    $stmt->execute();
    $locationId = $stmt->insert_id;
    $stmt->close();

    respond_json(true, 'บันทึกตำแหน่งจัดส่งสำเร็จ', [
        'location_id' => $locationId,
    ]);
} catch (Throwable $e) {
    error_log('Save delivery location failed: ' . $e->getMessage());
    http_response_code(500);
    respond_json(false, 'ไม่สามารถบันทึกตำแหน่งได้ กรุณาตรวจสอบฐานข้อมูลแล้วลองใหม่');
}
