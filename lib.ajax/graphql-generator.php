<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\GraphQLGenerator;
use MagicObject\SecretObject;

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
            /*
            Placeholder to replace
            $cfgDbDriver         = '{DB_DRIVER}';
            $cfgDbHost           = '{DB_HOST}';
            $cfgDbDatabaseName   = '{DB_NAME}';
            $cfgDbDatabaseSchema = '{DB_NAME}';
            $cfgDbDatabaseFile   = '{DB_FILE}';
            $cfgDbUser           = '{DB_USER}';
            $cfgDbPass           = '{DB_PASS}';
            $cfgDbCharset        = '{DB_CHARSET}';
            $cfgDbPort           = '{DB_PORT}';
            $cfgDbTimeZone       = '{DB_TIMEZONE}';
            */
            /*
            Yaml file
database:
    driver: sqlite
    databaseFilePath: D:/xampp/htdocs/graphql-application/inc.database/database.sqlite
    host: ""
    port: 0
    username: ""
    password: ""
    databaseName: ""
    databaseSchema: ""
    timeZone: Asia/Jakarta
    timeZoneSystem: Asia/Jakarta
    connectionTimeout: 10
            */
            $databaseConfiguration = str_replace('{DB_DRIVER}', str_replace("'", "\\'", $databaseConfig->getDriver()), $databaseConfiguration);
            $databaseConfiguration = str_replace('{DB_HOST}', str_replace("'", "\\'", $databaseConfig->getHost()), $databaseConfiguration);
            $databaseConfiguration = str_replace('{DB_NAME}', str_replace("'", "\\'", $databaseConfig->getDatabaseName()), $databaseConfiguration);
            $databaseConfiguration = str_replace('{DB_FILE}', str_replace("'", "\\'", $databaseConfig->getDatabaseFilePath()), $databaseConfiguration);
            $databaseConfiguration = str_replace('{DB_USER}', str_replace("'", "\\'", $databaseConfig->getUsername()), $databaseConfiguration);
            $databaseConfiguration = str_replace('{DB_PASS}', str_replace("'", "\\'", $databaseConfig->getPassword()), $databaseConfiguration);
            $databaseConfiguration = str_replace('{DB_CHARSET}', str_replace("'", "\\'", $databaseConfig->getCharset()), $databaseConfiguration);
            $databaseConfiguration = str_replace('{DB_PORT}', str_replace("'", "\\'", $databaseConfig->getPort()), $databaseConfiguration);
            $databaseConfiguration = str_replace('{DB_TIMEZONE}', str_replace("'", "\\'", $databaseConfig->getTimeZone()), $databaseConfiguration);

        }
    }
    return $databaseConfiguration;
}

$request = file_get_contents('php://input');
$data = json_decode($request, true);

$withFrontend = isset($data['withFrontend']) && ($data['withFrontend'] == 'true' || $data['withFrontend'] == '1' || $data['withFrontend'] === true) ? true : false;
$schema = isset($data['schema']) ? $data['schema'] : [];
$reservedColumns = isset($data['reservedColumns']) ? $data['reservedColumns'] : [];
$applicationId = isset($data['applicationId']) ? $data['applicationId'] : null;

try {
    $application = getApplication($databaseBuilder, $applicationId);

    if($withFrontend)
    {
        $generator = new GraphQLGenerator($schema, $reservedColumns);

        // Create ZIP file
        $zip = new ZipArchive();
        // Create a temporary file for the ZIP
        $zipFilePath = tempnam(sys_get_temp_dir(), 'graphql_');
        if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Could not create ZIP file.");
        }

        // Add generated code file
        $zip->addFromString('graphql.php', $generator->generate());
        // Add manual content file
        $zip->addFromString('manual.md', $generator->generateManual());
        
        // Add frontend config files
        $zip->addFromString('config/frontend-config.json', $generator->generateFrontendConfigJson());

        // Add entity language files

        // Add language file list
        $zip->addFromString('langs/available-language.json', file_get_contents(dirname(__DIR__) . "/inc.graphql-resources/langs/available-language.json"));


        $entityLanguagePacks = $generator->generateFrontendLanguageJson();
        $zip->addFromString('langs/entity/source.json', $entityLanguagePacks);

        // Assumpt that default language is `en`
        $zip->addFromString('langs/entity/en.json', $entityLanguagePacks);


        $languagePacksPath = dirname(__DIR__) . "/inc.graphql-resources/langs/i18n/en.json";
        $zip->addFile($languagePacksPath, 'langs/i18n/source.json');

        // Assumpt that default language is `en`
        $zip->addFile($languagePacksPath, 'langs/i18n/en.json');

        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/langs/i18n/id.json", 'langs/i18n/id.json');
        
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/assets/style.scss", 'assets/style.scss');
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/assets/style.css", 'assets/style.css');
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/assets/style.css.map", 'assets/style.css.map');
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/assets/style.min.css", 'assets-style.min.css');
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/assets/app.js", 'assets/app.js');
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/assets/app.min.js", 'assets/app.min.js');
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/assets/graphql.js", 'assets/graphql.js');
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/assets/graphql.min.js", 'assets/graphql.min.js');

        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/favicon.svg", 'favicon.svg');
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/inc/I18n.php", 'inc/I18n.php');

        

        // Replace application name
        // <title>{APP_NAME}</title>
        $indexFileContent = file_get_contents(dirname(__DIR__) . "/inc.graphql-resources/index.html");
        $indexFileContent = str_replace('{APP_NAME}', $application->getName(), $indexFileContent);

        $zip->addFromString('index.php', $indexFileContent);
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/language.php", 'language.php');
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/entity-language.php", 'entity-language.php');
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/available-language.php", 'available-language.php');

        $databaseConfiguration = file_get_contents(dirname(__DIR__) . "/inc.graphql-resources/database.php");

        // Replace database configuration placeholders with actual values
        $databaseConfiguration = setDatabaseConfiguration($application, $databaseConfiguration);

        $zip->addFromString('database.php', $databaseConfiguration);
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/auth.php", 'auth.php');
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/login.php", 'login.php');
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/logout.php", 'logout.php');
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/frontend-config.php", 'frontend-config.php');

        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/message.php", "message.php");
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/notification.php", "notification.php");
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/user-profile.php", "user-profile.php");
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/user-profile-update.php", "user-profile-update.php");
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/settings.php", "settings.php");
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/settings-update.php", "settings-update.php");
        $zip->addFile(dirname(__DIR__) . "/inc.graphql-resources/update-password.php", "update-password.php");

        
        // Bonus
        $zip->addFile(dirname(__DIR__) . "/inc.lib/composer.phar", "composer.phar");

        $zip->close();
        // Send the ZIP file as a download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="graphql.zip"');
        header('Content-Length: ' . filesize($zipFilePath));
        readfile($zipFilePath);
        // Delete the temporary file
        unlink($zipFilePath);
        exit();
    }
    else
    {
        $generator = new GraphQLGenerator($schema, $reservedColumns);
        $generatedCode = $generator->generate();
        $manualContent = $generator->generateManual();

        // Create ZIP file
        $zip = new ZipArchive();
        // Create a temporary file for the ZIP
        $zipFilePath = tempnam(sys_get_temp_dir(), 'graphql_');
        if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Could not create ZIP file.");
        }

        $databaseConfiguration = file_get_contents(dirname(__DIR__) . "/inc.graphql-resources/database.php");

        // Replace database configuration placeholders with actual values
        $databaseConfiguration = setDatabaseConfiguration($application, $databaseConfiguration);

        $zip->addFromString('database.php', $databaseConfiguration);
        $zip->addFromString('auth.php', file_get_contents(dirname(__DIR__) . "/inc.graphql-resources/auth.php"));

        // Add generated code file
        $zip->addFromString('graphql.php', $generatedCode);
        // Add manual content file
        $zip->addFromString('manual.md', $manualContent);

        // Bonus
        $zip->addFile(dirname(__DIR__) . "/inc.lib/composer.phar", "composer.phar");

        $zip->close();
        // Send the ZIP file as a download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="graphql.zip"');
        header('Content-Length: ' . filesize($zipFilePath));
        readfile($zipFilePath);
        // Delete the temporary file
        unlink($zipFilePath);
        exit();
    }
    exit();
    
} catch (Exception $e) {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}