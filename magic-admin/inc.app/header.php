<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MagicAppBuilder - <?php echo $currentModule->getModuleTitle();?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome/all.min.css">
    <link rel="stylesheet" href="css/css.css">
    <link rel="shortcut icon" href="css/favicon.png" type="image/png">
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

function generateSidebar($jsonData, $currentHref) {
    // Decode JSON data
    $data = json_decode($jsonData, true);
    
    // Start the sidebar HTML structure
    $sidebarHTML = '<ul class="nav flex-column" id="sidebarMenu">';

    // Loop through each main menu item
    foreach ($data['menu'] as $item) {
        $sidebarHTML .= '<li class="nav-item">';
        
        // Link for the main menu item, add collapse toggle if there are submenus
        $sidebarHTML .= '<a class="nav-link" href="' . $item['href'] . '"';
        if (count($item['submenu']) > 0) {
            $sidebarHTML .= ' data-toggle="collapse" aria-expanded="false"';
        }
        $sidebarHTML .= '><i class="' . $item['icon'] . '"></i> ' . $item['title'] . '</a>';
        
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
            $sidebarHTML .= '<div id="' . substr($item['href'], 1) . '" class="' . $collapseClass . '">';
            $sidebarHTML .= '<ul class="nav flex-column pl-3">';
            
            // Loop through each submenu item
            foreach ($item['submenu'] as $subItem) {
                $sidebarHTML .= '<li class="nav-item">';
                $sidebarHTML .= '<a class="nav-link" href="' . $subItem['href'] . '"><i class="' . $subItem['icon'] . '"></i> ' . $subItem['title'] . '</a>';
                $sidebarHTML .= '</li>';
            }
            
            $sidebarHTML .= '</ul>';
            $sidebarHTML .= '</div>';
        }

        $sidebarHTML .= '</li>';
    }

    // Close the sidebar HTML structure
    $sidebarHTML .= '</ul>';

    // Return the generated sidebar HTML
    return $sidebarHTML;
}


// Sample JSON data (can be replaced with your own)
$jsonData = '{
    "menu": [
        {
            "title": "Dashboard Home",
            "icon": "fas fa-tachometer-alt",
            "href": "index.php",
            "submenu": []
        },
        {
            "title": "Master",
            "icon": "fas fa-folder",
            "href": "#submenu1",
            "submenu": [
                {
                    "title": "Application",
                    "icon": "fas fa-file-alt",
                    "href": "application.php"
                },
                {
                    "title": "Application Group",
                    "icon": "fas fa-file-alt",
                    "href": "application-group.php"
                },
                {
                    "title": "Workspace",
                    "icon": "fas fa-file-alt",
                    "href": "workspace.php"
                },
                {
                    "title": "Admin",
                    "icon": "fas fa-file-alt",
                    "href": "admin.php"
                }
            ]
        },
        {
            "title": "Role",
            "icon": "fas fa-folder",
            "href": "#submenu2",
            "submenu": [
                {
                    "title": "Admin Workspace",
                    "icon": "fas fa-file-alt",
                    "href": "admin-workspace.php"
                }
            ]
        },
        {
            "title": "Reference",
            "icon": "fas fa-folder",
            "href": "#submenu3",
            "submenu": [
                {
                    "title": "Administrator Level",
                    "icon": "fas fa-file-alt",
                    "href": "admin-level.php"
                }
            ]
        }
    ]
}';

// Call the function to generate the sidebar

?>

<body>
    <script src="js/color-mode.js"></script>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="button-transparent toggle-sidebar"><i class="fas fa-times"></i></button>
        <!-- Button to toggle sidebar -->
        <h4 class="text-white text-center"><a href="./">Dashboard</a></h4> <!-- Sidebar title -->
        <?php
        echo generateSidebar($jsonData, basename($_SERVER['PHP_SELF']));
        ?>
    </div>

    <!-- Main Content -->
    <div class="content">
        <nav class="navbar navbar-expand navbar-light"> <!-- Navbar at the top -->
            <button class="btn btn-outline-secondary toggle-sidebar"><i class="fas fa-bars"></i></button>
            <!-- Button to toggle sidebar -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto"> <!-- Menu on the right side of the navbar -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button"
                            data-toggle="dropdown">
                            <i class="fas fa-bell"></i> <!-- Notification icon -->
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown"
                            id="notificationMenu">
                            <!-- Notifications will be populated by JavaScript -->
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="messageDropdown" role="button"
                            data-toggle="dropdown">
                            <i class="fas fa-comments"></i> <!-- Message icon -->
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="messageDropdown"
                            id="messageMenu">
                            <!-- Messages will be populated by JavaScript -->
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="accountDropdown" role="button"
                            data-toggle="dropdown">
                            <i class="fas fa-user"></i> <!-- Account icon -->
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="accountDropdown">
                            <a class="dropdown-item" href="#">Profile</a> <!-- Profile item -->
                            <a class="dropdown-item" href="#">Settings</a> <!-- Settings item -->
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button"
                            data-toggle="dropdown">
                            <i class="fas fa-globe"></i> <!-- Language selection icon -->
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="languageDropdown">
                            <a class="dropdown-item" href="#"><img src="css/id.svg" class="language-flag" alt="ID">
                                Bahasa Indonesia</a> <!-- Language option -->
                            <a class="dropdown-item" href="#"><img src="css/us.svg" class="language-flag" alt="EN">
                                English</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <button class="btn btn-outline-secondary toggle-mode"><i class="fas fa-adjust"></i></button>
                        <!-- Button to toggle mode -->
                    </li>
                </ul>
            </div>
        </nav>
        <h2><?php echo $currentModule->getModuleTitle();?></h2> <!-- Main content title -->