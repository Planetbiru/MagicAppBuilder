<?php

header('Content-Type: application/json');
$cacheTime = 86400; // 24 hours
header('Cache-Control: public, max-age=' . $cacheTime);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheTime) . ' GMT');

readfile(__DIR__ . '/langs/available-language.json');