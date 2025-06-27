<?php

use MagicObject\Util\PicoIniUtil;

require_once __DIR__ . '/AppUpdater.php';
header('Content-Type: application/json');

try {
    $updater = new AppUpdater('Planetbiru', 'MagicAppBuilder');
    $updater->replaceFromZip();
    $updater->cleanUp();


    $date = (new DateTime())->setTimezone(new DateTimeZone('UTC'));
    $formatted = $date->format('Y-m-d\TH:i:s.u\Z');

    $iniPath = dirname(dirname(__DIR__))."/app.ini";
    $ini = PicoIniUtil::parseIniFile($iniPath);
    $ini['last_update'] = $formatted;
    PicoIniUtil::writeIniFile($ini, $iniPath);

    echo json_encode([
        'success' => true,
        'message' => 'Extraction and update completed.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Extraction failed: " . $e->getMessage()
    ]);
}
 