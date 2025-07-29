<?php

require_once dirname(__DIR__) . "/inc.app/auth.php";

$baseDirectory = realpath(dirname(__DIR__) . '/.tmp'); 

$exportFileMaxAge = $builderConfig->getExportFileMaxAge();

if(!isset($exportFileMaxAge) || $exportFileMaxAge == 0)
{
    $exportFileMaxAge = 21600;
}
else
{
    $exportFileMaxAge = intval($exportFileMaxAge);
}

$maxAge = time() - $exportFileMaxAge; 

if (!is_dir($baseDirectory)) {
    exit;
}

$files = scandir($baseDirectory);

foreach ($files as $file) {
    $filePath = $baseDirectory . DIRECTORY_SEPARATOR . $file;

    if (is_file($filePath)) {
        $fileMTime = filemtime($filePath);

        if ($fileMTime !== false && $fileMTime < $maxAge) {
            unlink($filePath); 
        }
    }
}
