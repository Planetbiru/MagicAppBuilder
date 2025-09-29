<?php

use AppBuilder\AppInstaller;
use AppBuilder\EntityInstaller\EntityAdmin;
use AppBuilder\EntityInstaller\EntityAdminLevel;
use AppBuilder\Util\ChartDataUtil;
use MagicAdmin\Entity\Data\Admin;
use MagicAdmin\Entity\Data\AdminCreated;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Util\Database\PicoDatabaseUtil;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$databaseConfigBuilder = $builderConfig->getDatabase();
$databaseConfigured = false;
$installed = false;
if($databaseConfigBuilder != null)
{
    if($databaseConfigBuilder->getDriver() == PicoDatabaseType::DATABASE_TYPE_SQLITE && $databaseConfigBuilder->getDatabaseFilePath())
    {
        $installed = true;
        if(!file_exists($databaseConfigBuilder->getDatabaseFilePath()))
        {
            $installed = false;
        }
        $databaseBuilder = new PicoDatabase($databaseConfigBuilder);
        $databaseConfigured = true;
    }
    else
    {
        $databaseBuilder = new PicoDatabase($databaseConfigBuilder);
        $databaseConfigured = true;
    }
}

if($databaseConfigured)
{
    try
    {
        if($databaseBuilder->getDatabaseType() != PicoDatabaseType::DATABASE_TYPE_SQLITE)
        {
            try
            {
                $databaseBuilder->connect();
            }
            catch(Exception $e)
            {
                try
                {
                    // Connect to database server without specify database
                    $databaseBuilder->connect(false);
                    
                    // Create database
                    $databaseBuilder->query("CREATE DATABASE ".$databaseConfigBuilder->getDatabaseName());
                    
                    // Disconnect from database server
                    $databaseBuilder->disconnect();
                    
                    // Connect to database server with specify database
                    $databaseBuilder->connect();
                }
                catch(Exception $e)
                {
                    error_log($e->getMessage());
                }
            }
        }
        else
        {
            $databaseBuilder->connect();
        }
        
        // Install MagicAppBuilder
        $appInstaller = new AppInstaller();

        if($databaseBuilder->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_POSTGRESQL)
        {
            $ad = new EntityAdmin(null);
            $tableName = $ad->tableInfo()->getTableName();
            $schemaName = $databaseConfigBuilder->getDatabaseSchema();
            $sql = "SELECT EXISTS (
                SELECT 1
                FROM information_schema.tables
                WHERE table_schema = '$schemaName'  
                AND table_name = '$tableName'
            )";
            $stmt = $databaseBuilder->query($sql);
            $installed = $stmt->rowCount() > 0;
        }
        else if($databaseBuilder->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_MYSQL 
        || $databaseBuilder->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_MARIADB)
        {
            $ad = new EntityAdmin(null);
            $tableName = $ad->tableInfo()->getTableName();
            $schemaName = $databaseConfigBuilder->getDatabaseName();
            $sql = "SELECT COUNT(*)
                FROM information_schema.tables
                WHERE table_schema = '$schemaName' 
                AND table_name = '$tableName';";
            $res = $databaseBuilder->fetch($sql, PDO::FETCH_COLUMN);
            $installed = $res > 0;
        }
        
        if(!$installed && $databaseBuilder->isConnected())
        {
            try
            {
                $sql = $appInstaller->generateInstallerQuery($databaseBuilder);
                $queries = PicoDatabaseUtil::splitSql($sql);
                try
                {
                    foreach($queries as $query)
                    {
                        $sql = $query['query'];
                        $databaseBuilder->execute($sql);
                    }
                }
                catch(Exception $e)
                {
                    error_log($e->getMessage());
                }

                // Add Licenses
                $licensePath = __DIR__ . "/license.sql";
                if(file_exists($licensePath))
                {
                    try
                    {
                        $licenseSql = file_get_contents($licensePath);
                        $queries = PicoDatabaseUtil::splitSql($licenseSql);
                        foreach($queries as $query)
                        {
                            $sql = $query['query'];
                            $databaseBuilder->execute($sql);
                        }
                    }
                    catch(Exception $e)
                    {
                        error_log($e->getMessage());
                    }
                }

                // Create adminId manually
                $adminId = $databaseBuilder->generateNewId();

                $now = date('Y-m-d H:i:s');
                $password = 'administrator';
                $userLevelId = "superuser";
                $hash = hash('sha1', $password);
                $hash = hash('sha1', $hash);
                $ipAddress = $_SERVER['REMOTE_ADDR']; 

                $userLevel = new EntityAdminLevel(null, $databaseBuilder);
                $userLevel->setAdminLevelId($userLevelId);
                $userLevel->setName("Super User");
                $userLevel->setDescription("Administrator with unlimited access");
                $userLevel->setSortOrder(1);
                $userLevel->setAdminCreate($adminId);
                $userLevel->setAdminEdit($adminId);
                $userLevel->setTimeCreate($now);
                $userLevel->setTimeEdit($now);
                $userLevel->setIpCreate($ipAddress);
                $userLevel->setIpEdit($ipAddress);
                $userLevel->setActive(true);
                $userLevel->insert();

                $userLevel = new EntityAdminLevel(null, $databaseBuilder);
                $userLevel->setAdminLevelId("user");
                $userLevel->setName("User");
                $userLevel->setSortOrder(2);
                $userLevel->setAdminCreate($adminId);
                $userLevel->setAdminEdit($adminId);
                $userLevel->setTimeCreate($now);
                $userLevel->setTimeEdit($now);
                $userLevel->setIpCreate($ipAddress);
                $userLevel->setIpEdit($ipAddress);
                $userLevel->setActive(true);
                $userLevel->insert();
                        
                $admin = new EntityAdmin(null, $databaseBuilder);
                $admin->setAdminId($adminId);
                $admin->setUsername("administrator");
                $admin->setName("Administrator");
                $userLevel->setDescription("Administrator with limited access");
                $admin->setPassword($hash);    
                $admin->setLastResetPassword($now);
                $admin->setAdminLevelId($userLevelId);
                $admin->setLanguageId("en");
                $admin->setAdminCreate($adminId);
                $admin->setAdminEdit($adminId);
                $admin->setTimeCreate($now);
                $admin->setTimeEdit($now);
                $admin->setIpCreate($ipAddress);
                $admin->setIpEdit($ipAddress);
                $admin->setActive(true);
                $admin->insert();

                ChartDataUtil::updateChartData(new Admin(null, $databaseBuilder), new AdminCreated(null, $databaseBuilder), date('Ym'));
            }
            catch(Exception $e)
            {
                echo $e->getMessage();
            }
        }
    }
    catch(Exception $e)
    {
        echo $e->getMessage();
    }
}
