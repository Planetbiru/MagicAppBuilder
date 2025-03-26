<?php
header('Content-Type: application/json');

$composerJson = dirname(__DIR__) . "/inc.lib/composer.json";
$targetDir = dirname($composerJson);
$phpPath = "php";

$cmd = "cd $targetDir"."&&"."$phpPath composer.phar remove planetbiru/magic-app&&$phpPath composer.phar require planetbiru/magic-app&&$phpPath composer.phar update --ignore-platform-reqs";

exec($cmd . " 2>&1", $output, $returnVar);

echo json_encode([
    'success' => $returnVar === 0,
    'message' => implode("\n", $output),
    'exitCode' => $returnVar
]);