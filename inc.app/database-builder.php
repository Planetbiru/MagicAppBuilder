<?php

use AppBuilder\AppInstaller;
use AppBuilder\Entity\EntityApplicationUser;
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

    $databaseBuilder = new PicoDatabase($databaseConfigBuilder, null, function($sql){
        error_log($sql);
    });
    try
    {
        $databaseBuilder->connect();
    }
    catch(Exception $e)
    {
    }
    
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
                    
            $user = new EntityApplicationUser(null, $databaseBuilder);
            $user->setUsername("administrator");
            $user->setName("Administrator");
            $password = 'administrator';
            $hash = hash('sha1', $password);
            $hash = hash('sha1', $hash);
            $user->setPassword($hash);    
            $user->setLastResetPassword($now);
            $user->setTimeCreate($now);
            $user->setTimeEdit($now);
            $user->setIpCreate($_SERVER['REMOTE_ADDR']);
            $user->setIpEdit($_SERVER['REMOTE_ADDR']);
            $user->setActive(true);
            $user->insert();
                        
            $userUpdate = new EntityApplicationUser(null, $databaseBuilder);
            $userUpdate
                ->setApplicationUserId($user->getApplicationUserId())
                ->setAdminCreate($user->getApplicationUserId())
                ->setAdminEdit($user->getApplicationUserId())
                ->update();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }


    }
}
