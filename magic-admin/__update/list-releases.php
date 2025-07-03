<?php

require_once __DIR__ . '/AppUpdater.php';

try {
    $updater = new \AppUpdater('Planetbiru', 'MagicAppBuilder');
    header('Content-Type: application/json');
    echo json_encode($updater->listReleases());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
