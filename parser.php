<?php
// parser.php — безопасная версия для портфолио
// По умолчанию работает в MOCK-режиме и возвращает данные из заглушки или файла fixtures/debug-mobile.html.
// Если в configs/config.php выставить 'MOCK' => false и указать реальный ключ ScraperAPI,
// то можно включить реальный режим (но в публичном репо он выключен).

header('Content-Type: application/json; charset=utf-8');

$projectRoot = __DIR__ . '/configs';
$configFile = $projectRoot . '/config.php';

// Конфигурация по умолчанию
$CONFIG = [
    'MOCK' => true,
    'SCRAPERAPI_KEY' => '',
    'VPS_API_URL' => ''
];

// Загружаем config.php (если он есть и не в .gitignore)
if (file_exists($configFile)) {
    $cfg = require $configFile;
    if (is_array($cfg)) {
        $CONFIG = array_merge($CONFIG, $cfg);
    }
}

// Читаем входные данные
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];
$url = trim($data['link'] ?? '');

if (!$url) {
    echo json_encode(['error' => 'link обязателен']);
    exit;
}

// ---------------- MOCK-РЕЖИМ ----------------
if ($CONFIG['MOCK']) {
    // Если есть фикстура (fixtures/debug-mobile.html) — парсим её
    $fixture = __DIR__ . '/fixtures/debug-mobile.html';
    if (file_exists($fixture)) {
        $html = file_get_contents($fixture);
    } else {
        // Иначе берём минимальный HTML с тестовыми данными
        $html = '<html><body>
            <h1>Demo Car 2022</h1>
            <span>Первая регистрация</span><span>2022</span>
            <div>Объём двигателя: 2.0 л</div>
            <div>Пробег: 12 000 км</div>
            <div>100 000 € (Брутто)</div>
        </body></html>';
    }

    // Дальше простая выборка через регулярки
    preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $nameMatch);
    $name = trim(strip_tags($nameMatch[1] ?? ''));

    $year = '';
    $month = '01';
    $firstRegistration = '';
    if (preg_match('/Первая регистрация<\/span><span[^>]*>(\d{4})/iu', $html, $match)) {
        $year = $match[1];
        $firstRegistration = $year . '-' . $month;
    }

    $engine = '';
    if (preg_match('/([\d]{1,2}[.,]\d)\s*л/i', $html, $litreMatch)) {
        $engine = intval(floatval(str_replace(',', '.', $litreMatch[1])) * 1000);
    }

    preg_match('/(Пробег|Kilometerstand).*?([\d\s\.,]+)\s*(км|km)/iu', $html, $mileageMatch);
    $mileage = preg_replace('/\D/', '', $mileageMatch[2] ?? '');

    $price = '';
    if (preg_match('/([\d\s\.,]+)\s*€\s*\(Брутто\)/iu', $html, $priceMatch)) {
        $price = preg_replace('/[^\d]/', '', $priceMatch[1]);
    }

    // Топливо
    $fuel = '';
    if (preg_match('/(Топливо|Fuel)[:\s]*([а-яА-ЯA-Za-z\/\s]+)/ui', $html, $fuelMatch)) {
        $fuel = trim($fuelMatch[2]);
    }

    // Определение типа авто
    $fuelLower = mb_strtolower($fuel);
    $isElectric = false;
    $isHybrid = false;
    if (strpos($fuelLower, 'гибрид') !== false || strpos($fuelLower, 'hybrid') !== false) {
        $isHybrid = true;
    } elseif (strpos($fuelLower, 'электро') !== false || strpos($fuelLower, 'electric') !== false) {
        $isElectric = true;
    }

    echo json_encode([
        'name' => $name,
        'year' => $year,
        'month' => $month,
        'firstRegistration' => $firstRegistration,
        'engine' => $engine,
        'mileage' => $mileage,
        'price' => $price,
        'fuel' => $fuel,
        'isElectric' => $isElectric,
        'isHybrid' => $isHybrid,
        'mode' => 'mock'
    ]);
    exit;
}

// ---------------- РЕАЛЬНЫЙ РЕЖИМ ----------------
// В демо отключён. Включается только вручную через config.php.
echo json_encode(['error' => 'Реальный режим отключён в демо', 'mode' => 'real_disabled']);
