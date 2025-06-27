<?php
require_once __DIR__ . '/AppUpdater.php';

$tag = isset($_GET['tag']) ? $_GET['tag'] : 'latest';
header('Content-Type: application/json');
try {
    $updater = new AppUpdater('Planetbiru', 'MagicAppBuilder', $tag);
    $updater->downloadZip();
    echo json_encode([
        'success' => true,
        'message' => "Release $tag downloaded successfully."
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Download failed: " . $e->getMessage()
    ]);
}
