<?php

require_once dirname(__DIR__) . "/inc.app/auth-core.php";
$loggedIn = false;
if($userLoggedIn === true)
{
    $loggedIn = true;
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode(array(
    "loggedIn" => $loggedIn
));
exit();
