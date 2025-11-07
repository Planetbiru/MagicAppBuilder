<?php

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    header('Localtion: ./'.basename(__FILE__, '.php'));
    exit();
}

$_GET['action'] = 'update';

require_once __DIR__ . '/settings.php';