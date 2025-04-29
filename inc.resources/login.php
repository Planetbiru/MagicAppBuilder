<?php

use MagicAppTemplate\Entity\App\AppAdminImpl;
use MagicObject\Request\InputPost;

require_once __DIR__ . "/inc.app/session.php";

$inputPost = new InputPost();

$currentUser = new AppAdminImpl(null, $database);

if($inputPost->getUsername() != null && $inputPost->getPassword() != null)
{
    $userLoggedIn = false;
    try
    {
        $hashPassword = sha1($inputPost->getPassword());
        $currentUser->findOneByUsernameAndPassword($inputPost->getUsername(), sha1($hashPassword));
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