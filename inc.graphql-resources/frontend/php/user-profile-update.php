<?php

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    header('Location: ./#'.basename(__FILE__, '.php'));
    exit();
}

$_GET['action'] = 'update';

require_once __DIR__ . '/user-profile.php';