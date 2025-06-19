<?php

use AppBuilder\AppDatabase;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

if(!$database->isConnected())
{
    ResponseUtil::sendJSON(new stdClass);
    exit();
}

$inputPost = new InputPost();
$tableName = $inputPost->getTableName();

ResponseUtil::sendJSON(AppDatabase::getColumnList($appConfig, $databaseConfig, $database, $tableName));