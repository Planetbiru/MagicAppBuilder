<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="icon" type="image/ico" href="favicon.ico" />
    <title>404 Not Found</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            font-size: 0.8rem;
        }
        h1 {
            font-size: 1.6rem;
            color: #ff0000;
        }
        p {
            font-size: 1rem;
            color: #333;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>404 Not Found</h1>
    <p>The page or resource you are looking for could not be found.</p>
    <p>
        <a href="<?php echo htmlspecialchars(str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', __DIR__)) . '/'); ?>">
            Return to Homepage
        </a>
    </p>
</body>
</html>
