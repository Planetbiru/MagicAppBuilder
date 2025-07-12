<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Util\Database\PicoDatabaseUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

if(!$database->isConnected())
{
    ResponseUtil::sendJSON(new stdClass);
    exit();
}

$inputPost = new InputPost();
if($database->isConnected())
{
    $queries = PicoDatabaseUtil::splitSql($inputPost->getQuery());
    foreach($queries as $query)
    {
        try
        {
            $database->execute($query['query']);
        }
        catch(Exception $e)
        {
            error_log($e->getMessage());
        }
    }
}
ResponseUtil::sendJSON(new stdClass);
exit();