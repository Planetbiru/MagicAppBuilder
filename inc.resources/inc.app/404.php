<?php
http_response_code(404);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <?php
    require_once __DIR__ . "/error-style.php";
    ?>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <h2>Not Found</h2>
        <p>Oops! The page you are looking for does not exist.</p>
        <p><a href="./">Go to Homepage</a></p>
    </div>
</body>
</html>
