<?php

require_once __DIR__ . "/auth-core.php";

if(!$userLoggedIn)
{
    $response = [
        "response_code"=>"001",
        "response_text"=>"Require authentication"
    ];

    header("Content-type: application/json");
    echo json_encode($response);
    exit();
}