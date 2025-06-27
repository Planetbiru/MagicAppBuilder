<?php

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once __DIR__ . '/AppUpdater.php';

$tag = isset($_GET['tag']) ? $_GET['tag'] : 'latest';

header('Content-Type: application/json');

try {
    $updater = new \AppUpdater('Planetbiru', 'MagicAppBuilder', $tag);
    $updater->downloadZip();

    echo json_encode([
        'success' => true,
        'message' => $appLanguage->getReleaseDownloadedSuccessfully()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $appLanguage->getDownloadFailed() . ': ' . $e->getMessage()
    ]);
}
