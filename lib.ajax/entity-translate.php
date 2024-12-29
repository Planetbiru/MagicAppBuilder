<?php

use AppBuilder\Util\Error\ErrorChecker;
use MagicObject\Language\PicoEntityLanguage;
use MagicObject\MagicObject;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Util\PicoIniUtil;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputPost = new InputPost();
if($inputPost->getUserAction() == 'get')
{
    try
    {
        $baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
        $filter = $inputPost->getFilter();
        $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
        $baseEntity = str_replace("\\\\", "\\", $baseEntity);
        $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));
        $targetLanguage = $inputPost->getTargetLanguage();
        $allQueries = [];

        if($inputPost->getEntityName())
        {
            
            $entityName = $inputPost->getEntityName();
            $className = "\\".$baseEntity."\\".$entityName;
            $entityName = trim($entityName);
            $path = $baseDir."/".$entityName.".php";

            $pathTrans = $appConfig->getApplication()->getBaseApplicationDirectory()."/".$appConfig->getApplication()->getBaseLanguageDirectory()."/".$targetLanguage."/Entity/".$entityName.".ini";
            $langs = new MagicObject();
            if(file_exists($pathTrans))
            {
                $langs->loadIniFile($pathTrans);
            }
            
            if(file_exists($path))
            {
                
                $return_var = ErrorChecker::errorCheck($cacheDir, $path);
                
                if($return_var == 0)
                {  
                    include_once $path;
                    $entity = new $className(null);
                    $entityLabel = new PicoEntityLanguage($entity);
                    $list = $entityLabel->propertyList(true);
                    $response = [];
                    foreach($list as $key)
                    {
                        $original = $entityLabel->get($key);
                        $translated = $langs->get($key);

                        if($translated == null)
                        {
                            $translated = $original;
                            $response[] = array('original'=>$original, 'translated'=>$translated, 'propertyName'=>$key);
                        }  
                        else if($filter == 'all') 
                        {
                            $response[] = array('original'=>$original, 'translated'=>$translated, 'propertyName'=>$key);
                        }
                    }
                    ResponseUtil::sendJSON($response);
                    exit();
                }
            }
        }
    }
    catch(Exception $e)
    {
        // do nothing
        error_log($e->getMessage());
    }
    ResponseUtil::sendJSON([]);
}
if($inputPost->getUserAction() == 'set')
{
    $entityName = $inputPost->getEntityName();
    $translated = $inputPost->getTranslated();
    $propertyNames = $inputPost->getPropertyNames();
    $targetLanguage = $inputPost->getTargetLanguage();
    $keys = explode("|", $propertyNames);
    $values = explode("\n", str_replace("\r", "", $translated));
    $keysLength = count($keys);
    
    while(count($values) > $keysLength)
    {
        unset($values[count($values) - 1]);
    }

    $valuesLength = count($values);
    
    while(count($keys) > $valuesLength)
    {
        unset($keys[count($keys) - 1]);
    }
    $translatedLabel = array_combine($keys, $values);

    try
    {
        $baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
        $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
        $baseEntity = str_replace("\\\\", "\\", $baseEntity);
        $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));
        
        $allQueries = [];

        if($inputPost->getEntityName())
        {
            $path = $appConfig->getApplication()->getBaseApplicationDirectory()."/".$appConfig->getApplication()->getBaseLanguageDirectory()."/".$targetLanguage."/Entity/".$entityName.".ini";
            $dir = dirname($path); 
            if(!file_exists($dir))
            {
                mkdir($dir, 0755, true);
            }
            $original = PicoIniUtil::parseIniFile($path);
            foreach($translatedLabel as $key=>$value)
            {
                $original[$key] = $value;
            }
            PicoIniUtil::writeIniFile($original, $path);
        }
    }
    catch(Exception $e)
    {
        // do nothing
    }
}