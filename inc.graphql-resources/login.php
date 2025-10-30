<?php

// TODO: Your code here

// Please relpace all codes bellow

@session_start();
if(isset($_POST) && isset($_POST['username']) && isset($_POST['password']))
{
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['password'] = $_POST['password'];
}

if(isset($_SESSION['username']) && isset($_SESSION['password']))
{
    header('Content-Type', 'application/json; charset=utf-8');
    echo json_encode(['success' => true]);
    exit();
}
else
{
    header('HTTP/1.1 401 Unauthorized', true, 401);
    header('Content-Type', 'application/json; charset=utf-8');
    echo json_encode(['errors' => [['message' => 'Authentication required.']]]);
    exit();
}