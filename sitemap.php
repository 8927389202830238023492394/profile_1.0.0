<?php
// ============================================================
// SITEMAP GENERATOR — XML SITEMAP SYSTEM V1
// ============================================================
require_once __DIR__ . '/includes/functions.php';

$s = getAllSettings();
if (($s['sitemap_enabled'] ?? '1') !== '1') {
    header("HTTP/1.0 404 Not Found");
    exit('Sitemap disabled.');
}

header("Content-Type: application/xml; charset=utf-8");

$baseUrl = rtrim(getSetting('canonical_url', BASE_URL), '/');

// Helper function to create SEO friendly slugs
function toSlug($string) {
    $search = array(
        '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
        '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
        '#(ì|í|ị|ỉ|ĩ)#',
        '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
        '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
        '#(ỳ|ý|ỵ|ỷ|ỹ)#',
        '#(đ)#',
        '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
        '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
        '#(Ì|Í|Ị|Ỉ|Ĩ)#',
        '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
        '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
        '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
        '#(Đ)#',
        "/[^a-zA-Z0-9\-\_]/"
    );
    $replace = array(
        'a', 'e', 'i', 'o', 'u', 'y', 'd',
        'A', 'E', 'I', 'O', 'U', 'Y', 'D', '-'
    );
    $string = preg_replace($search, $replace, $string);
    $string = preg_replace('/(-)+/', '-', $string);
    $string = strtolower(trim($string, '-'));
    return $string;
}

// Start XML
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Helper function to format date
function formatSitemapDate($dateStr) {
    if (!$dateStr) return date('Y-m-d');
    $time = strtotime($dateStr);
    return $time ? date('Y-m-d', $time) : date('Y-m-d');
}

// 1. Home Page & Static Sections
$sections = [
    '/' => ['priority' => '1.0', 'freq' => 'daily'],
    '/services' => ['priority' => '0.9', 'freq' => 'weekly'],
    '/websites' => ['priority' => '0.8', 'freq' => 'weekly'],
    '/about' => ['priority' => '0.8', 'freq' => 'monthly'],
    '/contact' => ['priority' => '0.8', 'freq' => 'monthly']
];

foreach ($sections as $path => $meta) {
    $xml .= '    <url>' . "\n";
    $xml .= '        <loc>' . $baseUrl . $path . '</loc>' . "\n";
    $xml .= '        <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    $xml .= '        <changefreq>' . $meta['freq'] . '</changefreq>' . "\n";
    $xml .= '        <priority>' . $meta['priority'] . '</priority>' . "\n";
    $xml .= '    </url>' . "\n";
}

// 2. Services (Priority: 0.9, Freq: weekly) - Dynamic
try {
    $services = db()->query("SELECT name, created_at FROM services WHERE status=1")->fetchAll();
    foreach ($services as $srv) {
        $slug = toSlug($srv['name']);
        if ($slug) {
            $xml .= '    <url>' . "\n";
            $xml .= '        <loc>' . $baseUrl . '/service/' . $slug . '</loc>' . "\n";
            $xml .= '        <lastmod>' . formatSitemapDate($srv['created_at']) . '</lastmod>' . "\n";
            $xml .= '        <changefreq>weekly</changefreq>' . "\n";
            $xml .= '        <priority>0.9</priority>' . "\n";
            $xml .= '    </url>' . "\n";
        }
    }
} catch (Exception $e) {}

// 3. Websites (Priority: 0.8, Freq: weekly) - Dynamic
try {
    $websites = db()->query("SELECT name, created_at FROM websites WHERE status=1")->fetchAll();
    foreach ($websites as $web) {
        $slug = toSlug($web['name']);
        if ($slug) {
            $xml .= '    <url>' . "\n";
            $xml .= '        <loc>' . $baseUrl . '/website/' . $slug . '</loc>' . "\n";
            $xml .= '        <lastmod>' . formatSitemapDate($web['created_at']) . '</lastmod>' . "\n";
            $xml .= '        <changefreq>weekly</changefreq>' . "\n";
            $xml .= '        <priority>0.8</priority>' . "\n";
            $xml .= '    </url>' . "\n";
        }
    }
} catch (Exception $e) {}

// End XML
$xml .= '</urlset>';

echo $xml;
