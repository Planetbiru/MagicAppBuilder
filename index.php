<?php

require_once __DIR__ . "/inc.app/sqlite-detector.php";
require_once __DIR__ . "/inc.app/auth-with-form.php";

$workspaceId = isset($activeWorkspace) ? $activeWorkspace->getWorkspaceId() : "";
$applicationId = isset($activeApplication) ? $activeApplication->getApplicationId() : "";
$activeApplicationName = isset($activeApplication) ? $activeApplication->getName() : "";
$builderName = $builderConfig->getApplication()->getName();
$adminLevelId = isset($entityAdmin) ? $entityAdmin->getAdminLevelId() : "";

$pageTitle = isset($activeApplication) ? $activeApplication->getName() . " | " . $builderName : $builderName;
?><!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
  <meta name="workspace-id" content="<?php echo $workspaceId; ?>">
  <meta name="application-id" content="<?php echo $applicationId; ?>">
  <meta name="application-name" content="<?php echo $activeApplicationName; ?>">
  <meta name="builder-name" content="<?php echo $builderName; ?>">
  <meta name="admin-level-id" content="<?php echo $adminLevelId; ?>">
  <title><?php echo $pageTitle; ?></title>
  <link rel="icon" type="image/x-icon" href="favicon.ico" />
  <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
  <link rel="stylesheet" type="text/css" href="lib.assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="lib.assets/cm/lib/codemirror.min.css">
  <link rel="stylesheet" type="text/css" href="lib.assets/css/css.min.css">
  <link rel="stylesheet" type="text/css" href="lib.assets/css/fontawesome/css/all.min.css">
  <script type="text/javascript" src="lib.assets/jquery/js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="lib.assets/js/script.js"></script>
  <script type="text/javascript" src="lib.assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript" src="lib.assets/js/Editor.min.js"></script>
  <script type="text/javascript" src="lib.assets/cm/lib/codemirror.min.js"></script>
  <script type="text/javascript" src="lib.assets/cm/addon/mode/loadmode.min.js"></script>
  <script type="text/javascript" src="lib.assets/cm/mode/meta.min.js"></script>
  <script type="text/javascript" src="lib.assets/js/Sortable.min.js"></script>
</head>

<style>
  .directory-container .form-control {
    padding-right: 1.75rem;
  }

  .directory-container {
    position: relative;
    display: inline-block;
    width: 100%;
  }

  .directory-container::after {
    content: '';
    position: absolute;
    width: 1.75rem;
    height: 1.5rem;
    right: 0.5rem;
    top: 0.5rem;
  }

  .directory-container[data-writeable="true"]::after {
    content: '\2713'; 
    color: green; 
    font-size: 1.2rem;
    text-align: center;
  }

  .directory-container[data-writeable="false"]::after {
    content: '\2717'; 
    color: red; 
    font-size: 1.2rem;
    text-align: center;
  }

  .directory-container[data-loading="true"]::after {
    content: '\2022 \2022 \2022'; 
    color: green; 
    font-size: 1.5rem;
    text-align: center;
    animation: blink 1s infinite;
    white-space: nowrap;
  }

  @keyframes blink {
    0% {
      opacity: 1;
    }
    50% {
      opacity: 0.4;
    }
    100% {
      opacity: 1;
    }
  }
</style>


<body data-admin-level-id="<?php echo $adminLevelId; ?>">
</body>

</html>