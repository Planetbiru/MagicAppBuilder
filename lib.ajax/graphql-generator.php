<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicObject\Util\Parsedown;

require_once dirname(__DIR__) . "/inc.app/auth.php";

function getApplication($databaseBuilder, $applicationId)
{
    $application = new EntityApplication(null, $databaseBuilder);
    try {
        $application->find($applicationId);
    } catch (Exception $e) {
        // Do nothing
    }
    return $application;
}

/**
 * Generates an HTML manual from Markdown content.
 *
 * @param string $manualMd The Markdown content of the manual.
 * @param string $appName The name of the application.
 * @return string The generated HTML content.
 */
function generateManualHtml($manualMd, $appName)
{
    $parsedown = new Parsedown();
    $manualBody = $parsedown->text($manualMd);

    // Generate Table of Contents
    $toc = '';
    $headings = [];
    $slugs = [];

    // Add IDs to headings and extract them for the TOC
    $manualBody = preg_replace_callback('/<h([2-4])>(.*?)<\/h\1>/i', function ($matches) use (&$headings, &$slugs) {
        $level = (int) $matches[1];
        $text = $matches[2];
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim(strip_tags($text))));

        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (isset($slugs[$slug])) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $slugs[$slug] = true;

        $headings[] = ['level' => $level, 'text' => $text, 'slug' => $slug];
        return "<h$level id=\"$slug\">$text</h$level>";
    }, $manualBody);

    // Build the TOC HTML from the extracted headings
    if (!empty($headings)) {
        $toc .= "<div id=\"toc-container\"><h2>Table of Contents</h2>\n<ul class=\"toc\">\n";
        $lastLevel = 1;
        $openLevels = 0;
        foreach ($headings as $heading) {
            $level = $heading['level'];
            if ($level > $lastLevel) {
                $toc .= "<ul>\n";
                $openLevels++;
            } else if ($level < $lastLevel) {
                $toc .= str_repeat("</li></ul>\n", $lastLevel - $level);
                $openLevels -= ($lastLevel - $level);
            }
            $toc .= "<li><a href=\"#{$heading['slug']}\">{$heading['text']}</a>";
            $lastLevel = $level;
        }
        $toc .= str_repeat("</li></ul>\n", $openLevels);
        $toc .= "</ul></div>\n";
    }

    // Inject the TOC after the first H1 tag
    $manualBody = preg_replace('/(<\/h1>)/', '$1' . $toc, $manualBody, 1);

    // Add copy button to code blocks
    $manualBody = preg_replace('/<pre><code( class="language-(.*?)")?>/i', '<div class="code-container"><button class="copy-btn" title="Copy to clipboard">Copy</button><pre><code$1>', $manualBody);
    $manualBody = preg_replace('/<\/code><\/pre>/i', '</code></pre></div>', $manualBody);

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GraphQL API Manual for $appName</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f8f9fa; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1, h2, h3, h4 { margin-top: 24px; margin-bottom: 16px; font-weight: 600; line-height: 1.25; border-bottom: 1px solid #eee; padding-bottom: 0.3em; }
        h1 { margin-top: 0px; font-size: 2em; } h2 { font-size: 1.5em; } h3 { font-size: 1.25em; }
        code { font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace; font-size: 85%; padding: 0.2em 0.4em; margin: 0; background-color: rgba(27,31,35,0.05); border-radius: 3px; }
        pre { padding: 16px; overflow: auto; font-size: 85%; line-height: 1.45; background-color: #f6f8fa; border-radius: 6px; margin-top:0; margin-bottom:0; }
        pre code { display: inline; padding: 0; margin: 0; background-color: transparent; border: 0; }
        .code-container { position: relative; margin-top: 16px; margin-bottom: 16px; }
        .copy-btn { position: absolute; top: 8px; right: 8px; background-color: #e1e4e8; border: 1px solid #d1d5da; border-radius: 4px; padding: 4px 8px; font-size: 12px; cursor: pointer; opacity: 0.7; transition: opacity 0.2s; }
        .copy-btn:hover { opacity: 1; background-color: #d1d5da; }
        .copy-btn:active { background-color: #c6cbd1; }
        .copy-btn.copied { background-color: #28a745; color: white; border-color: #28a745; }
        #toc-container { background-color: #f6f8fa; border: 1px solid #e1e4e8; border-radius: 6px; padding: 15px 20px; margin-bottom: 20px; }
        ul.toc { margin-left: 0px; padding-left: 0px; }
        .toc ul { padding-left: 20px; list-style-type: disc; }
        .toc a { text-decoration: none; color: #0366d6; }
        .toc a:hover { text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin: 1em 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: 600; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        .back-to-toc { position: fixed; bottom: 20px; right: 20px; background-color: #0366d6; color: white; border: none; border-radius: 50%; width: 50px; height: 50px; font-size: 24px; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; text-decoration: none; z-index: 1000; }
        hr { height: 0; border: dotted #8a8a8a; border-width: 0px 0px 1px 0px; }
    </style>
</head>
<body>
    <div class="container">
        $manualBody
    </div>
    <a href="#toc-container" class="back-to-toc" title="Back to Table of Contents">&uarr;</a>
    <script>
        document.querySelectorAll('.copy-btn').forEach(button => {
            button.addEventListener('click', () => {
                const pre = button.nextElementSibling;
                const code = pre.querySelector('code');
                navigator.clipboard.writeText(code.innerText).then(() => {
                    button.textContent = 'Copied!';
                    button.classList.add('copied');
                    setTimeout(() => {
                        button.textContent = 'Copy';
                        button.classList.remove('copied');
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                });
            });
        });
    </script>
</body>
</html>
HTML;
}

/**
 * Adds a directory and all its contents to a ZIP archive.
 *
 * @param ZipArchive $zip The ZipArchive instance.
 * @param string $sourcePath The path to the directory to add.
 * @param string $zipPath The path inside the ZIP archive where the directory will be placed.
 */
function addDirectoryToZip($zip, $sourcePath, $zipPath) {
    if (is_dir($sourcePath)) {
        $zip->addEmptyDir($zipPath);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = $zipPath . '/' . substr($filePath, strlen($sourcePath) + 1);
            $file->isDir() ? $zip->addEmptyDir($relativePath) : $zip->addFile($filePath, $relativePath);
        }
    }
}

/**
 * Adds files with a specific prefix from a source directory to a ZIP archive.
 *
 * This function scans a directory for files that match a given prefix. If recursion is enabled,
 * it will traverse all subdirectories. Matching files are added to the provided ZipArchive object
 * under a specified path within the archive.
 *
 * @param ZipArchive $zip The ZipArchive instance to add files to.
 * @param string $sourcePath The source directory to search for files.
 * @param string $zipPath The path inside the ZIP archive where files will be placed.
 * @param string $prefix The prefix of the files to be added.
 */
function addFilesWithPrefixToZip($zip, $sourcePath, $zipPath, $prefix) { //NOSONAR
    if (!is_dir($sourcePath)) {
        return;
    }
    $pattern = $sourcePath . '/' . $prefix . '*';
    $files = glob($pattern);
    if ($files !== false) {
        foreach ($files as $file) {
            if (is_file($file)) {
                $finalZipPath = empty($zipPath) ? basename($file) : $zipPath . '/' . basename($file);
                $zip->addFile($file, $finalZipPath);
            }
        }
    }
}

function createReservedColumnMap($reservedColumns)
{
    $reservedColumnMap = array();
    if (isset($reservedColumns) && is_array($reservedColumns)) {
        foreach ($reservedColumns as $reservedColumn) {
            $key = $reservedColumn['key'];
            $reservedColumnMap[$key] = $reservedColumn['name'];
        }
    }
    return $reservedColumnMap;
}

$request = file_get_contents("php://input");
$data = json_decode($request, true);

$language = isset($data['programmingLanguage']) ? $data['programmingLanguage'] : 'php';

$withFrontend = isset($data['withFrontend']) && ($data['withFrontend'] == 'true' || $data['withFrontend'] == '1' || $data['withFrontend'] === true) ? true : false;
$schema = isset($data['schema']) ? $data['schema'] : [];
$reservedColumns = isset($data['reservedColumns']) ? $data['reservedColumns'] : [];
$applicationId = isset($data['applicationId']) ? $data['applicationId'] : null;
$inMemoryCache = isset($data['inMemoryCache']) && ($data['inMemoryCache'] == 'true' || $data['inMemoryCache'] == '1' || $data['inMemoryCache'] === true) ? true : false;
$verboseLogging = isset($data['verboseLogging']) && ($data['verboseLogging'] == 'true' || $data['verboseLogging'] == '1' || $data['verboseLogging'] === true) ? true : false;

if($language === 'java') {
    require_once __DIR__ . "/graphql-generator-java.php";
    exit;
} else if($language === 'php') {
    require_once __DIR__ . "/graphql-generator-php.php";
    exit;
} else if($language === 'nodejs') {
    require_once __DIR__ . "/graphql-generator-nodejs.php";
    exit;
} else if($language === 'python') {
    require_once __DIR__ . "/graphql-generator-python.php";
    exit;
} else if($language === 'kotlin') {
    require_once __DIR__ . "/graphql-generator-kotlin.php";
    exit;
} else {
    http_response_code(400);
    echo json_encode(["error" => "Unsupported programming language"]);
    exit;
}