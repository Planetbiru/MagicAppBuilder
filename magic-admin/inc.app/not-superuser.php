<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Access to MagicAppBuilder</title>
    <link rel="stylesheet" type="text/css" href="../lib.assets/bootstrap/css/bootstrap.min.css">
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
                <h5 class="card-title text-center mb-4">No Access</h5>
                <!-- Login Form -->
                <form action="login.php" method="POST">
                    <p>You have no access to this page.</p>
                    <button type="button" class="btn btn-primary w-100 mt-3" onclick="window.location='logout.php'">Logout</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
