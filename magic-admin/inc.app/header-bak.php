<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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

<body>
    <script src="js/color-mode.js"></script>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="button-transparent toggle-sidebar"><i class="fas fa-times"></i></button>
        <!-- Button to toggle sidebar -->
        <h4 class="text-white text-center"><a href="./">Dashboard</a></h4> <!-- Sidebar title -->
        <ul class="nav flex-column" id="sidebarMenu"> <!-- Sidebar menu, populated by JavaScript -->
            <!-- The menu will be populated by JavaScript -->
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fas fa-tachometer-alt"></i> Dashboard Home</a>
                <!-- Main link with icon -->
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#submenu1" data-toggle="collapse"><i class="fas fa-folder"></i> Master</a>
                <!-- Menu with a submenu -->
                <div id="submenu1" class="collapse show"> <!-- Submenu 1 -->
                    <ul class="nav flex-column pl-3">
                        <li class="nav-item">
                            <a class="nav-link" href="application.php"><i class="fas fa-file-alt"></i> Application</a> 
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="application-group.php"><i class="fas fa-file-alt"></i> Application Group</a> 
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="workspace.php"><i class="fas fa-file-alt"></i> Workspace</a>
                            <!-- Level 2 submenu -->
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php"><i class="fas fa-file-alt"></i> Admin</a>
                            <!-- Level 2 submenu -->
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#submenu2" data-toggle="collapse"><i class="fas fa-folder"></i> Role</a>
                <!-- Menu with a submenu -->
                <div id="submenu2" class="collapse"> <!-- Submenu 2 -->
                    <ul class="nav flex-column pl-3">
                        <li class="nav-item">
                            <a class="nav-link" href="admin-workspace.php"><i class="fas fa-file-alt"></i> Admin Workspace</a>
                            <!-- Level 2 submenu -->
                        </li>
                        
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#submenu3" data-toggle="collapse"><i class="fas fa-folder"></i> Reference</a>
                <!-- Menu with a submenu -->
                <div id="submenu2" class="collapse"> <!-- Submenu 2 -->
                    <ul class="nav flex-column pl-3">
                        <li class="nav-item">
                            <a class="nav-link" href="admin-level.php"><i class="fas fa-file-alt"></i> Administrator Level</a>
                            <!-- Level 2 submenu -->
                        </li>
                        
                    </ul>
                </div>
            </li>
        </ul>
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
        <h2>Form</h2> <!-- Main content title -->