<?php

header('Content-Type: application/json');
$cacheTime = 86400; // 24 hours
header('Cache-Control: public, max-age=' . $cacheTime);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheTime) . ' GMT');

// Default to 'en' if no language is specified
$languageId = isset($_GET['lang']) ? $_GET['lang'] : 'en';

// Sanitize to prevent directory traversal, e.g., 'en-US' becomes 'en'
$arr = explode("-", $languageId);
$languageId = $arr[0];
$arr = explode("_", $languageId);
$languageId = $arr[0];

// Define the path to the language file
$filePath = __DIR__ . '/langs/i18n/' . $languageId . '.json';

if (!file_exists($filePath)) {
    $languageId = 'en'; // Fallback to default language
}

$filePath = __DIR__ . '/langs/i18n/' . $languageId . '.json';

if (!file_exists($filePath)) {
    // Fallback to English if the requested language file does not exist
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