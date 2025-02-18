<?php

require_once __DIR__ . "/inc.app/sqlite-detector.php";
require_once __DIR__ . "/inc.app/auth-with-form.php";
require_once __DIR__ . "/inc.app/navs.php";

$workspaceId = isset($activeWorkspace) ? $activeWorkspace->getWorkspaceId() : "";
$applicationId = isset($activeApplication) ? $activeApplication->getApplicationId() : "";

?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
  <meta name="workspace-id" content="<?php echo $workspaceId; ?>">
  <meta name="application-id" content="<?php echo $applicationId; ?>">
  <title><?php echo $builderConfig->getApplication()->getName(); ?></title>
  <link rel="icon" type="image/x-icon" href="favicon.ico" />
  <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
  <link rel="stylesheet" type="text/css" href="lib.assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="lib.assets/cm/lib/codemirror.min.css">
  <link rel="stylesheet" type="text/css" href="lib.assets/css/css.min.css">
  <link rel="stylesheet" type="text/css" href="lib.assets/css/fontawesome/css/all.min.css">
  <script type="text/javascript" src="lib.assets/jquery/js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="lib.assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript" src="lib.assets/js/Editor.min.js"></script>
  <script type="text/javascript" src="lib.assets/cm/lib/codemirror.min.js"></script>
  <script type="text/javascript" src="lib.assets/cm/addon/mode/loadmode.min.js"></script>
  <script type="text/javascript" src="lib.assets/cm/mode/meta.min.js"></script>
  <script type="text/javascript" src="lib.assets/js/script.js"></script>
  <script type="text/javascript" src="lib.assets/js/Sortable.min.js"></script>
</head>

<body data-admin-level-id="<?php echo isset($entityAdmin) ? $entityAdmin->getAdminLevelId() : ""; ?>">
</body>

</html>