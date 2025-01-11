<?php

use AppBuilder\Entity\EntityAdmin;

require_once __DIR__ . "/app.php";
require_once __DIR__ . "/database-builder.php";
require_once __DIR__ . "/sessions.php";

if(isset($databaseBuilder))
{
    $entityAdmin = new EntityAdmin(null, $databaseBuilder);
    $userLoggedIn = false;
    if(isset($sessions->adminId) && isset($sessions->userPassword))
    {
        try
        {
            $entityAdmin->findOneByUsernameAndPassword($sessions->adminId, sha1($sessions->userPassword));
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