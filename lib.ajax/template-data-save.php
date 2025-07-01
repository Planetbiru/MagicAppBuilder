<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['content'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$filename = 'template-data.json';
$content = $data['content'];
$savePath = __DIR__ . '/'. $filename;

if (!file_exists(dirname($savePath))) {
    mkdir(dirname($savePath), 0755, true);
}

if (file_put_contents($savePath, $content) !== false) {
    echo json_encode(['success' => true, 'filename' => $filename]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save']);
}
