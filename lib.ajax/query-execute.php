<?php

use MagicObject\Request\InputPost;
use MagicObject\Util\Database\PicoDatabaseUtil;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

$inputPost = new InputPost();

$queries = PicoDatabaseUtil::splitSql($inputPost->getQuery());
try
{
    foreach($queries as $query)
    {
        $database->execute($query['query']);
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
}