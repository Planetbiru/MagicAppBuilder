<?php

use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputGet;
use MagicObject\SetterGetter;
use MagicObject\Util\PicoIniUtil;
use MagicObject\Util\ValidationUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$inputGet = new InputGet();
if($inputGet->getUserAction() == 'get')
{
    try
    {
        $targetLanguage = $inputGet->getTargetLanguage();
        $path = str_replace("\\", "/", $appConfig->getApplication()->getBaseApplicationDirectory() . "/inc.lang/$targetLanguage" . dirname(dirname($appConfig->getApplication()->baseEntityDataNamespace()))."/validator.ini");
        $filter = $inputGet->getFilter();
        $allQueries = array();

        $langs = new SetterGetter();
        if(file_exists($path))
        {
            $langs->loadData(PicoIniUtil::parseIniFile($path));
        }
        else
        {
            $langs->loadData(array());
        }
        $validatorUtil = new ValidationUtil();
        $reflection = new ReflectionClass($validatorUtil);
        $property = $reflection->getProperty('validationMessageTemplate');
        $property->setAccessible(true); // NOSONAR
        $list = $property->getValue($validatorUtil); // NOSONAR

        $response = array();
        foreach($list as $key=>$value)
        {
            $original = $value;
            $translated = $langs->get($key);
            if($translated == null)
            {
                $translated = $original;
            }
            $response[] = array(
                'original' => $original, 
                'translated' => $translated, 
                'propertyName' => $key
            );
        }
        ResponseUtil::sendJSON($response);
        exit();
    }
    catch(Exception $e)
    {
        // do nothing
        error_log($e->getMessage());
    }
    ResponseUtil::sendJSON([]);
}
else if($inputPost->getUserAction() == 'set')
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
    $values = array_map('trim', $values);
    $translatedLabel = array_combine($keys, $values);

    try
    {

        $path = str_replace("\\", "/", $appConfig->getApplication()->getBaseApplicationDirectory() . "/inc.lang/$targetLanguage" . dirname(dirname($appConfig->getApplication()->baseEntityDataNamespace()))."/validator.ini");
        $dir = dirname($path); 
        if(!file_exists($dir))
        {
            mkdir($dir, 0755, true);
        }
        $original = PicoIniUtil::parseIniFile($path);
        foreach($translatedLabel as $key => $value)
        {
            $original[$key] = $value;
        }
        PicoIniUtil::writeIniFile($original, $path);
        
        ResponseUtil::sendJSON([]);
    }
    catch(Exception $e)
    {
        // do nothing
        ResponseUtil::sendJSON([]);
    }
}
else
{
    ResponseUtil::sendJSON([]);
}