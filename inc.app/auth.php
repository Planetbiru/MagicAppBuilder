<?php

use AppBuilder\Entity\EntityApplicationUser;

require_once __DIR__ . "/app.php";
require_once __DIR__ . "/database-builder.php";
require_once __DIR__ . "/sessions.php";

if(isset($databaseBuilder))
{
    $entityUser = new EntityApplicationUser(null, $databaseBuilder);

    $userLoggedIn = false;

    if(isset($sessions->userId) && isset($sessions->userPassword))
    {
        try
        {
            $entityUser->findOneByUsernameAndPassword($sessions->userId, sha1($sessions->userPassword));
            $userLoggedIn = true;
        }
        catch(Exception $e)
        {
            $userLoggedIn = false;
        }
    }

    if(!$userLoggedIn)
    {
        require_once __DIR__ . "/login-form.php";
        exit();
    }
}