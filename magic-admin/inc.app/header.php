<?php

use MagicAdmin\AdminPage;
use MagicObject\SecretObject;

?><!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f8f9fa">
    <title>MagicAppBuilder - <?php echo $currentModule->getModuleTitle();?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome/all.min.css">
    <link rel="stylesheet" href="css/css.min.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
    <link rel="stylesheet" href="vendors/datetime-picker/bootstrap-datetimepicker.min.css">
    <script src="js/MultiSelect.js"></script>
    <script src="vendors/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendors/moment/min/moment.min.js"></script>
    <script src="vendors/datetime-picker/bootstrap-datetimepicker.min.js"></script>
    <script src="vendors/sortable/Sortable.min.js"></script>
    <script src="js/UrlSorter.min.js"></script>
    <script src="js/custom.min.js"></script>

</head>

<body>
    <script src="js/color-mode.min.js"></script>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="button-transparent toggle-sidebar"><i class="fas fa-times"></i></button>
        <h4 class="text-white text-center"><a href="./"><?php echo $appLanguage->getDashboard();?></a></h4>
        <?php
        // Sample JSON data (can be replaced with your own)
        $menuLoader = new SecretObject();
        $jsonData = $menuLoader->loadYamlFile(__DIR__ . "/menu.yml", false, true, true);

        // Call the function to generate the sidebar
        echo AdminPage::generateSidebar($jsonData, basename($_SERVER['PHP_SELF']), $appLanguage);
        ?>
    </div>

    <!-- Main Content -->
    <div class="content">
        <nav class="navbar navbar-expand navbar-light">
            <button class="btn btn-outline-secondary toggle-sidebar"><i class="fas fa-bars"></i></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button"
                            data-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown"
                            id="notificationMenu">
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="messageDropdown" role="button"
                            data-toggle="dropdown">
                            <i class="fas fa-comments"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="messageDropdown"
                            id="messageMenu">
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="accountDropdown" role="button"
                            data-toggle="dropdown">
                            <i class="fas fa-user"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="accountDropdown">
                            <a class="dropdown-item" href="profile.php"><?php echo $appLanguage->getProfile();?></a>
                            <a class="dropdown-item" href="setting.php"><?php echo $appLanguage->getSetting();?></a>
                            <div class="menu-separator"></div>
                            <a class="dropdown-item" href="logout.php"><?php echo $appLanguage->getLogout();?></a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button"
                            data-toggle="dropdown">
                            <i class="fas fa-globe"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="languageDropdown">
                            <?php
                            $appLanguageItems = $appConfig->getLanguages();
                            foreach($appLanguageItems as $appLanguageItem)
                            {
                                if($appLanguageItem->getCode() != null && $appLanguageItem->getName() != null)
                                {
                                    ?>
                                    <a class="dropdown-item<?php echo $currentUser->getLanguageId() == $appLanguageItem->getCode() ? ' item-selected':'';?>" href="set-language.php?language_id=<?php echo $appLanguageItem->getCode();?>"><img src="css/flag/<?php echo $appLanguageItem->getCode();?>.svg" class="language-flag" alt="<?php echo $appLanguageItem->getCode();?>">
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