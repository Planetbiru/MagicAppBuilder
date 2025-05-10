<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
    <title><?php echo $appConfig->getApplication()->getName();?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo $themeAssetsPath;?>css/bootstrap.min.css">
    <style>
        .container.login-form-container
        {
            height: 100vh;
        }
        @media screen and (min-height:342px) {
            .container.login-form-container
            {
                align-items: center !important;
            }
        }
        @media screen and (max-height:341px)
        {
            .card.login-form-card
            {
                border-color: transparent;
            }
        }
        .card.login-form-card{
            width: 100%; 
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="container login-form-container d-flex justify-content-center">
        <div class="card login-form-card">
            <div class="card-body">
                <h5 class="card-title text-center mb-4"><?php echo $appLanguage->getResetPassword();?></h5>
                <?php
                if($resetPasswordForm)
                {
                    ?>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="password" class="form-label"><?php echo $appLanguage->getPassword();?></label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo $appLanguage->getPlaceholderEnterPassword();?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label"><?php echo $appLanguage->getRepeatPassword();?></label>
                            <input type="password" class="form-control" id="passwordRepeat" name="passwordRepeat" placeholder="<?php echo $appLanguage->getPlaceholderRetypePassword();?>" required>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div class="form-check">
                            </div>
                            <a href="./" class="text-decoration-none"><?php echo $appLanguage->getLoginForm();?></a>
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
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div class="form-check">
                            </div>
                            <a href="./" class="text-decoration-none"><?php echo $appLanguage->getLoginForm();?></a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-3"><?php echo $appLanguage->getButtonSendLink();?></button>
                    </form>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
