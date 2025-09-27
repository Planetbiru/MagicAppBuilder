<?php

$themeAssetsPath = $appConfig->getAssets();
$directories = $appConfig->getApplication()->getBaseModuleDirectory();

$appDocumentTitle = trim($appLanguage->getIndex() . " | " . $appConfig->getApplication()->getName(), " | ");

?><!DOCTYPE html>
<html lang="<?php echo $currentUser->getLanguageId();?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f8f9fa">
    <title><?php echo $appDocumentTitle;?></title>
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
    <link rel="stylesheet" href="<?php echo $themeAssetsPath;?>css/bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        .container {
            margin-top: 60px;
            max-width: 450px; 
        }
        .card {
            transition: transform .2s;
        }
        .card:hover {
            transform: scale(1.01);
        }
        .btn-block {
            margin-bottom: 10px;
            text-align: left; /* biar label path rata kiri */
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="mb-4 text-center"><?php echo $appLanguage->getIndex();?></h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <?php 
            foreach ($directories as $directory) {
                $dir = ltrim($directory->getPath(), "/");
                $dir = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', $dir)) . "/";
                if($dir == "/")
                {
                    $dir = "";
                }
                $label = $directory->getName();
            ?>
                <a href="<?= htmlspecialchars($dir) ?>" 
                   class="btn btn-primary btn-block">
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php 
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>
