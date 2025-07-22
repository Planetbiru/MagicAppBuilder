<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename = 'template.html';
    $content = $_POST['content'];

    // Optional: sanitize filename or enforce specific folder
    $savePath = __DIR__ . '/' . $filename;

    if (!file_exists(dirname($savePath))) {
        mkdir(dirname($savePath), 0755, true);
    }

    if (file_put_contents($savePath, $content) !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'File saved',
            'filename' => $filename
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to write file'
        ]);
    }
    exit;
}
http_response_code(405);
echo json_encode(['error' => 'Invalid request']);
