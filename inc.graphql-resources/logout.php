<?php

// TODO: Your code here

// Please relpace all codes bellow

require_once __DIR__ . '/sessions.php';

unset($_SESSION['username']);
unset($_SESSION['password']);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true]);
exit();