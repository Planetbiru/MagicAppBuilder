<?php

use MagicApp\Field;
use MagicAppTemplate\Entity\App\AppMenuCacheImpl;
use MagicAppTemplate\Entity\App\AppMenuTranslationImpl;
use MagicAppTemplate\Entity\App\AppModuleImpl;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
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
    $languageId = $inputGet->getTargetLanguage();
    $appMenu = new AppModuleImpl(null, $database);
    $menuTranslation = new AppMenuTranslationImpl(null, $database);
    $langs = new MagicObject();
    $response = array();
    try
    {
        $translationData = $menuTranslation->findByLanguageId($languageId);
        foreach($translationData->getResult() as $row)
        {
            $langs->set($row->getModuleId(), $row->getName());
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
            }
            $response[] = array(
                'original' => $original, 
                'translated' => $translated, 
                'propertyName' => $key
            );
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
    $languageId = $inputPost->getTargetLanguage(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, true, true);
    $translated = $inputPost->getTranslated();
    $propertyName = $inputPost->getPropertyNames();
    $keys = explode("|", $propertyName);
    $values = explode("\n", $translated);
    foreach($values as $index=>$value)
    {
        $key = $keys[$index];
        $menuTranslation = new AppMenuTranslationImpl(null, $database);
        try
        {
            $menuTranslation->findOneByModuleIdAndLanguageId($key, $languageId);
            $menuTranslation->setName(htmlspecialchars($value));
            $menuTranslation->update();
        }
        catch(Exception $e)
        {
            $menuTranslation->setModuleId($key);
            $menuTranslation->setLanguageId($languageId);
            $menuTranslation->setName(htmlspecialchars($value));
            $menuTranslation->insert();
        }
    }
    $menuCache = new AppMenuCacheImpl(null, $database);
    $menuCache->where(
        PicoSpecification::getInstance()
        ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->languageId, $languageId))
    )
    ->delete();
}