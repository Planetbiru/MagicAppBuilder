<?php

use MagicAppTemplate\Entity\App\AppMenuTranslationImpl;
use MagicAppTemplate\Entity\App\AppModuleImpl;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Response\PicoResponse;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

$specs = PicoSpecification::getInstance();
$sorts = PicoSortable::getInstance()
    ->addSortable(['moduleGroup.sortOrder', 'ASC'])
    ->addSortable(['sortOrder', 'ASC'])
;

$inputGet = new InputGet();
$inputPost = new InputPost();

if($inputGet->getUserAction() == 'get')
{
    $languageId = $inputGet->getLanguageId();
    $appMenu = new AppModuleImpl(null, $database);
    $menuTranslation = new AppMenuTranslationImpl(null, $database);
    $langs = new MagicObject();
    $response = array();
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
            $key = $menu->getModuleId();
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