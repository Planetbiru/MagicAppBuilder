<?php

require_once __DIR__ . "/auth-core.php";

if(!$userLoggedIn)
{
    $response = array(
        "response_code"=>"001",
        "response_text"=>"Authentication required"
    );

    header("Content-Type: application/json; charset=UTF-8");
    http_response_code(401);
    echo json_encode($response);
    exit();
}