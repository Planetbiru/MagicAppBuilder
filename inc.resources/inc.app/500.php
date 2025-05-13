<?php
http_response_code(500);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="icon" type="image/ico" href="favicon.ico" />
    <title>500 Internal Server Error</title>
    <?php
    require_once __DIR__ . "/error-style.php";
    ?>
</head>
<body>
    <div class="container">
        <h1>500</h1>
        <h2>Internal Server Error</h2>
        <p>Oops! Something went wrong on our end. Please try again later.</p>
        <p><a href="./">Go to Homepage</a></p>
    </div>
</body>
</html>
