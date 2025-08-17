<?php

require_once dirname(__DIR__) . "/inc.app/auth.php";

$notificationConfig = $builderConfig->getNotification();

$notifyUrl = $notificationConfig->getHttpUrl();
$token = $notificationConfig->getAuthToken();
$requetTimeout = (int) $notificationConfig->getHttpRequestTimeout();

// Only proceed if notifications are enabled
if (!$notificationConfig->isEnabled()) {
    echo json_encode([
        'response_code' => '001',
        'response_text' => 'Notifications are disabled'
    ]);
    exit();
}

// Only proceed if the token is present
if (empty($token)) {
    echo json_encode([
        'response_code' => '001',
        'response_text' => 'Notification token is not set'
    ]);
    exit();
}

$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true);

/*
$data = [
    'title' => 'Test Notification',
    'message' => 'This is a test notification message.',
    'icon' => 'https://example.com/icon.png',
    'url' => 'https://example.com',
    'data' => [
        
        'applicationId' => 'magicappbuilder',
        'actions' => [
            ['action' => 'call-function', 'function' => 'loadAllResources', 'parameters'=>[], 'title' => 'Load Resources'],
            ['action' => 'open-url', 'url' => 'https://example.com/resources', 'title' => 'Open Resources'],
            ['action' => 'dismiss', 'title' => 'Dismiss']
        ]  
    ],
    'type' => 'system',
    'meta' => ['priority'=>'high']
];
*/

header("Content-Type: application/json; charset=UTF-8");

// Check if JSON decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {

    echo json_encode([
        'response_code' => '001',
        'response_text' => 'Invalid JSON format'
    ]);
    exit();
}


// Validate the data
if (empty($data['data']) || !is_array($data['data'])) {
    echo json_encode([
        'response_code' => '001',
        'response_text' => 'Invalid data format'
    ]);
    exit();
}

$ch = curl_init($notifyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);

// Set timeout options
curl_setopt($ch, CURLOPT_TIMEOUT, $requetTimeout);        
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $requetTimeout); 

$res = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);


if ($err)
{
    echo json_encode([
        'response_code' => '002',
        'response_text' => 'cURL Error: ' . $err
    ]);
} 
else if (empty($res))
{
    echo json_encode([
        'response_code' => '003',
        'response_text' => 'No response from server'
    ]);
} 
else if (strpos($res, 'error') !== false)
{
    echo json_encode([
        'response_code' => '004',
        'response_text' => 'Error in response: ' . $res
    ]);
}
else 
{
    echo json_encode([
        'response_code' => '000',
        'response_text' => 'Notification sent successfully',
        'data' => json_decode($res, true)
    ]);
}
exit();
