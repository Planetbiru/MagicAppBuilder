<?php

use MagicAppTemplate\Entity\App\AppModuleImpl;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;
use MagicObject\Response\PicoResponse;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";


$specs = PicoSpecification::getInstance();
$sorts = PicoSortable::getInstance()
    ->addSortable(['moduleGroup.sortOrder', 'ASC'])
    ->addSortable(['sortOrder', 'ASC'])
;
$appMenu = new AppModuleImpl(null, $database);
$langs = new MagicObject();
$response = array();
try
{
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