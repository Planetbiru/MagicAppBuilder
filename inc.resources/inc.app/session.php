<?php

use MagicObject\SecretObject;
use MagicObject\Session\PicoSession;

require_once __DIR__ . "/app.php";

$appSessionsConfig = $appConfig->getSessions();
if(!isset($appSessionsConfig))
{
    $appSessionsConfig = new SecretObject();
}

$sessions = new PicoSession($appSessionsConfig);

$appCookieMaxLifetime = $appSessionsConfig->getMaxLifetime();
$appCookiePath = $appSessionsConfig->getCookiePath();
$appCookieDomain = $appSessionsConfig->getCookieDomain();
$appCookieSecure = $appSessionsConfig->isCookieSecure();
$appCookieHttpOnly = $appSessionsConfig->isCookieHttpOnly();
$appCookieSameSite = $appSessionsConfig->getCookieSameSite();

if(!isset($appCookiePath))
{
    $appCookiePath = "/";
}
if(!isset($appCookieMaxLifetime))
{
    $appCookieMaxLifetime = "/";
}


$sessions->setSessionCookieParams(
    $appCookieMaxLifetime,
    $appCookiePath,
    $appCookieDomain,
    $appCookieSecure,
    $appCookieHttpOnly,
    $appCookieSameSite
);

$sessions->setSessionCookieParams($appCookieMaxLifetime, $appCookiePath, $appCookieDomain, $appCookieSecure, $appCookieHttpOnly, $appCookieSameSite);
$sessions->startSession();
