<?php

require_once dirname(__DIR__) . "/inc.app/auth.php";

// Polyfill for PHP < 8.0
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}

$baseDirectory = realpath(dirname(__DIR__) . '/tmp/'); // Ensure real path
if ($baseDirectory === false) {
    http_response_code(500);
    exit;
}

$fileName = isset($_GET['fileName']) ? $_GET['fileName'] : '';

// Sanitize: remove unwanted characters (anything not alphanumeric, dash, underscore, or dot)
$fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);

// Resolve final path
$filePath = realpath($baseDirectory . DIRECTORY_SEPARATOR . basename($fileName));

// Validate the resolved path is within the base directory
if ($filePath !== false && str_starts_with($filePath, $baseDirectory) && is_file($filePath)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    unlink($filePath); // Optional: delete after download
    exit;
} else {
    http_response_code(404);
    echo 'File not found or invalid path.';
}
