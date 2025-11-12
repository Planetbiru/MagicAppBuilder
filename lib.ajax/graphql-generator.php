<?php

require_once dirname(__DIR__) . "/inc.app/auth.php";

$request = file_get_contents("php://input");
$data = json_decode($request, true);

$language = isset($data['programmingLanguage']) ? $data['programmingLanguage'] : 'php';

if($language === 'java') {
    require_once __DIR__ . "/graphql-generator-java.php";
    exit;
} else if($language === 'php') {
    require_once __DIR__ . "/graphql-generator-php.php";
    exit;
} else {
    http_response_code(400);
    echo json_encode(["error" => "Unsupported programming language"]);
    exit;
}