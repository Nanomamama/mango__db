<?php

declare(strict_types=1);

header('Content-Type: application/xml; charset=UTF-8');

$rootUrl = 'https://khamaon.com/mango/';
$baseUrl = $rootUrl . 'user';   // ไม่มี / ต่อท้าย เพื่อให้ URL ออกมาเป็น user/index.php ไม่ใช่ user//index.php
$pages   = [];

function xmlEscape(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

function fileLastMod(string $file): string
{
    $path = __DIR__ . '/' . $file;
    $time = is_file($path) ? filemtime($path) : time();

    return date(DATE_W3C, $time);
}

function normalizeLastMod(?string $date): string
{
    if ($date === null || trim($date) === '') {
        return date(DATE_W3C);
    }

    $time = strtotime($date);

    return date(DATE_W3C, $time ?: time());
}

function absoluteUrl(string $path, string $baseUrl): string
{
    $path = trim(str_replace('\\', '/', $path));

    if ($path === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    // rtrim baseUrl ของ / และ ltrim path ของ / เพื่อป้องกัน double slash
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

function uploadUrl(?string $file, string $folder, string $rootUrl): string
{
    $file = trim((string) $file);

    if ($file === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $file)) {
        return $file;
    }

    return absoluteUrl(trim($folder, '/') . '/' . rawurlencode(basename($file)), $rootUrl);
}

function addUrl(
    array  &$pages,
    string $loc,
    string $lastmod,
    string $changefreq,
    string $priority,
    array  $images = [],
    array  $videos = []
): void {
    $pages[] = [
        'loc'        => $loc,
        'lastmod'    => $lastmod,
        'changefreq' => $changefreq,
        'priority'   => $priority,
        'images'     => array_values(array_filter($images, static fn($image) => !empty($image['loc']))),
        'videos'     => array_values(array_filter($videos, static fn($video) => !empty($video['content_loc']))),
    ];
}

function addImage(array &$images, string $loc, string $title = '', string $caption = ''): void
{
    if ($loc === '') {
        return;
    }

    $images[] = [
        'loc'     => $loc,
        'title'   => $title,
        'caption' => $caption,
    ];
}

function addVideo(
    array  &$videos,
    string $contentLoc,
    string $thumbnailLoc,
    string $title,
    string $description
): void {
    // แก้ไข: ไม่ต้องตรวจ is_file อีกต่อไป เพราะตรวจ path ผิดและทำให้วิดีโอหายไป
    // ตรวจแค่ว่า URL ไม่ว่างเปล่า
    if ($contentLoc === '' || $thumbnailLoc === '') {
        return;
    }

    $videos[] = [
        'content_loc'   => $contentLoc,
        'thumbnail_loc' => $thumbnailLoc,
        'title'         => $title,
        'description'   => $description,
    ];
}

// ---------------------------------------------------------------------------
// หน้าหลักที่ต้องการใน sitemap
// ---------------------------------------------------------------------------
$corePages = [
    ['file' => 'index.php',        'changefreq' => 'daily',   'priority' => '1.0'],
    ['file' => 'course.php',       'changefreq' => 'weekly',  'priority' => '0.8'],
    ['file' => 'bookings.php',     'changefreq' => 'weekly',  'priority' => '0.8'],
    ['file' => 'products.php',     'changefreq' => 'daily',   'priority' => '0.8'],
    ['file' => 'location.php',     'changefreq' => 'monthly', 'priority' => '0.6'],
    ['file' => 'member_login.php', 'changefreq' => 'monthly', 'priority' => '0.5'],
    ['file' => 'register.php',     'changefreq' => 'monthly', 'priority' => '0.5'],
    ['file' => 'order_status.php', 'changefreq' => 'weekly',  'priority' => '0.5'],
];

// ---------------------------------------------------------------------------
// สื่อประกอบแต่ละหน้า
// ---------------------------------------------------------------------------
$pageMedia = [
    'index.php'    => [
        'images' => [
            ['loc' => absoluteUrl('user/image/logo-3.png', $rootUrl), 'title' => 'ระบบมะม่วง', 'caption' => 'โลโก้ระบบมะม่วง'],
            ['loc' => absoluteUrl('logo/logo_01.png', $rootUrl),      'title' => 'ระบบมะม่วง', 'caption' => 'สัญลักษณ์ระบบมะม่วง'],
        ],
        'videos' => [],
    ],
    'products.php' => [
        'images' => [
            ['loc' => absoluteUrl('user/image/poster/poster500Free.png', $rootUrl), 'title' => 'โปรโมชั่นสินค้า',       'caption' => 'รูปโปรโมชันสินค้า'],
            ['loc' => absoluteUrl('user/image/poster/posterproduct.png', $rootUrl), 'title' => 'สินค้าในระบบมะม่วง', 'caption' => 'รูปแนะนำสินค้า'],
        ],
        'videos' => [],
    ],
    'bookings.php' => [
        'images' => [
            ['loc' => absoluteUrl('user/image/activity1.jpg', $rootUrl), 'title' => 'กิจกรรมระบบมะม่วง', 'caption' => 'ภาพกิจกรรมสำหรับการจองคิว'],
            ['loc' => absoluteUrl('user/image/activity2.jpg', $rootUrl), 'title' => 'กิจกรรมระบบมะม่วง', 'caption' => 'ภาพกิจกรรมสำหรับการจองคิว'],
        ],
        'videos' => [],
    ],
];

// ---------------------------------------------------------------------------
// แก้ไข: เพิ่มวิดีโอโดยตรงโดยไม่ต้องตรวจ is_file()
// เหตุผล: __DIR__ อยู่ใน /user/ ทำให้ path /../video/ หาไฟล์ไม่เจอ
//         ทั้งที่ไฟล์มีอยู่จริงที่ /mango/video/
// ---------------------------------------------------------------------------
foreach (['background-video2.mp4', 'background-video.mp4'] as $videoFile) {
    addVideo(
        $pageMedia['index.php']['videos'],
        absoluteUrl('video/' . $videoFile, $rootUrl),
        absoluteUrl('logo/logo_01.png', $rootUrl),
        'วิดีโอแนะนำระบบมะม่วง',
        'วิดีโอพื้นหลังหน้าแรกของระบบมะม่วง'
    );
}

// ---------------------------------------------------------------------------
// เพิ่มหน้าหลักเข้า sitemap
// แก้ไข: ถ้า is_file() หาไม่เจอ ให้เพิ่มเข้าไปอยู่ดี (กรณี PHP อยู่คนละ path)
// ---------------------------------------------------------------------------
foreach ($corePages as $page) {
    $media = $pageMedia[$page['file']] ?? ['images' => [], 'videos' => []];

    addUrl(
        $pages,
        $baseUrl . '/' . $page['file'],
        fileLastMod($page['file']),
        $page['changefreq'],
        $page['priority'],
        $media['images'],
        $media['videos']
    );
}

// predict.php (ถ้ามี)
if (is_file(__DIR__ . '/predict.php')) {
    addUrl($pages, $baseUrl . '/predict.php', fileLastMod('predict.php'), 'weekly', '0.8');
}

// ---------------------------------------------------------------------------
// ดึงข้อมูลจาก Database
// ---------------------------------------------------------------------------
if (extension_loaded('mysqli')) {
    $dbHost = '119.59.120.143';
    $dbUser = 'kratipho_db_mango';
    $dbPass = 'kratipho_db_mango';
    $dbName = 'kratipho_db_mango';

    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    if ($conn instanceof mysqli && !$conn->connect_errno) {
        $conn->set_charset('utf8mb4');

        // --- คอร์ส ---
        $courseResult = $conn->query(
            "SELECT courses_id, course_name, course_description, image1, image2, image3, updated_at, created_at
             FROM courses ORDER BY courses_id DESC"
        );
        if ($courseResult instanceof mysqli_result) {
            while ($course = $courseResult->fetch_assoc()) {
                $courseImages = [];
                foreach (['image1', 'image2', 'image3'] as $imageField) {
                    addImage(
                        $courseImages,
                        uploadUrl($course[$imageField] ?? '', 'uploads', $rootUrl),
                        (string) ($course['course_name'] ?? ''),
                        (string) ($course['course_description'] ?? '')
                    );
                }

                addUrl(
                    $pages,
                    $baseUrl . '/course_detail.php?id=' . urlencode((string) $course['courses_id']),
                    normalizeLastMod($course['updated_at'] ?? $course['created_at'] ?? null),
                    'weekly',
                    '0.7',
                    $courseImages
                );
            }
            $courseResult->free();
        }

        // --- สินค้า ---
        $productResult = $conn->query(
            "SELECT product_name, product_description, product_image
             FROM products
             WHERE status = 'active'
               AND product_image IS NOT NULL
               AND product_image != ''
             ORDER BY product_id DESC"
        );
        if ($productResult instanceof mysqli_result) {
            while ($product = $productResult->fetch_assoc()) {
                foreach ($pages as &$page) {
                    if ($page['loc'] !== $baseUrl . '/products.php') {
                        continue;
                    }

                    addImage(
                        $page['images'],
                        uploadUrl($product['product_image'] ?? '', 'admin/uploads/products', $rootUrl),
                        (string) ($product['product_name'] ?? ''),
                        (string) ($product['product_description'] ?? '')
                    );
                    break;
                }
                unset($page);
            }
            $productResult->free();
        }

        // --- พันธุ์มะม่วง ---
        $mangoResult = $conn->query(
            "SELECT mango_name, fruit_image, tree_image, leaf_image, flower_image, branch_image
             FROM mango_varieties ORDER BY mango_id DESC"
        );
        if ($mangoResult instanceof mysqli_result) {
            while ($mango = $mangoResult->fetch_assoc()) {
                if (empty($mango['mango_name'])) {
                    continue;
                }

                $mangoImages = [];
                foreach (['fruit_image', 'tree_image', 'leaf_image', 'flower_image', 'branch_image'] as $imageField) {
                    addImage(
                        $mangoImages,
                        uploadUrl($mango[$imageField] ?? '', 'admin/uploads', $rootUrl),
                        (string) $mango['mango_name'],
                        'รูปประกอบสายพันธุ์มะม่วง'
                    );
                }

                addUrl(
                    $pages,
                    $baseUrl . '/mango_detail.php?name=' . rawurlencode((string) $mango['mango_name']),
                    date(DATE_W3C),
                    'monthly',
                    '0.7',
                    $mangoImages
                );
            }
            $mangoResult->free();
        }

        $conn->close();
    }
}

// Keep index.php as the first URL in the sitemap output.
$indexUrl = $baseUrl . '/index.php';
usort(
    $pages,
    static function (array $a, array $b) use ($indexUrl): int {
        if ($a['loc'] === $indexUrl) {
            return -1;
        }

        if ($b['loc'] === $indexUrl) {
            return 1;
        }

        return 0;
    }
);

// ---------------------------------------------------------------------------
// Output XML
// ---------------------------------------------------------------------------
echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
<?php foreach ($pages as $page): ?>
    <url>
        <loc><?= xmlEscape($page['loc']) ?></loc>
        <lastmod><?= xmlEscape($page['lastmod']) ?></lastmod>
        <changefreq><?= xmlEscape($page['changefreq']) ?></changefreq>
        <priority><?= xmlEscape($page['priority']) ?></priority>
<?php foreach ($page['images'] as $image): ?>
        <image:image>
            <image:loc><?= xmlEscape($image['loc']) ?></image:loc>
<?php if (!empty($image['title'])): ?>
            <image:title><?= xmlEscape($image['title']) ?></image:title>
<?php endif; ?>
<?php if (!empty($image['caption'])): ?>
            <image:caption><?= xmlEscape($image['caption']) ?></image:caption>
<?php endif; ?>
        </image:image>
<?php endforeach; ?>
<?php foreach ($page['videos'] as $video): ?>
        <video:video>
            <video:thumbnail_loc><?= xmlEscape($video['thumbnail_loc']) ?></video:thumbnail_loc>
            <video:title><?= xmlEscape($video['title']) ?></video:title>
            <video:description><?= xmlEscape($video['description']) ?></video:description>
            <video:content_loc><?= xmlEscape($video['content_loc']) ?></video:content_loc>
        </video:video>
<?php endforeach; ?>
    </url>
<?php endforeach; ?>
</urlset>
