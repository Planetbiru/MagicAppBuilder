<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Database\PicoDatabase;
use MagicObject\Request\InputPost;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";


$inputPost = new InputPost();

$result = new stdClass;
$result->conneted1 = false;
$result->conneted2 = false;
$result->error1 = null;
$result->error2 = null;

if($inputPost->issetTestConnection())
{
    $databaseDriver = $inputPost->getDatabaseDriver();
    $databaseHost = $inputPost->getDatabaseHost();
    $databaseDatabaseFilePath = $inputPost->getDatabaseDatabaseFilePath();
    $databasePort = intval($inputPost->getDatabasePort());
    $databaseUsername = $inputPost->getDatabaseUsername();
    $databasePassword = $inputPost->getDatabasePassword();
    $databaseName = $inputPost->getDatabaseDatabaseName();
    $databaseSchema = $inputPost->getDatabaseDatabaseSchema();
    $databaseTimeZone = $inputPost->getDatabaseTimeZone();

    $databaseConfig = new SecretObject([
        'driver'=>$databaseDriver,
        'databaseFilePath'=>$databaseDatabaseFilePath,
        'host'=>$databaseHost,
        'port'=>$databasePort,
        'username'=>$databaseUsername,
        'password'=>$databasePassword,
        'databaseName'=>$databaseName,
        'databaseSchema'=>$databaseSchema,
        'timeZone'=>$databaseTimeZone
    ]);
    $database = new PicoDatabase($databaseConfig);
    try
    {
        // connecting without database name
        $connetcetd1 = $database->connect(false);
        if($connetcetd1)
        {
            $result->conneted1 = true;
            $database->disconnect();
            try
            {
                $connetcetd2 = $database->connect(true);
                if($connetcetd2)
                {
                    $result->conneted2 = true;
                }
            }
            catch(Exception $e2)
            {
                $result->error2 = $e2->getMessage();
            }
        }
    }
    catch(Exception $e1)
    {
        $result->error1 = $e1->getMessage();
    }
}

ResponseUtil::sendJSON($result);