<?php

header('Content-Type: application/json');

$languageId = isset($_GET['lang']) ? $_GET['lang'] : 'en';

$arr = explode("_", $languageId);
$languageId = $arr[0];

$filePath = __DIR__ . '/langs/entity/' . $languageId . '.json';

if (!file_exists($filePath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Language file not found.']);
    exit;
}
header('Content-size: '.filesize($filePath));
echo file_get_contents($filePath);
exit();