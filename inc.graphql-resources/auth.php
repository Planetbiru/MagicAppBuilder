<?php

// TODO: Your code here

// Please relpace all codes bellow

@session_start();

if(isset($_SESSION['username']) && isset($_SESSION['password']))
{
    // do nothing
}
else
{
    header('HTTP/1.1 401 Unauthorized', true, 401);
    header('Content-Type', 'application/json; charset=utf-8');
    echo json_encode(['errors' => [['message' => 'Authentication required.']]]);
    exit();
}

