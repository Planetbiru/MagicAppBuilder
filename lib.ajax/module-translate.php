<?php

use MagicObject\MagicObject;
use MagicObject\Request\InputPost;
use MagicObject\Util\PicoArrayUtil;
use MagicObject\Util\PicoIniUtil;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputPost = new InputPost();

function getKeys($path)
{
    $result = array();
    $content = file_get_contents($path);
    $p2 = 0;
    do{
        $p1 = strpos($content, '$appLanguage->', $p2);
        if($p1 !== false)
        {
            $p2 = strpos($content, '(', $p1);
            $sub = substr($content, $p1 + 17, $p2 - $p1 - 17);
            $sub = PicoStringUtil::snakeize($sub);
            $result[] = $sub;
        }
        else
        {
            $p2 = false;
        }
    }
    while($p1 !== false && $p2 !== false);
    return $result;
}

if($inputPost->getUserAction() == 'get')
{
    $allKeys = array();
    $response = array();
    try
    {
        $baseDir = $appConfig->getApplication()->getBaseApplicationDirectory();
        $targetLanguage = $inputPost->getTargetLanguage();
        $filter = $inputPost->getFilter();

        if($inputPost->countableModules())
        {
            foreach($inputPost->getModules() as $module)
            {
                $module = trim($module);
                $path = $baseDir."/".$module;
                if(file_exists($path))
                {  
                    $keys = getKeys($path); 
                    $allKeys = array_merge($allKeys, $keys);
                }
            }
            
            $allKeys = array_unique($allKeys);
            
            $parsed = array();
            foreach($allKeys as $key)
            {
                $camel = PicoStringUtil::camelize($key);
                $parsed[$camel] = PicoStringUtil::camelToTitle($camel);
            }
            
            $parsedLanguage = new MagicObject($parsed);   
            $pathTrans = $appConfig->getApplication()->getBaseApplicationDirectory()."/".$appConfig->getApplication()->getBaseLanguageDirectory()."/$targetLanguage/app.ini";
            $langs = array();
            
            if(file_exists($pathTrans))
            {
                $langs = new MagicObject(PicoIniUtil::parseIniFile($pathTrans));
            }
            
            $keys = array_keys($parsed);
            
            foreach($keys as $key)
            {
                $original = $parsedLanguage->get($key);
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
        }
    }
    catch(Exception $e)
    {
        // do nothing
    }
    $responseStr = json_encode($response);
    header("Content-type: application/json");
    header("Content-length: ".strlen($responseStr));

    echo $responseStr;
}

if($inputPost->getUserAction() == 'set')
{
    $allKeys = array();
    $response = array();
    
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

    foreach($keys as $i=>$key)
    {
        $keys[$i] = PicoStringUtil::snakeize($key);
    }
    
    $baseDir = $appConfig->getApplication()->getBaseApplicationDirectory();
    $targetLanguage = $inputPost->getTargetLanguage();
    $pathTrans = $appConfig->getApplication()->getBaseApplicationDirectory()."/".$appConfig->getApplication()->getBaseLanguageDirectory()."/$targetLanguage/app.ini";

    $storedTranslatedLabel = PicoIniUtil::parseIniFile($pathTrans);
    if(!is_array($storedTranslatedLabel))
    {
        $storedTranslatedLabel = array();
    }
    $storedTranslatedLabel = array_merge($storedTranslatedLabel, $translatedLabel);
    $storedTranslatedLabel = PicoArrayUtil::snakeize($storedTranslatedLabel);
    if(!file_exists(dirname($pathTrans)))
    {
        mkdir(dirname($pathTrans), 0755, true);
    }
    PicoIniUtil::writeIniFile($storedTranslatedLabel, $pathTrans);
}