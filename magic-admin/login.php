<?php

use AppBuilder\EntityInstaller\EntityAdmin;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/database-builder.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputPost = new InputPost();

$entityAdmin = new EntityAdmin(null, $databaseBuilder);

if($inputPost->getUsername() != null && $inputPost->getPassword() != null)
{
    $userLoggedIn = false;
    try
    {
        $hashPassword = sha1($inputPost->getPassword());
        $entityAdmin->findOneByUsernameAndPassword($inputPost->getUsername(), sha1($hashPassword));
        $userLoggedIn = true;
        $sessions->magicUsername = $inputPost->getUsername();
        $sessions->magicUserPassword = $hashPassword;
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
    else if($entityAdmin->getAdminLevelId() != 'superuser')
    {
        header("Location: ./profile.php");
    }
    else
    {
        header("Location: ./");
    }
}