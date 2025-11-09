<?php

// TODO: Your code here

// Please relpace all codes bellow

require_once __DIR__ . '/sessions.php';
require_once __DIR__ . '/database.php';

$appAdmin = array(
    'admin_id' => null,
    'username' => null,
    'name' => null,
    'email' => null,
    'phone' => null,
    'admin_level_id' => null
);

if(isset($_SESSION['username']) && isset($_SESSION['password']))
{
    $username = $_SESSION['username'];
    $password = $_SESSION['password'];

    try {
        // Fetch user by username
        $stmt = $db->prepare("SELECT * FROM admin WHERE username = :username");
        $stmt->execute(array(':username' => $username));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if ($user && sha1($password) === $user['password']) {
            $appAdmin['admin_id'] = $user['admin_id'];
            $appAdmin['username'] = $user['username'];
            $appAdmin['name'] = $user['name'];
            $appAdmin['email'] = $user['email'];
            $appAdmin['phone'] = $user['phone'];
            $appAdmin['admin_level_id'] = $user['admin_level_id'];
        }
        else
        {
            header('HTTP/1.1 401 Unauthorized', true, 401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['errors' => [['message' => 'Authentication required.']]]);
            exit();
        }
    } catch (Exception $e) {
        // Log the database error, but return a generic auth error to the user
        header('HTTP/1.1 401 Unauthorized', true, 401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['errors' => [['message' => 'Authentication required.']]]);
        exit();
    }
}
else
{
    header('HTTP/1.1 401 Unauthorized', true, 401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['errors' => [['message' => 'Authentication required.']]]);
    exit();
}
