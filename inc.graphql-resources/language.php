<?php

header('Content-Type: application/json');
$cacheTime = 86400; // 24 jam
header('Cache-Control: public, max-age=' . $cacheTime);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheTime) . ' GMT');

// Default ke 'en' jika tidak ada bahasa yang ditentukan
$languageId = isset($_GET['lang']) ? $_GET['lang'] : 'en';

// Sanitasi untuk mencegah directory traversal, misal 'en-US' menjadi 'en'
$arr = explode("-", $languageId);
$languageId = $arr[0];
$arr = explode("_", $languageId);
$languageId = $arr[0];

// Tentukan path ke file bahasa
$filePath = __DIR__ . '/langs/i18n/' . $languageId . '.json';

if (!file_exists($filePath)) {
    $languageId = 'en'; // Fallback to default language
}

$filePath = __DIR__ . '/langs/i18n/' . $languageId . '.json';

if (!file_exists($filePath)) {
    // Fallback ke bahasa Inggris jika file bahasa yang diminta tidak ada
    $filePath = __DIR__ . '/langs/i18n/en.json';
    if(!file_exists($filePath)) {
        http_response_code(404);
        echo json_encode(['error' => 'Default language file (en.json) not found.']);
        exit;
    }
}

header('Content-size: '.filesize($filePath));
echo file_get_contents($filePath);
exit();