<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="favicon.png" />
    <link rel="shortcut icon" type="image/png" href="favicon.png" />
    <title>Reset Password MagicAppBuilder</title>
    <link rel="stylesheet" type="text/css" href="lib.assets/bootstrap/css/bootstrap.min.css">
    <style>
        .container.login-form-container {
            height: 100vh;
        }

        @media screen and (min-height:342px) {
            .container.login-form-container {
                align-items: center !important;
            }
        }

        @media screen and (max-height:341px) {
            .card.login-form-card {
                border-color: transparent;
            }
        }

        .card.login-form-card {
            width: 100%;
            max-width: 400px;
        }
        pre{
            border: 1px solid #EEEEEE;
            background-color: #FAFAFA;
            padding: 16px 16px;
        }
    </style>
</head>

<body>

    <!-- Reset Password Section -->
    <div class="container login-form-container d-flex justify-content-center">
        <div class="card login-form-card">
            <div class="card-body">
                <h5 class="card-title text-center mb-4">Reset Password</h5>
                <!-- Reset Password -->
                <form action="reset-password.php" method="POST">
                    <div class="mb-3">
                        <p>To reset the password, edit the <code>reset-password.yml</code> file located in the <code>inc.cfg</code> directory. Set the username and password in it, as shown in the example below:</p>
                        <pre>
passwordToReset:
  - 
    username: "username1"
    password: "new-password-1"
  - 
    username: "username2"
    password: "new-password-2"</pre>

                        <p>After you set the new passwords, click "Reset Password" button bellow. If the username and password are not deleted automatically, don't forget to delete them after you reset the password.</p>

                    </div>


                    <button type="submit" class="btn btn-primary w-100 mt-3" name="reset_password" value="reset_password">Reset Password</button>
                    <button type="button" class="btn btn-primary w-100 mt-2" onclick="window.location='./'">Login Form</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>