<?php

use MagicObject\Session\PicoSession;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";
if($builderConfig != null && $builderConfig->getSessions() != null)
{
    $sessionConfig = $builderConfig->getSessions();
    $sessions = new PicoSession($sessionConfig);

    $appCookieMaxLifeTime = $sessionConfig->getMaxLifetime();
    $appCookiePath = $sessionConfig->getCookiePath();
    $appCookieDomain = $sessionConfig->getCookieDomain();
    $appCookieSecure = $sessionConfig->isCookieSecure();
    $appCookieHttpOnly = $sessionConfig->isCookieHttpOnly();
    $appCookieSameSite = $sessionConfig->getCookieSameSite();

    if(!isset($appCookiePath))
    {
        $appCookiePath = "/";
    }
    if(!isset($appCookieMaxLifeTime))
    {
        $appCookieMaxLifeTime = 1440;
    }

    $sessions->setSessionCookieParams(
        $appCookieMaxLifeTime,
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