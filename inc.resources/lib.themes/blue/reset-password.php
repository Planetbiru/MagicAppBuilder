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
    <h2 class="card-title text-center mb-4"><?php echo $appLanguage->getResetPassword();?></h2>
    <?php
    if($resetPasswordForm)
    {
        ?>
        <form action="" method="POST">
            <div class="mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo $appLanguage->getPlaceholderTypePassword();?>" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" id="passwordRepeat" name="passwordRepeat" placeholder="<?php echo $appLanguage->getPlaceholderRetypePassword();?>" required>
            </div>
            <div class="d-flex justify-content-between">
                <div class="form-check">
                </div>
                <button type="button" onclick="window.location='login.php'"><?php echo $appLanguage->getLoginForm();?></button>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-3"><?php echo $appLanguage->getButtonSave();?></button>
        </form>
        <?php
    }
    else
    {
        if($inputGet->getError() == 'reset-password-failed')
        {
            echo '<div class="alert alert-danger" role="alert">' . $appLanguage->getMessageResetPasswordFailed() . '</div>';
        }
        ?>
        <form action="reset-password.php" method="POST">
            <div class="mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="<?php echo $appLanguage->getPlaceholderEnterUsername();?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-3"><?php echo $appLanguage->getButtonSendLink();?></button>
            <div class="d-flex justify-content-between">
                <div class="form-check">
                </div>
                <button type="button" onclick="window.location='login.php'"><?php echo $appLanguage->getLoginForm();?></button>
            </div>
        </form>
        <?php
    }
    ?>
    </div>
</body>
</html>
