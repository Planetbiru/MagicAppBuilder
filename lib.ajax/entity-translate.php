<?php

use MagicObject\Language\PicoEntityLanguage;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputPost = new InputPost();
try
{
	$baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));
    
    $allQueries = array();

    if($inputPost->getEntityName())
    {
        $entityName = $inputPost->getEntityName();

        $className = "\\".$baseEntity."\\".$entityName;
        $entityName = trim($entityName);
        $path = $baseDir."/".$entityName.".php";

        if(file_exists($path))
        {
            include_once $path;
            $entity = new $className(null);
            $entityLabel = new PicoEntityLanguage($entity);
            $list = $entityLabel->propertyList(true);

            $result = array();
            foreach($list as $prop)
            {
                $result[] = array(
                    'propertyName' => $prop,
                    'original' => $entityLabel->get($prop),
                    'translated' => $entityLabel->get($prop)
                );
            }
            header('Content-type: application/json');
            echo json_encode($result);
        }
    }
}
catch(Exception $e)
{
    // do nothing
}