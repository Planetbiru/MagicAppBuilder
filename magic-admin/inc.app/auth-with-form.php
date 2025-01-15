<?php

require_once __DIR__."/auth-core.php";


if(!$userLoggedIn)
{
    require_once __DIR__ . "/login-form.php";
    exit();
}