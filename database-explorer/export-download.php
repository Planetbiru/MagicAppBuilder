<?php

$baseDirectory = dirname(__DIR__) . '/tmp/';

$fileName = isset($_GET['fileName']) ? $_GET['fileName'] : '';

$fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName); 
$fileName = basename($fileName); 
$filePath = realpath($baseDirectory . '/' . $fileName);

if ($filePath && strpos($filePath, realpath($baseDirectory)) === 0 && file_exists($filePath)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    unlink($filePath); 
    exit;
} else {
    http_response_code(404);
    echo 'File not found or invalid.';
    exit;
}
