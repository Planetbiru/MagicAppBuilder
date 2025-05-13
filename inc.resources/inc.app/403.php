<?php
http_response_code(403);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="icon" type="image/ico" href="favicon.ico" />
    <title>403 Forbidden</title>
    <?php
    require_once __DIR__ . "/error-style.php";
    ?>
</head>
<body>
    <div class="container">
        <h1>403</h1>
        <h2>Forbidden</h2>
        <p>Sorry, you don’t have permission to access this page.</p>
        <p><a href="<?php echo htmlspecialchars(str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', dirname(__DIR__))) . '/'); ?>">Go to Homepage</a></p>
    </div>
</body>
</html>