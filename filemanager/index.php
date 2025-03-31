<?php
require_once __DIR__ . "/func.php";
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager Sederhana</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div id="container">
    <div id="left-panel">
        <h3>Direktori</h3>
        <ul id="dir-tree">
            <?php
            // Tampilkan tree direktori mulai dari folder root
            echo getDirTree('./');
            ?>
        </ul>
    </div>

    <div id="right-panel">
        <h3>Isi File</h3>
        <div id="file-content"></div>
    </div>
</div>

<script src="../lib.assets/jquery/js/jquery-1.10.2.js"></script>
<script src="assets/script.js"></script>

</body>
</html>
