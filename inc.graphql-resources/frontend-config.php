<?php

require_once __DIR__ . '/auth.php';
header('Content-Type: application/json');
$filePath = __DIR__ . "/config/frontend-config.json"; // NOSONAR

if(file_exists($filePath))
{
    header('Content-size: '.filesize($filePath));
    echo file_get_contents($filePath);
}