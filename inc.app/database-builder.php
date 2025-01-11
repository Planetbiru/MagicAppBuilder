<?php

use AppBuilder\AppInstaller;
use AppBuilder\Entity\EntityAdmin;
use AppBuilder\Entity\EntityAdminLevel;
use AppBuilder\Entity\EntityUserLevel;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Util\Database\PicoDatabaseUtil;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$databaseConfigBuilder = $builderConfig->getDatabase();

if($databaseConfigBuilder != null &&  ($databaseConfigBuilder->getDriver() == PicoDatabaseType::DATABASE_TYPE_SQLITE && $databaseConfigBuilder->getDatabaseFilePath()))
{
    $installed = true;
    if(!file_exists($databaseConfigBuilder->getDatabaseFilePath()))
    {
        $installed = false;
    }

    $databaseBuilder = new PicoDatabase($databaseConfigBuilder);
    
    try
    {
        $databaseBuilder->connect();
        
        if(!$installed)
        {
            try
            {
                $appInstaller = new AppInstaller();

                $sql = $appInstaller->generateInstallerQuery($databaseBuilder, $cacheDir);
                $queries = PicoDatabaseUtil::splitSql($sql);
                try
                {
                    foreach($queries as $query)
                    {
                        $query = $query['query'];
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
