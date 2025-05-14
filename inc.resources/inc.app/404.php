<?php
http_response_code(404);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="icon" type="image/ico" href="favicon.ico" />
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
        <p><a href="<?php echo htmlspecialchars(str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', dirname(__DIR__))) . '/'); ?>">Go to Homepage</a></p>
    </div>
</body>
</html>
