<?php

use MagicApp\Field;
use MagicAppTemplate\Entity\App\AppAdminImpl;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once __DIR__ . "/inc.app/app.php";
require_once __DIR__ . "/inc.app/session.php";

$inputPost = new InputPost();

$currentUser = new AppAdminImpl(null, $database);

if($inputPost->getUsername() != null && $inputPost->getPassword() != null)
{
    $userLoggedIn = false;
    try
    {
        $username = $inputPost->getUsername(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
        $hashPassword = sha1($inputPost->getPassword(PicoFilterConstant::FILTER_DEFAULT, false, false, true));
        $appSpecsLogin = PicoSpecification::getInstance()
            ->addAnd(PicoPredicate::getInstance()->like(PicoPredicate::functionLower(Field::of()->username), strtolower($username)))
            ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->password, sha1($hashPassword)))
            ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->active, true))
            ->addAnd(PicoSpecification::getInstance()
                ->addOr(PicoPredicate::getInstance()->equals(Field::of()->blocked, null))
                ->addOr(PicoPredicate::getInstance()->equals(Field::of()->blocked, false))
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