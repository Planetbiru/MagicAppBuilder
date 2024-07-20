<?php

use MagicApp\Field;
use MagicApp\Menu\AccountMenu;

require_once dirname(__DIR__) . "/vendor/autoload.php";
$seperator = ",";
$line = '"%s - %s", nama, jabatan.nama, "coba", \'lagi\' ';
$arr = preg_split('/'.$seperator.'(?=(?:[^\"])*(?![^\"]))/', $line,-1, PREG_SPLIT_DELIM_CAPTURE);

print_r($arr);
preg_match_all('`"([^"]*)"`', $arr[0], $results);
print_r($results);
$format = isset($results[1]) && isset($results[1][0]) && !empty($results[1][0]) ? $results[1][0] : $arr[0];
echo "FORMAT = [$format] ";

$string = "There are %d monkeys in the %s %s %s%s";
$numPlaceholders = preg_match_all('/%[sd]/', $string, $matches);

echo "Number of %s and %d placeholders: " . $numPlaceholders;