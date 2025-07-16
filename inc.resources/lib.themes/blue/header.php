<?php

$themeAssetsPath = $appConfig->getAssets();

$appDocumentTitle = trim($currentModule->getModuleTitle() . " | " . $appConfig->getApplication()->getName(), " | ");

?><!DOCTYPE html>
<html lang="<?php echo $currentUser->getLanguageId();?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f8fbff">
    <title><?php echo $appDocumentTitle;?></title>
    <link rel="stylesheet" href="<?php echo $themeAssetsPath;?>css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $themeAssetsPath;?>css/font-awesome/all.min.css">
    <link rel="stylesheet" href="<?php echo $themeAssetsPath;?>css/css.min.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
    <link rel="stylesheet" href="<?php echo $themeAssetsPath;?>vendors/datetime-picker/bootstrap-datetimepicker.min.css">
    <script src="<?php echo $themeAssetsPath;?>vendors/jquery/jquery-3.2.1.min.js"></script>
    <script src="<?php echo $themeAssetsPath;?>vendors/popper/popper.min.js"></script>
    <script src="<?php echo $themeAssetsPath;?>vendors/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo $themeAssetsPath;?>vendors/moment/min/moment.min.js"></script>
    <script src="<?php echo $themeAssetsPath;?>vendors/datetime-picker/bootstrap-datetimepicker.min.js"></script>
    <script src="<?php echo $themeAssetsPath;?>vendors/sortable/Sortable.min.js"></script>
    <script src="<?php echo $themeAssetsPath;?>js/MultiSelect.js"></script>
    <script src="<?php echo $themeAssetsPath;?>js/PicoTagEditor.min.js"></script>
    <script src="<?php echo $themeAssetsPath;?>js/UrlSorter.min.js"></script>
    <script src="<?php echo $themeAssetsPath;?>js/custom.min.js"></script>
</head>

<body>
    <script src="<?php echo $themeAssetsPath;?>js/color-mode.min.js"></script>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="button-transparent toggle-sidebar"><i class="fas fa-times"></i></button>
            <h4 class="text-white text-center"><a href="index.php"><?php echo $appLanguage->getDashboard();?></a></h4>
        </div>
        <div class="sidebar-menu">
            <?php echo $appMenu;?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <nav class="navbar navbar-expand navbar-light">
            <button class="btn btn-outline-secondary toggle-sidebar"><i class="fas fa-bars"></i></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="javascript:" id="notificationDropdown" role="button"
                            data-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown"
                            id="notificationMenu">
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="javascript:" id="messageDropdown" role="button"
                            data-toggle="dropdown">
                            <i class="fas fa-comments"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="messageDropdown"
                            id="messageMenu">
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="javascript:" id="accountDropdown" role="button"
                            data-toggle="dropdown">
                            <i class="fas fa-user"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="accountDropdown">
                            <a class="dropdown-item" href="profile.php"><?php echo $appLanguage->getProfile();?></a>
                            <div class="menu-separator"></div>
                            <a class="dropdown-item" href="logout.php"><?php echo $appLanguage->getLogout();?></a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="javascript:" id="languageDropdown" role="button"
                            data-toggle="dropdown">
                            <i class="fas fa-globe"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="languageDropdown">
                            <?php
                            $appLanguageList = $appConfig->getLanguages();
                            foreach($appLanguageList as $appLanguageItem)
                            {
                                if($appLanguageItem->getCode() != null && $appLanguageItem->getName() != null)
                                {
                                    ?>
                                    <a class="dropdown-item<?php echo $currentUser->getLanguageId() == $appLanguageItem->getCode() ? ' item-selected':'';?>" href="set-language.php?language_id=<?php echo $appLanguageItem->getCode();?>">
                                    <?php echo $appLanguageItem->getName();?></a>
                                    <?php
                                }
                            }
                            ?>

                        </div>
                    </li>
                    <li class="nav-item">
                        <button class="btn btn-outline-secondary toggle-mode"><i class="fas fa-adjust"></i></button>
                    </li>
                </ul>
            </div>
        </nav>
        <h2><a href="<?php echo basename($_SERVER['PHP_SELF']);?>"><?php echo $currentModule->getModuleTitle();?></a></h2> <!-- Main content title -->