<?php

use AppBuilder\AppInstaller;
use MagicObject\Database\PicoDatabase;
use MagicObject\SecretObject;
use MagicObject\Util\Database\PicoDatabaseUtil;

require_once __DIR__ . "/inc.app/auth.php";

$appInstaller = new AppInstaller();


try
{
    $sql = $appInstaller->generateInstallerQuery($databaseBuilder, $cacheDir);
    $queries = PicoDatabaseUtil::splitSql($sql);
    try
    {
        foreach($queries as $query)
        {
            $query = $query['query'];
            $databaseBuilder->execute($query);
            error_log($query);
        }
    }
    catch(Exception $e)
    {
        error_log($e->getMessage());
    }

    
}
catch(Exception $e)
{
    echo $e->getMessage();
}