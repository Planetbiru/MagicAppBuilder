<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Error</title>
    <style>
        body {
        font-family: Arial,sans-serif;
        min-height: 100vh;
        font-size: .85rem;
        }
        .alert {
        margin: 10px 0;
        border-radius: 3px;
        }
        .alert, .sql-error {
        padding: 8px 16px;
        border: 1px solid #e86835;
        background-color: #ffd3c1;
        font-family: "Courier New",Courier,monospace;
        }
    </style>
</head>
<body>
    <div>
        <div class="alert alert-danger">
            <?php
            if(isset($e) && $e instanceof \Exception)
            {
                echo $e->getMessage();
            }
            ?>
        </div>
    </div>
</body>
</html>