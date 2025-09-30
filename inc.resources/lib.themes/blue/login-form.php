<?php $themeAssetsPath = $appConfig->getAssets();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
    <title><?php echo $appConfig->getApplication()->getName();?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo $themeAssetsPath;?>css/login.min.css">
</head>
<body>
  <div class="overlay"></div>
  <div class="login-box">
    <h2><?php echo $appLanguage->getLogin();?></h2>
    <form action="login.php" method="POST">
      <input type="text" name="username" placeholder="<?php echo $appLanguage->getPlaceholderEnterUsername();?>" required>
      <input type="password" name="password" placeholder="<?php echo $appLanguage->getPlaceholderEnterPassword();?>" required>
      <button type="submit"><?php echo $appLanguage->getButtonLogin();?></button>
    </form>
    <p><?php echo $appLanguage->getForgotPassword();?> <a href="reset-password.php"><?php echo $appLanguage->getButtonResetPassword();?></a></p>
  </div>
</body>

</html>
