<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicObject\Util\Parsedown;

require_once dirname(__DIR__) . "/inc.app/auth.php";

/**
 * Gets an application entity by its ID.
 *
 * @param mixed $databaseBuilder The database builder.
 * @param string $applicationId The ID of the application.
 * @return EntityApplication An instance of EntityApplication.
 */
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
 * Adds a directory and all its contents to a ZIP archive.
 *
 * @param ZipArchive $zip The ZipArchive instance.
 * @param string $sourcePath The path to the directory to add.
 * @param string $zipPath The path inside the ZIP archive where the directory will be placed.
 * @param callable|null $callback
 */
function addDirectoryToZip($zip, $sourcePath, $zipPath, $callback = null) {
    if (is_dir($sourcePath)) {
        $zip->addEmptyDir($zipPath);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = ltrim($zipPath . '/' . substr($filePath, strlen($sourcePath) + 1), '/');
            if($file->isDir())
            {
                $zip->addEmptyDir($relativePath);
            }
            else
            {
                if(is_callable($callback))
                {
                    call_user_func($callback, $zip, $filePath, $relativePath);
                }
                else
                {
                    $zip->addFile($filePath, $relativePath);
                }
            }
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

/**
 * Creates a map of reserved columns from a given array.
 *
 * This function transforms an array of reserved column definitions into an
 * associative array where the key is the column's logical key and the value
 * is the actual column name.
 * @param array|null $reservedColumns An array of reserved column definitions.
 * @return array A map of reserved column keys to their names.
 */
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

/**
 * Retrieve a list of columns that are automatically handled by the backend.
 *
 * This function checks a mapping of reserved column names and returns
 * a normalized array describing which fields should be populated by
 * backend logic (such as timestamps, admin identifiers, or IP addresses).
 *
 * Each returned item contains:
 * - `columnName`: The actual database column name from the reserved map.
 * - `type`: The expected data type used by the backend to populate the value.
 *
 * @param array $reservedColumnMap An associative array containing reserved column names
 *                                 (e.g., time_create, admin_edit, ip_create).
 *
 * @return array An associative array where keys represent logical backend fields
 *               (e.g., timeCreate, adminEdit), and values contain metadata such as
 *               the actual column name and expected data type.
 */
function getBackendHandledColumns($reservedColumnMap)
{
    $backendHandledColumns = [];
    if (isset($reservedColumnMap['time_create'])) {
        $backendHandledColumns['timeCreate'] = ['columnName' => $reservedColumnMap['time_create'], 'type' => 'timestamp'];
    }
    if (isset($reservedColumnMap['time_edit'])) {
        $backendHandledColumns['timeEdit'] = ['columnName' => $reservedColumnMap['time_edit'], 'type' => 'timestamp'];
    }
    if (isset($reservedColumnMap['admin_create'])) {
        $backendHandledColumns['adminCreate'] = ['columnName' => $reservedColumnMap['admin_create'], 'type' => 'string'];
    }
    if (isset($reservedColumnMap['admin_edit'])) {
        $backendHandledColumns['adminEdit'] = ['columnName' => $reservedColumnMap['admin_edit'], 'type' => 'string'];
    }
    if (isset($reservedColumnMap['ip_create'])) {
        $backendHandledColumns['ipCreate'] = ['columnName' => $reservedColumnMap['ip_create'], 'type' => 'string'];
    }
    if (isset($reservedColumnMap['ip_edit'])) {
        $backendHandledColumns['ipEdit'] = ['columnName' => $reservedColumnMap['ip_edit'], 'type' => 'string'];
    }
    return $backendHandledColumns;
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

if($language === 'php' && file_exists(__DIR__ . "/graphql-generator-php.php")) {
    require_once __DIR__ . "/graphql-generator-php.php";
    exit;
} else if($language === 'java' && file_exists(__DIR__ . "/graphql-generator-java.php")) {
    require_once __DIR__ . "/graphql-generator-java.php";
    exit;
} else if($language === 'nodejs' && file_exists(__DIR__ . "/graphql-generator-nodejs.php")) {
    require_once __DIR__ . "/graphql-generator-nodejs.php";
    exit;
} else if($language === 'python' && file_exists(__DIR__ . "/graphql-generator-python.php")) {
    require_once __DIR__ . "/graphql-generator-python.php";
    exit;
} else if($language === 'kotlin' && file_exists(__DIR__ . "/graphql-generator-kotlin.php")) {
    require_once __DIR__ . "/graphql-generator-kotlin.php";
    exit;
} else if($language === 'go' && file_exists(__DIR__ . "/graphql-generator-go.php")) {
    require_once __DIR__ . "/graphql-generator-go.php";
    exit;
} else {
    http_response_code(400);
    echo json_encode(["error" => "Unsupported programming language"]);
    exit;
}