<?php

require_once __DIR__ . "/inc.app/platform-check.php";
require_once __DIR__ . "/inc.app/auth-with-form.php";

$workspaceId = isset($activeWorkspace) ? $activeWorkspace->getWorkspaceId() : "";
$applicationId = isset($activeApplication) ? $activeApplication->getApplicationId() : "";
$activeApplicationName = isset($activeApplication) ? $activeApplication->getName() : "";
$builderName = $builderConfig->getApplication()->getName();
$adminLevelId = isset($entityAdmin) ? $entityAdmin->getAdminLevelId() : "";

$pageTitle = isset($activeApplication) && $activeApplication->getName() != "" ? $activeApplication->getName() . " | " . $builderName : $builderName;

function basenameRequestUri($uri)
{
  if(substr($uri, strlen($uri) - 1, 1) == "/")
  {
    return $uri;
  }
  else
  {
    return str_replace("\\", "/", dirname($uri));
  }
}
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
  <meta name="base-asset-url" content="<?php echo basenameRequestUri($_SERVER['REQUEST_URI']);?>">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <link rel="icon" type="image/x-icon" href="favicon.ico" />
  <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
  <link rel="stylesheet" type="text/css" href="lib.assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="lib.assets/cm/lib/codemirror.min.css">
  <link rel="stylesheet" type="text/css" href="lib.assets/css/css.min.css">
  <script type="text/javascript" src="lib.assets/jquery/js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="lib.assets/js/script.min.js"></script>
  <script type="text/javascript" src="lib.assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript" src="lib.assets/js/Editor.min.js"></script>
  <script type="text/javascript" src="lib.assets/cm/lib/codemirror.min.js"></script>
  <script type="text/javascript" src="lib.assets/cm/addon/mode/loadmode.min.js"></script>
  <script type="text/javascript" src="lib.assets/cm/mode/meta.min.js"></script>
  <script type="text/javascript" src="lib.assets/js/Sortable.min.js"></script>
  <link rel="stylesheet" type="text/css" href="lib.assets/css/fontawesome/css/all.min.css">
</head>

<body data-admin-level-id="<?php echo htmlspecialchars($adminLevelId); ?>">
</body>

</html>