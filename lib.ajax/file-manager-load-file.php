<?php

use AppBuilder\Util\FileDirUtil;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";

// Exit if the application is not set up
if ($appConfig->getApplication() == null) {
    exit();
}

$inputGet = new InputGet();

try {
    // Get the base directory of the active application
    $baseDirectory = rtrim($activeApplication->getBaseApplicationDirectory(), "/");

    // Construct the full path
    $file = FileDirUtil::normalizationPath($baseDirectory . "/" . $inputGet->getFile());

    if (!file_exists($file)) {
        http_response_code(404);
        exit("File not found");
    }

    // Detect content type by file extension
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimeTypes = [
        // Database files
        "sqlite" => "application/x-sqlite3",
        "db"     => "application/x-sqlite3",

        // Text-based formats
        "sql"    => "text/plain; charset=UTF-8",
        "json"   => "application/json; charset=UTF-8",
        "js"     => "application/javascript; charset=UTF-8",
        "css"    => "text/css; charset=UTF-8",
        "scss"   => "text/x-scss; charset=UTF-8",
        "html"   => "text/html; charset=UTF-8",
        "htm"    => "text/html; charset=UTF-8",
        "php"    => "text/x-php; charset=UTF-8",
        "txt"    => "text/plain; charset=UTF-8",
        "xml"    => "application/xml; charset=UTF-8",
        "md"     => "text/markdown; charset=UTF-8",
        "yml"    => "text/yaml; charset=UTF-8",
        "yaml"   => "text/yaml; charset=UTF-8",
        "ini"    => "text/plain; charset=UTF-8",
        "env"    => "text/plain; charset=UTF-8",
        "log"    => "text/plain; charset=UTF-8",
        "csv"    => "text/csv; charset=UTF-8",
        "tsv"    => "text/tab-separated-values; charset=UTF-8",
        "sh"     => "text/x-shellscript; charset=UTF-8",
        "bat"    => "text/x-msdos-batch; charset=UTF-8",

        // Added MIME types
        "pdf"    => "application/pdf",
        "xls"    => "application/vnd.ms-excel",
        "xlsx"   => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "ods"    => "application/vnd.oasis.opendocument.spreadsheet",
        "docx"   => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",

        // Font formats
        "ttf"    => "font/ttf",
        "otf"    => "font/otf",
        "woff"   => "font/woff",
        "woff2"  => "font/woff2",
        "eot"    => "application/vnd.ms-fontobject",
        "svg"    => "image/svg+xml",

        "mp3"  => "audio/mpeg",
        "wav"  => "audio/wav",
        "m4a"  => "audio/mp4",
        "flac" => "audio/flac",
        
        "mp4"  => "video/mp4",
        "ogg"  => "video/ogg",
        "webm" => "video/webm",
        "avi"  => "video/x-msvideo",
        "mov"  => "video/quicktime",
        "wmv"  => "video/x-ms-wmv",
        "flv"  => "video/x-flv",
        "mkv"  => "video/x-matroska",
        "3gp"  => "video/3gpp",
    ];

    $contentType = isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : "application/octet-stream";

    header("Content-Type: $contentType");
    header("Content-Length: " . filesize($file));
    readfile($file);
    exit();

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
}
