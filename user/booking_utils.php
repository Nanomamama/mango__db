<?php
// Helper utilities for booking calculations and validations

function calculate_booking_totals(int $visitor_count): array {
    $price_per_person = 150.00;
    $instructor_fee = 1800.00;
    $venue_fee = 3000.00;

    $price_total = ($visitor_count * $price_per_person) + $instructor_fee + $venue_fee;
    $deposit_amount = round($price_total * 0.3, 2);
    $balance_amount = round($price_total - $deposit_amount, 2);

    return [
        'price_per_person' => $price_per_person,
        'instructor_fee' => $instructor_fee,
        'venue_fee' => $venue_fee,
        'price_total' => $price_total,
        'deposit_amount' => $deposit_amount,
        'balance_amount' => $balance_amount,
    ];
}

function valid_upload_file(array $file, array $opts = []): array {
    $max = $opts['max'] ?? 5 * 1024 * 1024;
    $allow = $opts['allow_ext'] ?? ['pdf','jpg','jpeg','png'];
    $allowMime = $opts['allow_mime'] ?? ['application/pdf','image/jpeg','image/png'];

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'upload error'];
    }
    if ($file['size'] > $max) return [false, 'file too large'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allow, true)) return [false, 'invalid extension'];
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, $allowMime, true)) return [false, 'invalid mime'];
        }
    }
    return [true, 'ok'];
}

?>
