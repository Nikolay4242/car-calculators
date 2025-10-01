<?php
// parser-proxy.php 
// По умолчанию возвращает заглушку с тестовыми данными.
// Если в config.php включить 'MOCK' => false и указать VPS_API_URL,
// то можно проксировать запросы на реальный сервер.

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Метод не поддерживается"]);
    exit;
}

$projectRoot = __DIR__ . '/configs';
$configFile = $projectRoot . '/config.php';
$CONFIG = ['MOCK' => true, 'VPS_API_URL' => ''];

if (file_exists($configFile)) {
    $cfg = require $configFile;
    if (is_array($cfg)) $CONFIG = array_merge($CONFIG, $cfg);
}

$rawInput = file_get_contents("php://input");
$input = json_decode($rawInput, true) ?: [];
$link = trim($input['link'] ?? '');

if (!$link) {
    echo json_encode(["error" => "link обязателен"]);
    exit;
}

// ---------------- MOCK-РЕЖИМ ----------------
if ($CONFIG['MOCK']) {
    echo json_encode([
        "name" => "Mock Car from Proxy",
        "year" => "2023",
        "price" => 29999,
        "engine" => 2000,
        "fuel" => "Petrol",
        "mileage" => 12000,
        "isElectric" => false,
        "isHybrid" => false,
        "source" => $link,
        "mode" => "mock"
    ]);
    exit;
}

// ---------------- РЕАЛЬНЫЙ РЕЖИМ ----------------
if (!$CONFIG['VPS_API_URL']) {
    echo json_encode(["error" => "VPS_API_URL не задан"]);
    exit;
}

$payload = json_encode(["link" => $link, "ts" => time()]);
$ch = curl_init($CONFIG['VPS_API_URL']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["error" => "Ошибка cURL: " . curl_error($ch)]);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($httpCode ?: 200);
echo $response;
