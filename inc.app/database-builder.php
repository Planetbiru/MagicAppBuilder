<?php

use AppBuilder\Entity\EntityUser;
use MagicObject\Database\PicoDatabase;
use MagicObject\Generator\PicoDatabaseDump;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

if($builderConfig->getDatabase() != null)
{
    $databaseConfigBuilder = $builderConfig->getDatabase();
    $databaseBuilder = new PicoDatabase($databaseConfigBuilder);
    try
    {
        $databaseBuilder->connect();
    }
    catch(Exception $e)
    {
        if($e->getCode() == 1049)
        {
            try
            {
                $databaseName = $databaseConfigBuilder->getDatabaseName();
                
                $databaseBuilder->connect(false);
                $databaseBuilder->execute("CREATE DATABASE IF NOT EXISTS $databaseName");
                $databaseBuilder->connect();
                
                $files = glob(dirname(__DIR__)."/inc.lib/classes/AppBuilder/Entity/*.php");
                
                foreach($files as $file)
                {
                    $path = $file;
                    
                    $entityName = basename($path, ".php");
                    include_once $path;
                            
                    $className = "AppBuilder\\Entity\\".$entityName;
                    $entity = new $className(null, $databaseBuilder);
                    $dumper = new PicoDatabaseDump();
        
                    $quertArr = $dumper->createAlterTableAdd($entity);
                    foreach($quertArr as $sql)
                    {
                        if(!empty($sql))
                        {
                            $databaseBuilder->execute($sql);
                        }
                    }
                }
                
                $now = date('Y-m-d H:i:s');
                
                $user = new EntityUser(null, $databaseBuilder);
                $user->setUsername("administrator");
                $user->setName("Administrator");
                $password = 'administrator';
                $hash = hash('sha512', $password);
                $hash = hash('sha512', $hash);
                $user->setPassword($hash);    
                $user->setLastResetPassword($now);
                $user->setTimeCreate($now);
                $user->setTimeEdit($now);
                $user->setIpCreate($_SERVER['REMOTE_ADDR']);
                $user->setIpEdit($_SERVER['REMOTE_ADDR']);
                $user->setActive(true);
                $user->insert();
                            
                $userUpdate = new EntityUser(null, $databaseBuilder);
                $userUpdate
                    ->setUserId($user->getUserId())
                    ->setAdminCreate($user->getUserId())
                    ->setAdminEdit($user->getUserId())
                    ->update();
            }
            catch(Exception $e2)
            {
                exit();
            }
        }
    }
}