<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Profile</title>
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
    <script src="js/profile.js"></script>
  <style>
    .container-custom {
      max-width: 800px;
    }
  </style>
</head>
<body>

  <div class="container container-custom mt-5">
    <div class="row">
      <div class="col-12">
        <h3><?php echo $currentModule->getModuleTitle();?></h3>
        