<?php

use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";


$inputPost = new InputPost();
header("Content-type: text/plain");

try
{
	$baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));
    $lines = array();

    if($inputPost->getEntity() != null && $inputPost->countableEntity())
    {
        $inputEntity = $inputPost->getEntity();
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
