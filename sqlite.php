<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQLite Not Available</title>
    <link rel="stylesheet" href="lib.assets/css/sqlite.css">
</head>
<body>
    <div class="container">
        <div class="message-box">
            <h1>SQLite is Not Available</h1>
            <p>It seems that SQLite is not available on your system. Please make sure that SQLite is properly installed and enabled.</p>
            <?php
            // Mendapatkan semua ekstensi yang aktif di PHP
            $extensions = get_loaded_extensions();
            if(!in_array('pdo_sqlite', $extensions))
            {
                ?>
                <p>To resolve this issue, verify that the <strong>pdo_sqlite</strong> extension is loaded. If it is not, you may need to install or enable it.</p>
                <p>For assistance with setting up SQLite, please refer to the installation documentation or contact your system administrator.</p>
                <p>Follow links: 
                    <br>
                    <a href="https://www.php.net/manual/en/sqlite3.setup.php" target="_blank">https://www.php.net/manual/en/sqlite3.setup.php</a>
                </p>
                <?php
            }
            ?>
        </div>
    </div>
</body>
</html>
