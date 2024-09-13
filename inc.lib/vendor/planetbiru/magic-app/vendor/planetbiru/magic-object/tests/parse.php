<?php
$fieldRaw = "upper(lower(sha1(md5(lower(table2.field)))))";
$pattern = '/(\((?:\(??[^\(]*?\)))/m';
preg_match_all($pattern , $fieldRaw, $out);
$field = trim($out[0][0], "()");

print_r($field);
