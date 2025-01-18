<?php

use AppBuilder\AppInstaller;
use AppBuilder\Entity\EntityAdmin;
use AppBuilder\Entity\EntityAdminLevel;
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
                    $databaseBuilder->connect(false);
                    error_log("CREATE DATABASE ".$databaseConfigBuilder->getDatabaseName());
                    $databaseBuilder->query("CREATE DATABASE ".$databaseConfigBuilder->getDatabaseName());
                    $databaseBuilder->disconnect();
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
                        $query = $query['query'];
                        error_log($query);
                        $databaseBuilder->execute($query);
                    }
                }
                catch(Exception $e)
                {
                    error_log($e->getMessage());
                }

                $now = date('Y-m-d H:i:s');
                        
                $admin = new EntityAdmin(null, $databaseBuilder);
                $admin->setUsername("administrator");
                $admin->setName("Administrator");
                $password = 'administrator';
                $hash = hash('sha1', $password);
                $hash = hash('sha1', $hash);
                $admin->setPassword($hash);    
                $admin->setLastResetPassword($now);
                $admin->setAdminLevelId("superuser");
                $admin->setTimeCreate($now);
                $admin->setTimeEdit($now);
                $admin->setIpCreate($_SERVER['REMOTE_ADDR']);
                $admin->setIpEdit($_SERVER['REMOTE_ADDR']);
                $admin->setActive(true);
                $admin->insert();
                            
                $userUpdate = new EntityAdmin(null, $databaseBuilder);
                $userUpdate
                    ->setApplicationUserId($admin->getApplicationUserId())
                    ->setAdminCreate($admin->getApplicationUserId())
                    ->setAdminEdit($admin->getApplicationUserId())
                    ->update();
                    
                
                $userLevel = new EntityAdminLevel(null, $databaseBuilder);
                $userLevel->setAdminLevelId("superuser");
                $userLevel->setName("Super User");
                $userLevel->setSortOrder(1);
                $userLevel->setTimeCreate($now);
                $userLevel->setTimeEdit($now);
                $userLevel->setIpCreate($_SERVER['REMOTE_ADDR']);
                $userLevel->setIpEdit($_SERVER['REMOTE_ADDR']);
                $userLevel->setActive(true);
                $userLevel->insert();
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
