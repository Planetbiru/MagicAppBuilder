<?php
use MagicObject\SecretObject;

?><!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MagicAppBuilder - <?php echo $currentModule->getModuleTitle();?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome/all.min.css">
    <link rel="stylesheet" href="css/css.css">
    <link rel="icon" type="image/png" href="favicon.png" />
    <link rel="shortcut icon" type="image/png" href="favicon.png" />
    <link rel="stylesheet" href="vendors/datetime-picker/bootstrap-datetimepicker.css">
    <link rel="stylesheet" href="vendors/fontawesome-free-6.5.2-web/css/all.min.css">
    <script src="js/MultiSelect.js"></script>
    <script src="vendors/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendors/moment/min/moment.min.js"></script>
    <script src="vendors/datetime-picker/bootstrap-datetimepicker.js"></script>
    <script src="vendors/sortable/Sortable.js"></script>
    <script src="js/custom.js"></script>

</head>

<?php

/**
 * Generates an HTML sidebar menu based on a JSON structure and the current active link.
 *
 * This function dynamically generates a sidebar in HTML format. It reads menu data from a provided
 * JSON object, and adds submenu items if available. If the `currentHref` matches any submenu item's href,
 * the respective submenu will be expanded by adding the "show" class to its `collapse` div.
 *
 * @param string $jsonData A JSON-encoded string representing the menu structure, including main items and submenus.
 * @param string $currentHref The href of the current page, used to determine which submenu (if any) should be expanded.
 * 
 * @return string The generated HTML for the sidebar, including the main menu and any expanded submenus.
 */
function generateSidebar($jsonData, $currentHref) // NOSONAR
{
    // Decode JSON data
    $data = json_decode($jsonData, true);
    
    // Start the sidebar HTML structure
    $sidebarHTML = '<ul class="nav flex-column" id="sidebarMenu">';

    // Loop through each main menu item
    foreach ($data['menu'] as $item) {
        $sidebarHTML .= '<li class="nav-item">';
        
        // Link for the main menu item, add collapse toggle if there are submenus
        $sidebarHTML .= '<a class="nav-link" href="' . $item['href'] . '"';
        
        // Add target="_blank" if specified in the JSON (or set default)
        $target = isset($item['target']) ? $item['target'] : '';
        if ($target) {
            $sidebarHTML .= ' target="' . $target . '"';
        }

        if (count($item['submenu']) > 0) {
            $sidebarHTML .= ' data-toggle="collapse" aria-expanded="false"';
        }
        $sidebarHTML .= '><i class="' . $item['icon'] . '"></i> ' . $item['title'] . '</a>'."\r\n";
        
        // Check if there are submenus
        if (count($item['submenu']) > 0) {
            // Check if currentHref matches any of the submenu items' href
            $isActive = false;
            foreach ($item['submenu'] as $subItem) {
                if ($subItem['href'] === $currentHref) {
                    $isActive = true;
                    break;
                }
            }
            
            // Add class "show" if the currentHref matches any submenu item
            $collapseClass = $isActive ? 'collapse show' : 'collapse';
            $sidebarHTML .= '<div id="' . substr($item['href'], 1) . '" class="' . $collapseClass . '">'."\r\n";
            $sidebarHTML .= '<ul class="nav flex-column pl-3">'."\r\n";
            
            // Loop through each submenu item
            foreach ($item['submenu'] as $subItem) {
                $sidebarHTML .= '<li class="nav-item">';
                $sidebarHTML .= '<a class="nav-link" href="' . $subItem['href'] . '"';
                
                // Add target="_blank" for submenu links if specified
                $subTarget = isset($subItem['target']) ? $subItem['target'] : '';
                if ($subTarget) {
                    $sidebarHTML .= ' target="' . $subTarget . '"';
                }

                $sidebarHTML .= '><i class="' . $subItem['icon'] . '"></i> ' . $subItem['title'] . '</a>';
                $sidebarHTML .= '</li>'."\r\n";
            }
            
            $sidebarHTML .= '</ul>'."\r\n";
            $sidebarHTML .= '</div>'."\r\n";
        }

        $sidebarHTML .= '</li>'."\r\n";
    }

    // Close the sidebar HTML structure
    $sidebarHTML .= '</ul>';

    // Return the generated sidebar HTML
    return $sidebarHTML;
}

// Sample JSON data (can be replaced with your own)
$menuLoader = new SecretObject();
$jsonData = $menuLoader->loadYamlFile(__DIR__ . "/menu.yml", false, true, true);

// Call the function to generate the sidebar

?>

<body>
    <script src="js/color-mode.js"></script>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="button-transparent toggle-sidebar"><i class="fas fa-times"></i></button>
        <h4 class="text-white text-center"><a href="./"><?php echo $appLanguage->getDashboard();?></a></h4>
        <?php
        echo generateSidebar($jsonData, basename($_SERVER['PHP_SELF']));
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
                            $languages = $appConfig->getLanguages();
                            foreach($languages as $language)
                            {
                                if($language->getCode() != null && $language->getName() != null)
                                {
                                    ?>
                                    <a class="dropdown-item" href="set-language.php?language_id=<?php echo $language->getCode();?>"><img src="css/flag/<?php echo $language->getCode();?>.svg" class="language-flag" alt="<?php echo $language->getCode();?>">
                                    <?php echo $language->getName();?></a>
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