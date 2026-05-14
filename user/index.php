<?php
session_start();
require_once __DIR__ . '/../db/db.php';

if (isset($_SESSION['member_id'])) {
    $member_id_for_status_check = $_SESSION['member_id'];
    $stmt_status = $conn->prepare("SELECT status FROM members WHERE member_id = ?");
    if ($stmt_status) {
        $stmt_status->bind_param("i", $member_id_for_status_check);
        $stmt_status->execute();
        $result_status = $stmt_status->get_result();
        if ($row_status = $result_status->fetch_assoc()) {
            if ((int) $row_status['status'] === 0) {
                session_unset();
                session_destroy();
                header('Location: index.php?login_error=disabled');
                exit;
            }
        }
        $stmt_status->close();
    }
}

$booking_summary = [
    'total' => 0,
    'pending' => 0,
    'confirmed' => 0,
];
$latest_bookings = [];
$latest_courses = [];
$featured_products = [];

$bookingSummarySql = "
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status IN ('pending', 'awaiting_payment') THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_count
    FROM bookings
";
$bookingSummaryResult = $conn->query($bookingSummarySql);
if ($bookingSummaryResult && $summaryRow = $bookingSummaryResult->fetch_assoc()) {
    $booking_summary['total'] = (int) ($summaryRow['total'] ?? 0);
    $booking_summary['pending'] = (int) ($summaryRow['pending_count'] ?? 0);
    $booking_summary['confirmed'] = (int) ($summaryRow['confirmed_count'] ?? 0);
}

$latestBookingsSql = "
    SELECT booking_date, booking_time, visitor_count, status
    FROM bookings
    ORDER BY bookings_id DESC
    LIMIT 3
";
$latestBookingsResult = $conn->query($latestBookingsSql);
if ($latestBookingsResult) {
    while ($row = $latestBookingsResult->fetch_assoc()) {
        $latest_bookings[] = $row;
    }
}

$latestCoursesSql = "
    SELECT courses_id, course_name, course_description, image1
    FROM courses
    ORDER BY courses_id DESC
    LIMIT 3
";
$latestCoursesResult = $conn->query($latestCoursesSql);
if ($latestCoursesResult) {
    while ($row = $latestCoursesResult->fetch_assoc()) {
        $latest_courses[] = $row;
    }
}

$featuredProductsSql = "
    SELECT
        p.product_id,
        p.product_name,
        p.price,
        p.unit,
        p.product_description,
        p.product_image,
        COALESCE(SUM(CASE WHEN o.order_status = 'completed' THEN oi.quantity ELSE 0 END), 0) AS sold_count
    FROM products p
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.order_id
    WHERE p.status = 'active'
    GROUP BY p.product_id
    ORDER BY sold_count DESC, p.product_id DESC
    LIMIT 4
";
$featuredProductsResult = $conn->query($featuredProductsSql);
if ($featuredProductsResult) {
    while ($row = $featuredProductsResult->fetch_assoc()) {
        $featured_products[] = $row;
    }
}

function format_booking_status_label($status)
{
    switch ($status) {
        case 'confirmed':
            return 'ยืนยันแล้ว';
        case 'awaiting_payment':
            return 'รอชำระเงิน';
        case 'cancelled':
            return 'ยกเลิก';
        default:
            return 'รอตรวจสอบ';
    }
}

function format_booking_status_class($status)
{
    switch ($status) {
        case 'confirmed':
            return 'status-confirmed';
        case 'awaiting_payment':
            return 'status-awaiting';
        case 'cancelled':
            return 'status-cancelled';
        default:
            return 'status-pending';
    }
}

function find_hero_video_source()
{
    $locations = [
        ['dir' => __DIR__ . '/../video', 'url' => '../video'],
        ['dir' => __DIR__ . '/video', 'url' => 'video'],
        ['dir' => __DIR__ . '/../user/video', 'url' => '../user/video'],
        ['dir' => __DIR__ . '/uploads', 'url' => 'uploads'],
        ['dir' => __DIR__ . '/../uploads', 'url' => '../uploads'],
    ];

    $fileNames = ['background-video2.mp4', 'background-video.mp4'];

    foreach ($locations as $location) {
        foreach ($fileNames as $fileName) {
            $path = $location['dir'] . DIRECTORY_SEPARATOR . $fileName;
            if (file_exists($path)) {
                return $location['url'] . '/' . $fileName;
            }
        }
    }

    foreach ($locations as $location) {
        $files = glob($location['dir'] . '/*.mp4');
        if (!empty($files)) {
            return $location['url'] . '/' . basename($files[0]);
        }
    }

    return null;
}

function truncate_text($text, $length = 120)
{
    $text = trim((string) $text);
    if ($text === '') {
        return '';
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text, 'UTF-8') > $length
            ? mb_substr($text, 0, $length, 'UTF-8') . '...'
            : $text;
    }

    return strlen($text) > $length
        ? substr($text, 0, $length) . '...'
        : $text;
}

$heroVideoSrc = find_hero_video_source();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="../logo/logo_01.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../logo/logo_01.png">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#ffffff">
    <title>สวนลุงเผือก</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --forest: #123b2d;
            --forest-deep: #0b261d;
            --green: #016A70;
            --moss: #1f6b52;
            --leaf: #71c59a;
            --cream: #f7f1e8;
            --sand: #e7dccd;
            --stone: #5d685f;
            --clay: #c46f4a;
            --white: #ffffff;
            --shadow-soft: 0 30px 80px rgba(10, 25, 18, 0.12);
            --shadow-card: 0 18px 50px rgba(8, 29, 21, 0.09);
            --radius-lg: 32px;
            --radius-md: 22px;
            --radius-sm: 16px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--leaf);
            background: #ffffff;
            font-family: "Prompt", sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: "Kanit", sans-serif;
            letter-spacing: -0.02em;
        }

        .page-shell {
            overflow: hidden;
        }

        .hero-shell {
            position: relative;
            min-height: calc(100svh - 80px);
            display: flex;
            align-items: stretch;
            background: #000;
        }

        .hero-media {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }

        .hero-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scale(1.04);
            filter: saturate(0.95);
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(8, 26, 20, 0.82) 0%, rgba(8, 26, 20, 0.56) 42%, rgba(8, 26, 20, 0.36) 100%),
                linear-gradient(180deg, rgba(14, 35, 28, 0.16) 0%, rgba(14, 35, 28, 0.68) 100%);
        }

        .hero-grid {
            position: relative;
            z-index: 1;
            width: min(1240px, calc(100% - 32px));
            margin: 0 auto;
            padding: 4rem 0 3.25rem;
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
            gap: 3rem;
            align-items: center;
            min-height: inherit;
        }

        .hero-copy {
            grid-column: 1 / -1;
            justify-self: center;
            align-self: center;
            color: var(--white);
            text-align: center;
        }

        .location-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 999px;
            margin-bottom: 18px;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            color: var(--green);
            background: rgb(255, 255, 255);
            border: 1px solid rgba(13, 107, 99, 0.12);
        }

        .location-kicker::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--location-primary), var(--location-accent));
            box-shadow: 0 0 0 6px rgba(13, 107, 99, 0.09);
        }

        .hero-title {
            margin: 0;
            font-size: clamp(2.0rem, 6vw, 4.5rem);
            line-height: 0.96;
            font-weight: 500;
            text-wrap: balance;
        }

        .hero-description {
            max-width: 560px;
            margin: 1.25rem auto 0;
            font-size: 1.0rem;
            line-height: 1.75;
            color: rgba(255, 255, 255, 0.84);
        }

        .hero-actions {
            display: flex;
            gap: 0.9rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 1.8rem;
        }

        .hero-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 52px;
            padding: 0.85rem 1.4rem;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.28s ease, background-color 0.28s ease, color 0.28s ease, border-color 0.28s ease;
        }

        .hero-btn-primary {
            color: var(--forest-deep);
            background: var(--green);
        }

        .hero-btn-secondary {
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(8px);
        }

        .hero-btn:hover {
            transform: translateY(-2px);
        }

        .main-content-surface {
            background: #ffffff;
            position: relative;
            margin-top: -1px;
            padding-bottom: 1px;
        }

        .section-wrap {
            width: min(1240px, calc(100% - 32px));
            margin: 0 auto;
        }

        .services-block {
            padding: 6.25rem 0 4rem;
        }

        .section-intro {
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(280px, 0.75fr);
            gap: 2rem;
            align-items: end;
            margin-bottom: 2.6rem;
        }

        .eyebrow {
            display: inline-block;
            font-size: 0.88rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--moss);
            margin-bottom: 0.85rem;
        }

        .section-title {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3.15rem);
            line-height: 1.02;
            color: var(--forest-deep);
        }

        .section-lead {
            margin: 0;
            max-width: 460px;
            color: #617066;
            font-size: 1.04rem;
            line-height: 1.75;
        }

        .services-layout {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .service-column {
            padding: 1.8rem 1.4rem 0 0;
            border-top: 1px solid rgba(18, 59, 45, 0.16);
        }

        .service-index {
            display: inline-block;
            font-family: "Kanit", sans-serif;
            font-size: 0.95rem;
            color: var(--clay);
            margin-bottom: 1rem;
        }

        .service-column h3 {
            margin: 0 0 0.8rem;
            font-size: 1.7rem;
            color: var(--forest);
        }

        .service-column p {
            margin: 0 0 1rem;
            color: #647166;
            line-height: 1.75;
        }

        .service-points {
            list-style: none;
            padding: 0;
            margin: 0 0 1.2rem;
        }

        .service-points li {
            position: relative;
            padding-left: 1.1rem;
            color: #2d463c;
            line-height: 1.75;
        }

        .service-points li+li {
            margin-top: 0.45rem;
        }

        .service-points li::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0.78rem;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--leaf);
        }

        .section-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: var(--forest);
            font-weight: 600;
        }

        .section-link::after {
            content: "→";
            transition: transform 0.25s ease;
        }

        .section-link:hover::after {
            transform: translateX(4px);
        }

        .data-section {
            padding: 1.5rem 0 2rem;
        }

        .data-shell {
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-card);
            background:
                radial-gradient(circle at top right, var(--green), transparent 32%),
                linear-gradient(135deg, var(--green) 0%, var(--moss) 100%);
            color: var(--white);
            padding: 2rem;
        }

        .bookings-shell {
            background:
                radial-gradient(circle at top right, var(--green), transparent 32%),
                linear-gradient(135deg, var(--green) 0%, var(--moss) 100%);
            color: var(--white);
            padding: 2rem;
        }

        .shell-head {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 1rem;
            align-items: end;
            margin-bottom: 1.8rem;
        }

        .shell-head h2 {
            margin: 0;
            font-size: clamp(1.8rem, 3.5vw, 2.7rem);
            color: inherit;
        }

        .shell-head p {
            margin: 0.5rem 0 0;
            max-width: 520px;
            color: inherit;
            opacity: 0.78;
            line-height: 1.75;
        }

        .shell-link {
            color: inherit;
            text-decoration: none;
            font-weight: 600;
        }

        .booking-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .booking-panel {
            min-height: 220px;
            padding: 1.3rem;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .booking-panel:hover,
        .course-panel:hover,
        .product-panel:hover {
            transform: translateY(-5px);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .status-confirmed {
            background: rgba(113, 197, 154, 0.16);
            color: #baf0d0;
        }

        .status-awaiting {
            background: rgba(104, 204, 233, 0.14);
            color: #bfefff;
        }

        .status-pending {
            background: rgba(246, 194, 62, 0.16);
            color: #ffe4a1;
        }

        .status-cancelled {
            background: rgba(231, 74, 59, 0.16);
            color: #ffc0b8;
        }

        .booking-date {
            font-size: 1.6rem;
            margin: 0 0 0.2rem;
            color: var(--white);
        }

        .booking-time {
            margin: 0;
            font-size: 1rem;
            opacity: 0.88;
        }

        .booking-meta {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2.3rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.78);
        }

        .courses-shell {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.78) 0%, rgba(255, 255, 255, 0.92) 100%);
            padding: 2rem;
            border: 1px solid rgba(18, 59, 45, 0.08);
        }

        .courses-shell .shell-head h2,
        .products-shell .shell-head h2 {
            color: #000000;
        }

        .courses-shell .shell-head p,
        .products-shell .shell-head p {
            color: #667268;
            opacity: 1;
        }

        .courses-shell .shell-link,
        .products-shell .shell-link {
            color: var(--forest);
        }

        .course-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 1rem;
        }

        .course-stack {
            display: grid;
            gap: 1rem;
        }

        .course-panel {
            position: relative;
            min-height: 320px;
            border-radius: 26px;
            overflow: hidden;
            background: #d8e5dd;
            isolation: isolate;
        }

        .course-panel.small {
            min-height: 154px;
        }

        .course-panel img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.7s ease;
        }

        .course-panel:hover img {
            transform: scale(1.05);
        }

        .course-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(7, 20, 15, 0.04) 0%, rgba(7, 20, 15, 0.74) 100%);
            z-index: 0;
        }

        .course-content {
            position: absolute;
            inset: auto 0 0 0;
            z-index: 1;
            padding: 1.4rem;
            color: var(--white);
        }

        .course-tag {
            display: inline-block;
            margin-bottom: 0.75rem;
            font-size: 0.8rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.72);
        }

        .course-title {
            margin: 0 0 0.5rem;
            font-size: 1.8rem;
            line-height: 1.05;
        }

        .course-panel.small .course-title {
            font-size: 1.32rem;
        }

        .course-desc {
            margin: 0;
            max-width: 90%;
            color: rgba(255, 255, 255, 0.88);
            line-height: 1.65;
        }

        .course-empty,
        .product-empty,
        .booking-empty {
            padding: 1.8rem;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.08);
        }

        .products-shell {
            background: #ffffff;
            padding: 2rem;
            border: 1px solid rgba(18, 59, 45, 0.08);
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .product-panel {
            position: relative;
            background: rgba(255, 255, 255, 0.74);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(15, 38, 29, 0.08);
            transition: transform 0.3s ease;
        }

        .product-media {
            aspect-ratio: 1 / 1;
            background: linear-gradient(135deg, #efe4d2 0%, #e1efdf 100%);
            overflow: hidden;
        }

        .product-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.7s ease;
        }

        .product-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(9, 23, 18, 0.04) 25%, rgba(9, 23, 18, 0.84) 100%);
            opacity: 0;
            transition: opacity 0.35s ease;
            pointer-events: none;
        }

        .product-panel:hover .product-media img {
            transform: scale(1.06);
        }

        .product-content {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
            padding: 1.2rem;
            color: var(--white);
            transform: translateY(calc(100% - 4.5rem));
            transition: transform 0.35s ease;
        }

        .product-title {
            margin: 0 0 0.45rem;
            font-size: 1.3rem;
            color: var(--white);
        }

        .product-desc {
            min-height: 3.2em;
            margin: 0 0 0.9rem;
            color: rgba(255, 255, 255, 0.86);
            line-height: 1.6;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 0.75rem;
        }

        .product-price {
            font-family: "Kanit", sans-serif;
            font-size: 1.45rem;
            line-height: 1;
            color: #ffd8a0;
            font-weight: 700;
        }

        .product-unit,
        .product-sales {
            display: block;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.82);
            margin-top: 0.3rem;
        }

        .product-panel:hover::after {
            opacity: 1;
        }

        .product-panel:hover .product-content {
            transform: translateY(0);
        }

        .final-banner {
            padding: 2rem 0 0;
        }

        .final-banner-inner {
            width: min(1240px, calc(100% - 32px));
            margin: 0 auto;
            padding: 2rem 2.1rem;
            border-radius: 30px;
            background:
                radial-gradient(circle at top right, var(--green), transparent 32%),
                linear-gradient(135deg, var(--green) 0%, var(--moss) 100%);
            color: var(--white);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            box-shadow: var(--shadow-soft);
        }

        .final-banner h3 {
            margin: 0 0 0.45rem;
            font-size: clamp(1.7rem, 3vw, 2.4rem);
        }

        .final-banner p {
            margin: 0;
            max-width: 560px;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.7;
        }

        .final-banner a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 190px;
            min-height: 52px;
            padding: 0.85rem 1.2rem;
            border-radius: 999px;
            background: var(--cream);
            color: var(--forest-deep);
            text-decoration: none;
            font-weight: 600;
        }

        .reveal {
            opacity: 0;
            transform: translateY(28px);
            animation: revealUp 0.85s ease forwards;
        }

        .reveal.delay-1 {
            animation-delay: 0.12s;
        }

        .reveal.delay-2 {
            animation-delay: 0.22s;
        }

        .reveal.delay-3 {
            animation-delay: 0.32s;
        }

        .reveal.delay-4 {
            animation-delay: 0.42s;
        }

        @keyframes revealUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1100px) {

            .hero-grid,
            .section-intro,
            .course-grid {
                grid-template-columns: 1fr;
            }

            .hero-grid {
                grid-template-columns: 1fr;
            }

            .services-layout,
            .booking-grid {
                grid-template-columns: 1fr;
            }

            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .course-panel.small {
                min-height: 220px;
            }
        }

        @media (max-width: 768px) {
            .hero-shell {
                min-height: auto;
            }

            .hero-grid {
                width: calc(100% - 24px);
                padding: 2.5rem 0 2rem;
                gap: 2rem;
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .hero-title {
                line-height: 1.02;
            }

            .hero-description {
                font-size: 1rem;
            }

            .section-wrap,
            .final-banner-inner {
                width: calc(100% - 24px);
            }

            .services-block {
                padding-top: 4.75rem;
            }

            .services-layout {
                grid-template-columns: 1fr;
            }

            .booking-grid {
                grid-template-columns: 1fr;
            }

            .product-grid {
                grid-template-columns: 1fr;
            }

            .bookings-shell,
            .courses-shell,
            .products-shell,
            .final-banner-inner {
                padding: 1.4rem;
            }

            .final-banner-inner {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .hero-grid {
                grid-template-columns: minmax(0, 1fr);
                padding: 3.5rem 0 2.75rem;
            }

            .hero-copy {
                grid-column: auto;
            }
        }
    </style>
    <meta name="google-site-verification" content="bmoWqmU29MxuV-8wzCGKFQ2yEeQHp9BzaSisH2OYMpI" />
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    <?php include __DIR__ . '/fb_chat_button.php'; ?>

    <div class="page-shell">
        <section class="hero-shell">
            <div class="hero-media">
                <?php if (!empty($heroVideoSrc)): ?>
                    <video class="hero-video" autoplay muted loop playsinline preload="auto">
                        <source src="<?= htmlspecialchars($heroVideoSrc, ENT_QUOTES, 'UTF-8') ?>" type="video/mp4">
                    </video>
                <?php endif; ?>
                <div class="hero-overlay"></div>
            </div>

            <div class="hero-grid">
                <div class="hero-copy reveal">
                    <span class="location-kicker">พอเพียง ก็เพียงพอ แบ่งปัน</span>
                    <h2 class="hero-title">ระบบจองคิวเยี่ยมชมศูนย์การเรียนรู้ <br>เศรษฐกิจพอเพียง <br>สวนลุงเผือก</h2>
                    <p class="hero-description">
                        เว็บไซต์ศูนย์การเรียนรู้เศรษฐกิจพอเพียงและเปิดให้เข้าศึกษาดูงาน <br>
                        สวนลุงเผือก บ.บุฮม อ.เชียงคาน จ.เลย
                    </p>
                </div>
            </div>
        </section>

        <div class="main-content-surface">
            <section class="services-block">
                <div class="section-wrap">
                    <div class="section-intro reveal">
                        <div>
                            <span class="eyebrow">Explore Our Services</span>
                            <h2 class="section-title">เปิดประสบการณ์เรียนรู้วิถีธรรมชาติ</h2>
                        </div>
                        <p class="section-lead">
                            ครบจบในที่เดียว ไม่ว่าจะเป็นการวางแผนเข้าชม การเรียนรู้ทักษะใหม่ๆ หรือเลือกซื้อผลผลิตจากความตั้งใจ
                            เราออกแบบทุกฟังก์ชันเพื่อให้คุณเข้าถึงบริการของเราได้อย่างง่ายดายและรวดเร็วที่สุด
                        </p>
                    </div>

                    <div class="services-layout">
                        <article class="service-column reveal delay-1">
                            <span class="service-index">01 / Booking</span>
                            <h3>สัมผัสบรรยากาศจริง</h3>
                            <p>เลือกวัน เวลา และจำนวนผู้เข้าชม พร้อมดูสถานะการจองในระบบได้ต่อเนื่อง</p>
                            <ul class="service-points">
                                <li>เช็กคิวได้แบบ Real-time เลือกวันและเวลาที่สะดวกได้ทันที</li>
                                <li>โปร่งใส ชัดเจน ระบบคำนวณมัดจำแม่นยำ พร้อมสถานะที่ตรวจสอบได้ตลอด 24 ชม.</li>
                            </ul>
                            <a class="section-link" href="../user/bookings.php">ไปหน้าจองคิว</a>
                        </article>

                        <article class="service-column reveal delay-2">
                            <span class="service-index">02 / Activity</span>
                            <h3>ร่วมสร้างประสบการณ์</h3>
                            <p>ดูกิจกรรมล่าสุดของสวนจากภาพและคำอธิบายแบบย่อ ก่อนเข้าไปอ่านรายละเอียดเต็ม</p>
                            <ul class="service-points">
                                <li>บันทึกทุกความทรงจำ ถ่ายทอดผ่านภาพถ่ายและเรื่องราวที่น่าประทับใจ</li>
                                <li>อัปเดตกิจกรรมใหม่ก่อนใคร เข้าถึงรายละเอียดตารางอบรมได้ในคลิกเดียว</li>
                            </ul>
                            <a class="section-link" href="../user/course.php">ดูกิจกรรมทั้งหมด</a>
                        </article>

                        <article class="service-column reveal delay-3">
                            <span class="service-index">03 / Shop</span>
                            <h3>คัดสรรจากสวนถึงมือคุณ</h3>
                            <p>รวมสินค้าเด่นพร้อมราคา หน่วยขาย และจำนวนที่ขายแล้ว เพื่อช่วยตัดสินใจได้เร็วขึ้น</p>
                            <ul class="service-points">
                                <li>สินค้าคุณภาพระดับพรีเมียม คัดเฉพาะไอเทมเด็ด พร้อมดีลพิเศษหน้าเว็บไซต์</li>
                                <li>ช้อปง่าย สบายใจ แสดงราคาและยอดขายจริง ช่วยให้คุณตัดสินใจได้ไม่ผิดพลาด</li>
                            </ul>
                            <a class="section-link" href="../user/products.php">ไปหน้าสินค้า</a>
                        </article>
                    </div>
                </div>
            </section>

            <section class="data-section">
                <div class="section-wrap">
                    <div class="data-shell bookings-shell reveal">
                        <div class="shell-head">
                            <div>
                                <h2>รายการจองล่าสุด</h2>
                                <p>คิวที่จองล่าสุด เพื่อให้ผู้ใช้เห็นความเคลื่อนไหวล่าสุดของการจอง</p>
                            </div>
                            <a href="../user/bookings.php" class="shell-link">ดูหน้าจองคิวทั้งหมด</a>
                        </div>

                        <?php if (!empty($latest_bookings)): ?>
                            <div class="booking-grid">
                                <?php foreach ($latest_bookings as $index => $booking): ?>
                                    <article class="booking-panel reveal delay-<?= min($index + 1, 4) ?>">
                                        <span class="status-pill <?= format_booking_status_class($booking['status']) ?>">
                                            <?= htmlspecialchars(format_booking_status_label($booking['status']), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <h3 class="booking-date"><?= htmlspecialchars(date('d/m/Y', strtotime($booking['booking_date'])), ENT_QUOTES, 'UTF-8') ?></h3>
                                        <p class="booking-time">เวลาเข้าชม <?= htmlspecialchars(substr($booking['booking_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?> น.</p>
                                        <div class="booking-meta">
                                            <span>ผู้เข้าชม</span>
                                            <strong><?= number_format((int) $booking['visitor_count']) ?> คน</strong>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="booking-empty">
                                ยังไม่มีข้อมูลการจองล่าสุดในระบบ
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="data-section">
                <div class="section-wrap">
                    <div class="data-shell courses-shell reveal">
                        <div class="shell-head">
                            <div>
                                <h2>กิจกรรมอบรมล่าสุด</h2>
                                <p>หมวดกิจกรรมถูกออกแบบให้เน้นภาพและบรรยากาศมากขึ้น เพื่อให้ผู้ใช้เห็นเนื้อหาล่าสุดของสวนแบบชัดและน่าสนใจ</p>
                            </div>
                            <a href="../user/course.php" class="shell-link">ดูกิจกรรมทั้งหมด</a>
                        </div>

                        <?php if (!empty($latest_courses)): ?>
                            <?php
                            $featuredCourse = $latest_courses[0];
                            $otherCourses = array_slice($latest_courses, 1);
                            $featuredCourseImage = !empty($featuredCourse['image1']) ? '../uploads/' . rawurlencode($featuredCourse['image1']) : '';
                            ?>
                            <div class="course-grid">
                                <article class="course-panel reveal delay-1">
                                    <?php if ($featuredCourseImage !== ''): ?>
                                        <img src="<?= htmlspecialchars($featuredCourseImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($featuredCourse['course_name'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?php endif; ?>
                                    <div class="course-content">
                                        <span class="course-tag">Featured Activity</span>
                                        <h3 class="course-title"><?= htmlspecialchars($featuredCourse['course_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                        <p class="course-desc"><?= htmlspecialchars(truncate_text($featuredCourse['course_description'], 135), ENT_QUOTES, 'UTF-8') ?></p>
                                        <a class="shell-link" href="../user/course_detail.php?id=<?= (int) $featuredCourse['courses_id'] ?>">ดูรายละเอียดกิจกรรม</a>
                                    </div>
                                </article>

                                <div class="course-stack">
                                    <?php if (!empty($otherCourses)): ?>
                                        <?php foreach ($otherCourses as $index => $course): ?>
                                            <?php $courseImage = !empty($course['image1']) ? '../uploads/' . rawurlencode($course['image1']) : ''; ?>
                                            <article class="course-panel small reveal delay-<?= min($index + 2, 4) ?>">
                                                <?php if ($courseImage !== ''): ?>
                                                    <img src="<?= htmlspecialchars($courseImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8') ?>">
                                                <?php endif; ?>
                                                <div class="course-content">
                                                    <span class="course-tag">Latest Course</span>
                                                    <h3 class="course-title"><?= htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                                    <p class="course-desc"><?= htmlspecialchars(truncate_text($course['course_description'], 76), ENT_QUOTES, 'UTF-8') ?></p>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="course-empty">ยังไม่มีรายการกิจกรรมเพิ่มเติม</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="course-empty">
                                ยังไม่มีกิจกรรมอบรมให้แสดงในขณะนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="data-section">
                <div class="section-wrap">
                    <div class="data-shell products-shell reveal">
                        <div class="shell-head">
                            <div>
                                <h2>สินค้าแนะนำบางส่วน</h2>
                                <p>หมวดสินค้าถูกแยกออกมาเป็นพื้นที่เฉพาะของร้าน เพื่อให้ผู้ใช้มองเห็นของเด่น ราคา และยอดขายได้แบบสแกนง่าย</p>
                            </div>
                            <a href="../user/products.php" class="shell-link">ดูสินค้าทั้งหมด</a>
                        </div>

                        <?php if (!empty($featured_products)): ?>
                            <div class="product-grid">
                                <?php foreach ($featured_products as $index => $product): ?>
                                    <?php $productImage = !empty($product['product_image']) ? '../admin/uploads/products/' . rawurlencode($product['product_image']) : ''; ?>
                                    <article class="product-panel reveal delay-<?= min($index + 1, 4) ?>">
                                        <div class="product-media">
                                            <?php if ($productImage !== ''): ?>
                                                <img src="<?= htmlspecialchars($productImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-content">
                                            <h3 class="product-title"><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                            <p class="product-desc"><?= htmlspecialchars(truncate_text($product['product_description'] ?: 'สินค้าแนะนำจากสวนลุงเผือก', 72), ENT_QUOTES, 'UTF-8') ?></p>
                                            <div class="product-footer">
                                                <div>
                                                    <div class="product-price">฿<?= number_format((float) $product['price']) ?></div>
                                                    <span class="product-unit">ต่อ <?= htmlspecialchars($product['unit'], ENT_QUOTES, 'UTF-8') ?></span>
                                                </div>
                                                <span class="product-sales">ขายแล้ว <?= number_format((int) $product['sold_count']) ?></span>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="product-empty">
                                ยังไม่มีสินค้าแนะนำในขณะนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="final-banner">
                <div class="final-banner-inner reveal">
                    <div>
                        <h3>พร้อมเริ่มใช้งานส่วนไหนก่อน</h3>
                        <p>ไม่ว่าจะจองคิวเพื่อเข้าศึกษาดูงาน เลือกดูกิจกรรมอบรม หรือเลือกซื้อสินค้าของสวน ตอนนี้ทุกอย่างพร้อมใช้งานเรียบร้อยแล้ว</p>
                    </div>
                    <a href="../user/bookings.php">เริ่มจองคิวตอนนี้</a>
                </div>
            </section>

            <?php include 'location.php'; ?>
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const loginError = urlParams.get('login_error');

            if (loginError === 'disabled') {
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่สามารถเข้าสู่ระบบได้',
                    text: 'บัญชีของคุณถูกปิดการใช้งาน หากต้องการใช้งานกรุณาสมัครสมาชิกใหม่ หรือติดต่อผู้ดูแล',
                    confirmButtonText: 'ตกลง'
                });
            }
        });
    </script>
</body>

</html>
