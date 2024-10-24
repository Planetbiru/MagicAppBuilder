<?php

use MagicObject\Util\PicoDateTimeUtil;

require_once dirname(__DIR__) . "/vendor/autoload.php";


// Contoh penggunaan
$dateStrings = [
    '2024-10-24',
    '2024-10-24 15:30:00',
    '2024-10-24T15:30:40',
    'Thu, 24 Oct 2024 15:30:00 +0800',
    '24/10/2024',
    '24 October 2024',
    'Thursday, 24 October 2024'
];

foreach ($dateStrings as $dateString) {
    $parsedDate = PicoDateTimeUtil::parseDateTime($dateString);
    if ($parsedDate) {
        echo "Parsed: " . $parsedDate->format('Y-m-d H:i:s') . " => from $dateString\n";
    } else {
        echo "Could not parse: $dateString\n";
    }
}