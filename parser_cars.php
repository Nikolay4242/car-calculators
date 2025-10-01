<?php
// parser_cars.php
// Работает в MOCK-режиме, парсит fixtures/debug-cars.html или выдаёт заглушку.

header('Content-Type: application/json; charset=utf-8');

$projectRoot = __DIR__ . '/configs';
$configFile = $projectRoot . '/config.php';
$CONFIG = ['MOCK' => true];

if (file_exists($configFile)) {
    $cfg = require $configFile;
    if (is_array($cfg)) $CONFIG = array_merge($CONFIG, $cfg);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];
$url = trim($data['link'] ?? '');

if (!$url) {
    echo json_encode(['error' => 'link обязателен']);
    exit;
}

// ---------------- MOCK-РЕЖИМ ----------------
if ($CONFIG['MOCK']) {
    $fixture = __DIR__ . '/fixtures/debug-cars.html';
    if (file_exists($fixture)) {
        $html = file_get_contents($fixture);
    } else {
        $html = '<html><body>
            <h1 class="listing-title">2021 Demo Car</h1>
            <span data-qa="primary-price">$24,999</span>
            <dt>Fuel type</dt><dd>Gasoline</dd>
            <dt>Engine</dt><dd>3.5 L</dd>
            <dt>Mileage</dt><dd>12,345 mi</dd>
        </body></html>';
    }

    preg_match('/<h1[^>]*class="listing-title"[^>]*>(.*?)<\/h1>/i', $html, $nameMatch);
    $name = trim(strip_tags($nameMatch[1] ?? 'Demo Car'));
    $year = (preg_match('/(\d{4})/', $name, $y)) ? $y[1] : '';

    preg_match('/\$([\d,]+)/', $html, $priceMatch);
    $price = str_replace(',', '', $priceMatch[1] ?? '');

    preg_match('/Fuel type.*?<dd[^>]*>(.*?)<\/dd>/is', $html, $fuelMatch);
    $fuel = trim($fuelMatch[1] ?? 'Unknown');

    preg_match('/Engine.*?<dd[^>]*>(.*?)<\/dd>/is', $html, $engineMatch);
    $engineRaw = trim($engineMatch[1] ?? '');

    preg_match('/Mileage.*?([\d,\.]+)\s*mi/i', $html, $mileageMatch);
    $mileage = str_replace([',', '.'], '', $mileageMatch[1] ?? '');

    $engine = 0;
    if (preg_match('/(\d+(?:[.,]\d+)?)\s*[lл]/iu', $engineRaw, $liters)) {
        $engine = intval(floatval(str_replace(',', '.', $liters[1])) * 1000);
    }

    $fuelLower = strtolower($fuel);
    $isElectric = strpos($fuelLower, 'electric') !== false;
    $isHybrid   = strpos($fuelLower, 'hybrid') !== false;

    echo json_encode([
        'name' => $name,
        'year' => $year,
        'price' => $price,
        'fuel' => $fuel,
        'engine' => $engine,
        'mileage' => $mileage,
        'isElectric' => $isElectric,
        'isHybrid' => $isHybrid,
        'mode' => 'mock'
    ]);
    exit;
}

// ---------------- РЕАЛЬНЫЙ РЕЖИМ ----------------
echo json_encode(['error' => 'Реальный режим отключён в демо', 'mode' => 'real_disabled']);
