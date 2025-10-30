<?php

// TODO: Your code here

// Please relpace all codes bellow

@session_start();

unset($_SESSION['username']);
unset($_SESSION['password']);

header('Content-Type', 'application/json; charset=utf-8');
echo json_encode(['success' => true]);
exit();