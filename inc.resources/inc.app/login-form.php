<?php

$themeAssetsPath = "lib.themes/$appCurrentTheme/assets/";

require_once __DIR__ . "/default-language.php";

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
    // Show show login form for ajax requests
    header('HTTP/1.1 401 Unauthorized', true, 401);
    require_once dirname(__DIR__) . "/lib.themes/$appCurrentTheme/login-form-ajax.php";
}
else
{
    require_once dirname(__DIR__) . "/lib.themes/$appCurrentTheme/login-form.php";
}