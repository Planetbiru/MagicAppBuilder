<?php

use MagicObject\Request\InputPost;
use MagicObject\Util\Database\PicoDatabaseUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

require_once dirname(__DIR__) . "/inc.app/database.php";

$inputPost = new InputPost();
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