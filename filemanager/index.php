<?php
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div id="container">
    <div id="left-panel">
        <h3>Directory</h3>
        <ul id="dir-tree" data-base-directory="./">
        </ul>
    </div>

    <div id="right-panel">
        <h3>File Contents</h3>
        <div id="file-content">
            <div class="media-display image-mode"></div>
            <div class="code text-mode">
                <textarea id="code" name="code"></textarea>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="../lib.assets/cm/lib/codemirror.css">
<script src="../lib.assets/cm/lib/codemirror.js"></script>
<script src="../lib.assets/cm/addon/mode/loadmode.js"></script>
<script src="../lib.assets/cm/mode/meta.js"></script>
<script src="assets/script.js"></script>

</body>
</html>
