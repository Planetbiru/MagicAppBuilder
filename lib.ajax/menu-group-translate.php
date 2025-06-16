<?php

use MagicApp\AppMenuGroupItem;
use MagicAppTemplate\Entity\App\AppMenuGroupTranslationImpl;
use MagicAppTemplate\Entity\App\AppModuleGroupImpl;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Response\PicoResponse;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

if($inputGet->getUserAction() == 'get')
{
    $languageId = $inputGet->getLanguageId();
    $specs = PicoSpecification::getInstance();
    $sorts = PicoSortable::getInstance()
        ->addSortable(['sortOrder', 'ASC'])
    ;
    $appMenu = new AppModuleGroupImpl(null, $database);
    $menuTranslation = new AppMenuGroupTranslationImpl(null, $database);
    $response = array();
    $langs = new MagicObject();
    
    try
    {
        $translationData = $menuTranslation->findByLanguageId($languageId);
        foreach($translationData->getResult() as $row)
        {
            $langs->set($row->getMenuGroupId(), $row->getName());
        }

        $pageData = $appMenu->findAll($specs, null, $sorts);
        foreach($pageData->getResult() as $menu)
        {
            $key = $menu->getModuleGroupId();
            $original = $menu->getName();
            $translated = $langs->get($key);
            if($translated == null)
            {
                $translated = $original;
                $response[] = array(
                    'original' => $original, 
                    'translated' => $translated, 
                    'propertyName' => $key
                );
            }  
            else if($filter == 'all') 
            {
                $response[] = array(
                    'original' => $original, 
                    'translated' => $translated, 
                    'propertyName' => $key
                );
            }
        }
    }
    catch(Exception $e)
    {
        // Do nothing
    }

    PicoResponse::sendJSON($response);
}
else if($inputPost->getUserAction() == 'set')
{
    $translated = $inputPost->getTranslated();
    $propertyName = $inputPost->getTranslated();
    $keys = explode("|", $propertyName);
    $values = explode("\n", $propertyName);
    foreach($values as $index=>$value)
    {
        $key = $keys[$index];
    }
}