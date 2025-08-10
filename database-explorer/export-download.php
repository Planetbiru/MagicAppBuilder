<?php

require_once dirname(__DIR__) . "/inc.app/auth-core.php";
if(!$userLoggedIn)
{
    exit();
}
function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}
$tmpDir = dirname(__DIR__) . '/.tmp';
if(!file_exists($tmpDir))
{
    mkdir($tmpDir, 0755, true);
}
else
{
    chmod($tmpDir, 0755);
}
$baseDirectory = realpath($tmpDir); // Ensure real path
if ($baseDirectory === false) {
    http_response_code(500);
    exit;
}

$fileName = isset($_GET['fileName']) ? $_GET['fileName'] : '';
$downloadName = isset($_GET['downloadName']) ? $_GET['downloadName'] : '';

// Sanitize: remove unwanted characters (anything not alphanumeric, dash, underscore, or dot)
$fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);
if(empty($downloadName))
{
    $downloadName = $fileName;
}

// Resolve final path
$filePath = realpath($baseDirectory . DIRECTORY_SEPARATOR . basename($fileName));

// Validate the resolved path is within the base directory
if ($filePath !== false && startsWith($filePath, $baseDirectory) && is_file($filePath)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    unlink($filePath); // Optional: delete after download
    exit;
} else {
    http_response_code(404);
    echo 'File not found or invalid path.';
}
