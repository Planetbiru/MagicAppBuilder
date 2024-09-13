<?php

$arr = hash_algos();

foreach($arr as $algorithm)
{
    $string = strtoupper(preg_replace("/[^A-Za-z0-9 ]/", "_", $algorithm));
    $string2 = sprintf("ALG_%-12s", $string);
    echo "conts $string2 = \"$algorithm\";\r\n";
}