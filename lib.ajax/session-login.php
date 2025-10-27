<?php

use AppBuilder\EntityInstaller\EntityAdmin;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/database-builder.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputPost = new InputPost();

$entityAdmin = new EntityAdmin();
if(isset($databaseBuilder) && $databaseBuilder->isConnected())
{
    $entityAdmin->currentDatabase($databaseBuilder);
}

$userLoggedIn = false;
if($inputPost->getUsername() != null && $inputPost->getPassword() != null)
{
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
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode(array(
    "loggedIn" => $userLoggedIn
));
exit();