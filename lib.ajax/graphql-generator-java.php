<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\GraphQLGeneratorJava;
use MagicObject\SecretObject;
use MagicObject\Util\Parsedown;

require_once dirname(__DIR__) . "/inc.app/auth.php";

function getApplication($databaseBuilder, $applicationId)
{
    $application = new EntityApplication(null, $databaseBuilder);
    try
    {
        $application->find($applicationId);
    }
    catch(Exception $e)
    {
        // Do nothing
    }
    return $application;
}

function setDatabaseConfiguration($application, $databaseConfiguration)
{
    $appConfig = new SecretObject(null);

    // Get from database
    $projectDirectory = $application->getProjectDirectory();

    $yml = $projectDirectory . "/default.yml";

    if(file_exists($yml))
    {
        $appConfig->loadYamlFile($yml, false, true, true);
        $databaseConfig = $appConfig->getDatabase();
        if($databaseConfig != null)
        {
            $driver = $databaseConfig->getDriver();
            $host = $databaseConfig->getHost();
            $port = $databaseConfig->getPort();
            $dbName = $databaseConfig->getDatabaseName();
            $dbFile = $databaseConfig->getDatabaseFilePath();

            $url = '';
            $driverClass = '';
            $dialect = '';

            if ($driver == 'mysql' || $driver == 'mariadb') {
                $url = "jdbc:mysql://$host:$port/$dbName";
                $driverClass = 'com.mysql.cj.jdbc.Driver';
                $dialect = 'org.hibernate.dialect.MySQLDialect';
            } else if ($driver == 'pgsql') {
                $url = "jdbc:postgresql://$host:$port/$dbName";
                $driverClass = 'org.postgresql.Driver';
                $dialect = 'org.hibernate.dialect.PostgreSQLDialect';
            } else if ($driver == 'sqlite') {
                $url = "jdbc:sqlite:" . $dbFile;
                $driverClass = 'org.sqlite.JDBC';
                $dialect = 'org.hibernate.dialect.SQLiteDialect';
            } else if ($driver == 'sqlsrv') {
                $url = "jdbc:sqlserver://$host:$port;databaseName=$dbName;encrypt=false";
                $driverClass = 'com.microsoft.sqlserver.jdbc.SQLServerDriver';
                $dialect = 'org.hibernate.dialect.SQLServerDialect';
            }
            
            // In pom.xml, only mysql-connector-j is included by default.
            // For other databases, the user will need to add the corresponding dependency to pom.xml.
            // For example, for PostgreSQL:
            // <dependency>
            //     <groupId>org.postgresql</groupId>
            //     <artifactId>postgresql</artifactId>
            //     <scope>runtime</scope>
            // </dependency>

            $databaseConfiguration = str_replace('{DB_URL}', $url, $databaseConfiguration);
            $databaseConfiguration = str_replace('{DB_DRIVER_CLASS}', $driverClass, $databaseConfiguration);
            $databaseConfiguration = str_replace('{DB_DIALECT}', $dialect, $databaseConfiguration);

            $databaseConfiguration = str_replace('{DB_USER}', str_replace("'", "\\'", $databaseConfig->getUsername()), $databaseConfiguration);
            $databaseConfiguration = str_replace('{DB_PASS}', str_replace("'", "\\'", $databaseConfig->getPassword()), $databaseConfiguration);
        }
    }
    return $databaseConfiguration;
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

function createReservedColumnMap($reservedColumns) {
    $reservedColumnMap = array();
    foreach ($reservedColumns as $reservedColumn) {
        $key = $reservedColumn['key'];
        $reservedColumnMap[$key] = $reservedColumn['name'];
    }
    return $reservedColumnMap;
}

$request = file_get_contents('php://input');
$data = json_decode($request, true);

$withFrontend = isset($data['withFrontend']) && ($data['withFrontend'] == 'true' || $data['withFrontend'] == '1' || $data['withFrontend'] === true) ? true : false;
$schema = isset($data['schema']) ? $data['schema'] : [];
$reservedColumns = isset($data['reservedColumns']) ? $data['reservedColumns'] : [];
$applicationId = isset($data['applicationId']) ? $data['applicationId'] : null;
$inMemoryCache = isset($data['inMemoryCache']) && ($data['inMemoryCache'] == 'true' || $data['inMemoryCache'] == '1' || $data['inMemoryCache'] === true) ? true : false;

$verboseLogging = isset($data['verboseLogging']) && ($data['verboseLogging'] == 'true' || $data['verboseLogging'] == '1' || $data['verboseLogging'] === true) ? true : false;

$reservedColumnMap = createReservedColumnMap($reservedColumns['columns']);

$groupId = $builderConfig->issetGroupId() ? $builderConfig->getGroupId() : 'com.planetbiru';

$backendHandledColumns = [];

if(isset($reservedColumnMap['time_create']))
{
    $backendHandledColumns['timeCreate'] = [
        'columnName' => $reservedColumnMap['time_create'],
        'type' => 'datetime'
    ];
}

if(isset($reservedColumnMap['time_edit']))
{
    $backendHandledColumns['timeEdit'] = [
        'columnName' => $reservedColumnMap['time_edit'],
        'type' => 'datetime'
    ];
}

if(isset($reservedColumnMap['admin_create']))
{
    $backendHandledColumns['adminCreate'] = [
        'columnName' => $reservedColumnMap['admin_create'],
        'type' => 'string'
    ];
}

if(isset($reservedColumnMap['admin_edit']))
{
    $backendHandledColumns['adminEdit'] = [
        'columnName' => $reservedColumnMap['admin_edit'],
        'type' => 'string'
    ];
}

if(isset($reservedColumnMap['ip_create']))
{
    $backendHandledColumns['ipCreate'] = [
        'columnName' => $reservedColumnMap['ip_create'],
        'type' => 'string'
    ];
}

if(isset($reservedColumnMap['ip_edit']))
{
    $backendHandledColumns['ipEdit'] = [
        'columnName' => $reservedColumnMap['ip_edit'],
        'type' => 'string'
    ];
}

try {
    /** @var \MagicObject\Database\PicoDatabase $databaseBuilder */
    $app = getApplication($databaseBuilder, $applicationId);

    $generator = new GraphQLGeneratorJava(
        $schema, 
        $reservedColumns, 
        $backendHandledColumns, 
        $inMemoryCache,
        array(
            'groupId' => $groupId,
            'artifactId' => $app->getApplicationId(),
            'version' => '0.0.1-SNAPSHOT',
            'name' => $app->getName(),
            'description' => $app->getDescription(),
            'javaVersion' => '21',
            'packageName' => $groupId . '.' . str_replace('-', '', $app->getApplicationId()),
            'verboseLogging' => $verboseLogging
        ),
        $verboseLogging,
        false // requireLogin
    );

    // --- Create Backend ZIP ---
    $backendZip = new ZipArchive();
    $backendZipFilePath = tempnam(sys_get_temp_dir(), 'backend_');
    if ($backendZip->open($backendZipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        throw new Exception("Could not create backend ZIP file.");
    }

    $backendFiles = $generator->generate();

    // application.properties
    $applicationProperties = $generator->generateApplicationProperties();
    $applicationProperties = setDatabaseConfiguration($app, $applicationProperties);
    $backendFiles[] = ['name' => 'src/main/resources/application.properties', 'content' => $applicationProperties];


    foreach ($backendFiles as $file) {
        $backendZip->addFromString($file['name'], $file['content']);
    }

    // Add Maven Wrapper files to backend zip
    $backendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/backend/mvn/mvnw", 'mvnw');
    $backendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/backend/mvn/mvnw.cmd", 'mvnw.cmd');
    //$backendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/backend/mvn/.mvn/wrapper/maven-wrapper.jar", '.mvn/wrapper/maven-wrapper.jar');
    $backendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/backend/mvn/.mvn/wrapper/maven-wrapper.properties", '.mvn/wrapper/maven-wrapper.properties');
    
    $backendZip->close();

    // --- Create Frontend ZIP ---
    $frontendZip = new ZipArchive();
    $frontendZipFilePath = tempnam(sys_get_temp_dir(), 'frontend_');
    if ($frontendZip->open($frontendZipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        throw new Exception("Could not create frontend ZIP file.");
    }

    // Add generated frontend config files
    $frontendZip->addFromString('config/frontend-config.json', $generator->generateFrontendConfigJson());

    // Add language files
    $frontendZip->addFromString('langs/available-language.json', file_get_contents(dirname(__DIR__) . "/inc.graphql-resources/frontend/langs/available-language.json"));
    $entityLanguagePacks = $generator->generateFrontendLanguageJson();
    $frontendZip->addFromString('langs/entity/source.json', $entityLanguagePacks);
    $frontendZip->addFromString('langs/entity/en.json', $entityLanguagePacks); // Assume 'en' is default

    // Add i18n files
    $frontendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/frontend/langs/i18n/en.json", 'langs/i18n/source.json');
    $frontendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/frontend/langs/i18n/en.json", 'langs/i18n/en.json');
    $frontendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/frontend/langs/i18n/id.json", 'langs/i18n/id.json');

    $frontendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/frontend/assets/style.scss", 'assets/style.scss');
    $frontendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/frontend/assets/style.css", 'assets/style.css');
    $frontendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/frontend/assets/style.css.map", 'assets/style.css.map');
    $frontendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/frontend/assets/style.min.css", 'assets/style.min.css');
    $frontendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/frontend/assets/app-mvn.js", 'assets/app.js');
    $frontendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/frontend/assets/app-mvn.min.js", 'assets/app.min.js');
    $frontendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/frontend/assets/graphql.js", 'assets/graphql.js');
    $frontendZip->addFile(dirname(__DIR__) . "/inc.graphql-resources/frontend/assets/graphql.min.js", 'assets/graphql.min.js');

    // Add assets
    addDirectoryToZip($frontendZip, dirname(__DIR__) . "/inc.graphql-resources/frontend/assets", 'assets');
    
    // Add index.html
    $indexFileContent = file_get_contents(dirname(__DIR__) . "/inc.graphql-resources/frontend/index.html");
    $indexFileContent = str_replace('{APP_NAME}', $app->getName(), $indexFileContent);
    $frontendZip->addFromString('index.html', $indexFileContent);

    // Add icon files
    addFilesWithPrefixToZip($frontendZip, dirname(__DIR__) . "/inc.graphql-resources/frontend", '', 'icon-');
    addFilesWithPrefixToZip($frontendZip, dirname(__DIR__) . "/inc.graphql-resources/frontend", '', 'android-icon-');
    addFilesWithPrefixToZip($frontendZip, dirname(__DIR__) . "/inc.graphql-resources/frontend", '', 'apple-icon-');
    addFilesWithPrefixToZip($frontendZip, dirname(__DIR__) . "/inc.graphql-resources/frontend", '', 'favicon');

    $frontendZip->close();

    // --- Create Main ZIP ---
    $mainZip = new ZipArchive();
    $mainZipFilePath = tempnam(sys_get_temp_dir(), 'main_zip_');
    if ($mainZip->open($mainZipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        throw new Exception("Could not create main ZIP file.");
    }

    // Add backend.zip and frontend.zip to the main zip
    $mainZip->addFile($backendZipFilePath, 'backend.zip');
    $mainZip->addFile($frontendZipFilePath, 'frontend.zip');

    // Add documentation to the root of the main zip
    $manualMd = $generator->generateManual();
    $mainZip->addFromString('MANUAL.md', $manualMd);
    $appName = $app->getName();
    $manualHtml = generateManualHtml($manualMd, $appName);
    $mainZip->addFromString('manual.html', $manualHtml);

    $readmeContent = "# " . $appName . "\n\n" .
        "This is a Spring Boot GraphQL application generated by MagicAppBuilder.\n\n" .
        "This archive contains two separate zip files:\n" .
        "- `backend.zip`: The Spring Boot (Java) application.\n" .
        "- `frontend.zip`: The HTML, CSS, and JavaScript assets for the frontend.\n\n" .
        "## How to Run\n\n" .
        "### Backend\n\n" .
        "1.  Extract `backend.zip`.\n" .
        "1.  Ensure you have Java " . $generator->getProjectConfig()['javaVersion'] . " or later installed.\n" .
        "2.  Configure your database connection in `src/main/resources/application.properties`.\n" .
        "3.  Run the application using the Maven wrapper:\n\n" .
        "On Linux/macOS:\n" .
        "```bash\nchmod +x mvnw\n./mvnw spring-boot:run\n```\n\n" .
        "On Windows:\n" .
        "```bash\nmvnw.cmd spring-boot:run\n```\n\n" .
        "The GraphQL endpoint will be available at `http://localhost:8080/graphql` and the GraphiQL UI at `http://localhost:8080/graphiql`.\n\n" .
        "### Frontend\n\n" .
        "1.  Extract `frontend.zip`.\n" .
        "2.  Serve the files using any local web server (e.g., `python -m http.server`, `npx serve`, or Apache/Nginx).\n" .
        "3.  Open `index.html` in your browser.\n";

    $mainZip->addFromString('README.md', $readmeContent);
    $mainZip->close();

    // Send the ZIP file as a download
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $app->getApplicationId() . '-package.zip"');
    header('Content-Length: ' . filesize($mainZipFilePath));
    readfile($mainZipFilePath);

    // Delete the temporary file
    unlink($backendZipFilePath);
    unlink($frontendZipFilePath);
    unlink($mainZipFilePath);
    exit();

} catch (Exception $e) {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}