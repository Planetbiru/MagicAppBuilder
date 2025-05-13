<?php

use MagicApp\Field;
use MagicAppTemplate\Entity\App\AppAdminImpl;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\InputPost;

require_once __DIR__ . "/inc.app/app.php";
require_once __DIR__ . "/inc.app/session.php";

$inputPost = new InputPost();

$currentUser = new AppAdminImpl(null, $database);

if($inputPost->getUsername() != null && $inputPost->getPassword() != null)
{
    $userLoggedIn = false;
    try
    {
        $hashPassword = sha1($inputPost->getPassword());
        $appSpecsLogin = PicoSpecification::getInstance()
            ->addAnd(PicoPredicate::getInstance()->like(PicoPredicate::functionLower(Field::of()->username), strtolower($inputPost->getUsername())))
            ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->password, sha1($hashPassword)))
            ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->active, true))
            ->addAnd(PicoSpecification::getInstance()
                ->addOr(PicoPredicate::getInstance()->equals(Field::of()->blocked, false))
                ->addOr(PicoPredicate::getInstance()->equals(Field::of()->blocked, null))
            )
        ;
        $currentUser->findOne($appSpecsLogin);
        $userLoggedIn = true;
        $sessions->username = $inputPost->getUsername();
        $sessions->userPassword = $hashPassword;
    }
    catch(Exception $e)
    {
        $userLoggedIn = false;
    }
    if(!$userLoggedIn)
    {
        require_once __DIR__ . "/inc.app/login-form.php";
        exit();
    }
    else
    {
        header("Location: ./index.php");
    }
}
else
{
    require_once __DIR__ . "/inc.app/login-form.php";
    exit();
}