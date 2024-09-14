<?php

use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

$inputGet = new InputGet();

try
{
	$baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));
    $lines = array();

    if($inputGet->getEntity() != null && $inputGet->countableEntity())
    {
        $inputEntity = $inputGet->getEntity();
        foreach($inputEntity as $entityName)
        {
            $entityName = trim($entityName);
            $path = $baseDir."/".$entityName.".php";
            $entityQueries = array();

            if(file_exists($path))
            {                
                $lines[] = file_get_contents($path);
            }
        }
    }

    echo implode("\n\n", $lines);
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
