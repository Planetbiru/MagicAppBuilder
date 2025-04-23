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
    <!-- Login Form Section -->
    <div class="container login-form-container d-flex justify-content-center">
        <div class="card login-form-card">
            <div class="card-body">
                <h5 class="card-title text-center mb-4">Login</h5>
                <!-- Login Form -->
                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                        <a href="#" class="text-decoration-none">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
