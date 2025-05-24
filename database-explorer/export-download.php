<?php
$fileName = $_GET['fileName'];

$baseDirectory = dirname(__DIR__) . '/tmp/';

$fileName = str_replace("..\\", "", $fileName);
$fileName = str_replace("../", "", $fileName);

$filePath = $baseDirectory . basename($fileName);
if (file_exists($filePath)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
    readfile($filePath);
    unlink($filePath);
    exit;
} else {
    http_response_code(404);
    echo 'File not found.';
}
