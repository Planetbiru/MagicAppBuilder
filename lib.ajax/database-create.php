<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Database\PicoDatabase;
use MagicObject\Request\InputPost;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputPost = new InputPost();

$result = new stdClass;
$result->conneted1 = false;
$result->conneted2 = false;
$result->success = false;
$result->error1 = null;
$result->error2 = null;

if($inputPost->issetTestConnection())
{
    $databaseDriver = $inputPost->getDatabaseDriver();
    $databaseFilePath = $inputPost->getDatabaseFilePath();
    $databasePort = intval($inputPost->getDatabasePort());
    $databaseUsername = $inputPost->getDatabaseUsername();
    $databasePassword = $inputPost->getDatabasePassword();
    $databaseName = $inputPost->getDatabaseName();
    $databaseSchema = $inputPost->getDatabaseSchema();
    $databaseTimeZone = $inputPost->getDatabaseTimeZone();

    $databaseConfig = new SecretObject([
        'driver'=>$databaseDriver,
        'databaseFilePath'=>$databaseFilePath,
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
            $res = $database->query("CREATE DATABASE $databaseName");
            if($res)
            {
                $result->success = true;
            }
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