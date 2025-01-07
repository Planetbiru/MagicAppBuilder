<?php

use AppBuilder\Entity\EntityApplicationUser;
use MagicObject\Request\InputPost;

require_once __DIR__ . "/inc.app/app.php";
require_once __DIR__ . "/inc.app/database-builder.php";
require_once __DIR__ . "/inc.app/sessions.php";

$inputPost = new InputPost();

$entityUser = new EntityApplicationUser(null, $databaseBuilder);

if($inputPost->getUsername() != null && $inputPost->getPassword() != null)
{
    $userLoggedIn = false;
    try
    {
        $hashPassword = sha1($inputPost->getPassword());
        $entityUser->findOneByUsernameAndPassword($inputPost->getUsername(), sha1($hashPassword));
        $userLoggedIn = true;
        $sessions->userId = $inputPost->getUsername();
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
        header("Location: ./");
    }
}