<?php

use MagicObject\Session\PicoSession;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";
if($builderConfig != null && $builderConfig->getSessions() != null)
{
    $sessionConfig = $builderConfig->getSessions();
    $sessions = new PicoSession($sessionConfig);

    $appCookieMaxLifetime = $sessionConfig->getMaxLifetime();
    $appCookiePath = $sessionConfig->getCookiePath();
    $appCookieDomain = $sessionConfig->getCookieDomain();
    $appCookieSecure = $sessionConfig->isCookieSecure();
    $appCookieHttpOnly = $sessionConfig->isCookieHttpOnly();
    $appCookieSameSite = $sessionConfig->getCookieSameSite();

    if(!isset($appCookiePath))
    {
        $appCookiePath = "/";
    }
    if(!isset($appCookieMaxLifetime))
    {
        $appCookieMaxLifetime = 1440;
    }

    $sessions->setSessionCookieParams(
        $appCookieMaxLifetime,
        $appCookiePath,
        $appCookieDomain,
        $appCookieSecure,
        $appCookieHttpOnly,
        $appCookieSameSite
    );

}
else
{
    $sessions = new PicoSession();
}
$sessions->startSession();