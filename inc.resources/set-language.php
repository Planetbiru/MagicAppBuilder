<?php

use MagicApp\UserAction;
use MagicAppTemplate\AppValidatorMessage;
use MagicObject\Exceptions\InvalidValueException;
use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;


require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();

if($inputGet->getLanguageId())
{
    $languageId = $inputGet->getLanguageId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
    if($appConfig->getDevelopmentMode() === true || $appConfig->getDevelopmentMode() === "true")
    {
        $sessions->languageId = $languageId;
    }
    else
    {
        try
        {
            $currentUser->setLanguageId($languageId);
            $currentUser->validate(null, AppValidatorMessage::loadTemplate($currentUser->getLanguageId()));
            $currentUser->update();    
        }
        catch(InvalidValueException $e)
        {
            $currentModule->setErrorMessage($e->getMessage());
            $currentModule->setErrorField($e->getPropertyName());
            $currentModule->setCurrentAction(UserAction::UPDATE);
            $currentModule->setFormData($inputPost->formData());
        }
        catch(Exception $e)
        {
            // Handle exception if needed
        }
    }
}

if(isset($_SERVER['HTTP_REFERER']))
{
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}
header("Location: ./");
