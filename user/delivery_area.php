<?php
declare(strict_types=1);

function delivery_area_point_on_segment(float $x, float $y, float $x1, float $y1, float $x2, float $y2): bool
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

function delivery_area_point_in_ring(float $longitude, float $latitude, array $ring): bool
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

        if (delivery_area_point_on_segment($longitude, $latitude, $xi, $yi, $xj, $yj)) {
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

function delivery_area_point_in_polygon_coordinates(float $longitude, float $latitude, array $polygon): bool
{
    if (empty($polygon[0]) || !delivery_area_point_in_ring($longitude, $latitude, $polygon[0])) {
        return false;
    }

    $holeCount = count($polygon);
    for ($i = 1; $i < $holeCount; $i++) {
        if (delivery_area_point_in_ring($longitude, $latitude, $polygon[$i])) {
            return false;
        }
    }

    return true;
}

function point_in_delivery_area(float $latitude, float $longitude, ?string $geoJsonPath = null): bool
{
    $geoJsonPath ??= __DIR__ . '/Map/DeliveryArea.geojson';
    if (!is_file($geoJsonPath)) {
        throw new RuntimeException('Delivery area file not found');
    }

    $geoJson = json_decode((string) file_get_contents($geoJsonPath), true);
    if (!is_array($geoJson)) {
        throw new RuntimeException('Delivery area file is invalid');
    }

    $features = ($geoJson['type'] ?? '') === 'FeatureCollection'
        ? ($geoJson['features'] ?? [])
        : [$geoJson];

    foreach ($features as $feature) {
        $geometry = ($feature['type'] ?? '') === 'Feature'
            ? ($feature['geometry'] ?? null)
            : $feature;

        if (!is_array($geometry)) {
            continue;
        }

        $type = $geometry['type'] ?? '';
        $coordinates = $geometry['coordinates'] ?? [];

        if ($type === 'Polygon' && delivery_area_point_in_polygon_coordinates($longitude, $latitude, $coordinates)) {
            return true;
        }

        if ($type === 'MultiPolygon') {
            foreach ($coordinates as $polygon) {
                if (delivery_area_point_in_polygon_coordinates($longitude, $latitude, $polygon)) {
                    return true;
                }
            }
        }
    }

    return false;
}
