<?php

// Runner script for starting the WebSocket server

use AppBuilder\WebSocket\WebSocketServer;

require_once dirname(__DIR__) . "/inc.app/app.php";

// You can change host, port, and session name as needed
$server = new WebSocketServer('127.0.0.1', 8080, 'PHPSESSID');
$server->start();
