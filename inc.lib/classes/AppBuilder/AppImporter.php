<?php

namespace AppBuilder;

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\FileDirUtil;
use Exception;
use MagicObject\Database\PicoDatabase;
use MagicObject\Exceptions\NoRecordFoundException;
use MagicObject\SecretObject;

/**
 * Class AppImporter
 *
 * Responsible for importing an application configuration from a YAML file
 * into the system. It reads application metadata, constructs an `EntityApplication`,
 * and persists it into the database if it does not already exist.
 */
class AppImporter
{
    /**
     * Database connection instance used for persistence operations.
     *
     * @var PicoDatabase
     */
    private $databaseBuilder;

    /**
     * AppImporter constructor.
     *
     * @param PicoDatabase $databaseBuilder The database handler instance.
     */
    public function __construct($databaseBuilder)
    {
        $this->databaseBuilder = $databaseBuilder;
    }

    /**
     * Imports application configuration from a YAML file and saves it to the database.
     *
     * If the application already exists (based on application ID and workspace ID), it will not be inserted again.
     *
     * @param string $yml Path to the YAML file containing the application configuration.
     * @param string $dir The root directory of the application project.
     * @param string $workspaceId The ID of the workspace to associate with this application.
     * @param string $author The author name to be saved with the application.
     * @param string $adminId The ID of the admin performing the import.
     * @return EntityApplication The imported or existing application entity.
     */
    public function importApplication($yml, $dir, $workspaceId, $author, $adminId)
    {
        $config = new SecretObject(null);
        $config->loadYamlFile($yml, false, true, true);
        $app = $config->getApplication();
        if (!isset($app)) {
            $app = new SecretObject();
        }

        $now = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'];

        $applicationId = $app->getId();
        $applicationName = $app->getName();
        $projectDirectory = FileDirUtil::normalizePath($dir);
        $applicationDirectory = $app->getBaseApplicationDirectory();
        $applicationArchitecture = $app->getArchitecture();
        $applicationDescription = $app->getDescription();

        $url = $app->getBaseApplicationUrl();
        if (!isset($url) || strpos($url, '://') === false) {
            $url = "http://" . $_SERVER['SERVER_NAME'] . "/" . basename(rtrim($applicationDirectory, '/'));
        }

        $application = new EntityApplication(null, $this->databaseBuilder);

        try {
            $application->findOneByApplicationIdAndWorkspaceId($applicationId, $workspaceId);
        } catch (NoRecordFoundException $e) {
            $application->setApplicationId($applicationId);
            $application->setName($applicationName);
            $application->setDescription($applicationDescription);
            $application->setProjectDirectory($projectDirectory);
            $application->setBaseApplicationDirectory($applicationDirectory);
            $application->setUrl($url);
            $application->setArchitecture($applicationArchitecture);
            $application->setAuthor($author);
            $application->setAdminId($adminId);
            $application->setWorkspaceId($workspaceId);
            $application->setAdminCreate($adminId);
            $application->setAdminEdit($adminId);
            $application->setTimeCreate($now);
            $application->setTimeEdit($now);
            $application->setIpCreate($ip);
            $application->setIpEdit($ip);
            $application->setActive(true);
            $application->insert();
        } catch (Exception $e) {
            // Silently fail on any unexpected exception (not recommended in production).
        }

        return $application;
    }
}
