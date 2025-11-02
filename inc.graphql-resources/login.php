<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/inc/I18n.php';

@session_start();

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Fetch user by username
        $stmt = $db->prepare("SELECT * FROM admin WHERE username = :username");
        $stmt->execute(array(':username' => $username));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if ($user && sha1(sha1($password)) === $user['password']) {
            // Set session on successful login
            $_SESSION['username'] = $user['username'];
            $_SESSION['password'] = sha1($password); // Storing the hash is more secure

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success' => true));
            exit();
        }
    } catch (Exception $e) {
        // Log the database error, but return a generic auth error to the user
        error_log('Login database error: ' . $e->getMessage());
    }
}

// If we reach here, it means login failed (user not found, password incorrect, or no credentials provided)
header('HTTP/1.1 401 Unauthorized', true, 401);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(array('success' => false, 'message' => $i18n->t('invalid_credentials')));
exit();