<?php

use MagicApp\Field;
use MagicAppTemplate\AppAccountSecurity;
use MagicAppTemplate\Entity\App\AppAdminImpl;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . "/inc.app/app.php";
require_once __DIR__ . "/inc.app/session.php";

$inputPost = new InputPost();
$inputGet = new InputGet();

/**
 * Builds the content of the email
 *
 * @param string $template The email template
 * @param string $baseUrl The base URL for the reset password link
 * @param MagicObject $admin The admin object containing the admin's information
 * @return string The email content with placeholders replaced
 */
function buildContent($template, $baseUrl, $admin)
{
    $data = [
        'admin_name' => $admin->getName(),
        'reset_password_link' => $baseUrl."?token=" . urlencode($admin->getResetToken()),
    ];

    foreach ($data as $key => $value) {
        $template = str_replace('${' . $key . '}', $value, $template);
    }

    return $template;
}

$resetPasswordForm = false;

if($inputGet->getToken() != null)
{
    $token = $inputGet->getToken(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, true, true);
    if($token != null && $token != "" && strpos($token, ".") !== false)
    {
        $time = explode(".", $token)[1];
    }
    else
    {
        $time = 0;
    }
    if($time > time())   
    {
        $specs = PicoSpecification::getInstance()
        ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->validationCode, hash('sha256', $token)))
        ;
        $admin = new AppAdminImpl(null, $database);
        $admin->findOne($specs);
        if($admin->getAdminId() != null)
        {
            if($inputPost->getPassword() != null && $inputPost->getPassword() != "" 
            && $inputPost->getPasswordRepeat() != null && $inputPost->getPasswordRepeat() != "" 
            && $inputPost->getPassword() == $inputPost->getPasswordRepeat())
            {
                $hashPassword = AppAccountSecurity::generateHash($appConfig, $plainPassword, 1);
                $hashPassword2 = AppAccountSecurity::generateHash($appConfig, $hashPassword, 1);
                $admin->setPassword($hashPassword2);
                $admin->setResetToken(null);
                $admin->setValidationCode(null);
                $admin->update();
                
                $sessions->userPassword = $inputPost->getPassword();
                $sessions->username  = $admin->getUsername();
                
                header("Location: ./index.php");
                exit();
            }
            else
            {
                $resetPasswordForm = true;
                require_once __DIR__ . "/inc.app/reset-password.php"; // NOSONAR
                exit();
            }
        }
    }
    $admin = new AppAdminImpl(null, $database);
}

if($inputPost->getUsername() != null)
{
    $userLoggedIn = false;
    try
    {
        $template = '
        <html>
        <head>
            <title>Password Reset</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { width: 600px; margin: 0 auto; }
                .header { background-color: #f4f4f4; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .button {
                    background-color: #4CAF50;
                    color: white;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Password Reset Request</h1>
                </div>
                <div class="content">
                    <p>Dear ${admin_name},</p>
                    <p>We received a request to reset your password. Click the link below to reset your password:</p>
                    <p><a href="${reset_password_link}" class="button">Reset Password</a></p>
                    <p>If you did not request this, please ignore this email.</p>
                    <p>Best regards,<br>Your App Team</p>
                </div>
            </div>
        </body>
        </html>
        ';

        
        $admin = new AppAdminImpl(null, $database);

        $specs = PicoSpecification::getInstance()
        ->addAnd(PicoPredicate::getInstance()->like(PicoPredicate::functionLower(Field::of()->username), strtolower($inputPost->getUsername(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, true, true))))
        ;
        $admin->findOne($specs);
        $admin->setResetToken(hash('sha256', $admin->getUsername() . time() . $appConfig->getApplication()->getName()) . mt_rand(0, 1000000)."." . strtotime('+1 hour'));
        $admin->setValidationCode(hash('sha256', $admin->getResetToken()));
        $admin->update();
        
        if($appConfig->getResetPassword() != null)
        {
            $resetPasswordConfig = $appConfig->getResetPassword()->getEmail();
            
            if(!class_exists('PHPMailer\PHPMailer\PHPMailer'))
            {
                throw new Exception("Can not send email right now."); // NOSONAR
            }

            //Create an instance; passing `true` enables exceptions
            $mail = new PHPMailer(true);

            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $resetPasswordConfig->getHost();        //Set the SMTP server to send through
            $mail->SMTPAuth   = $resetPasswordConfig->isAuth();         //Enable SMTP authentication
            $mail->Username   = $resetPasswordConfig->getUsername();    //SMTP username
            $mail->Password   = $resetPasswordConfig->getPassword();    //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = $resetPasswordConfig->getPort();        //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            
            $sender = $resetPasswordConfig->issetFrom() ? $resetPasswordConfig->getFrom() : new SecretObject();
            
            // Sender
            $mail->setFrom($sender->getEmail(), $sender->getName());
            
            // Recipients
            $mail->addAddress($admin->getEmail(), $admin->getName());   //Add a recipient

            // Content
            
            $body = buildContent($template, $resetPasswordConfig->getBaseUrl(), $admin);
            $mail->CharSet = 'UTF-8';            
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $resetPasswordConfig->getSubject();
            $mail->Body    = $body;

            $mail->send();
        }
        
    }
    catch(Exception $e)
    {
        $userLoggedIn = false;
        header("Location: reset-password.php?error=reset-password-failed");
    }
    if(!$userLoggedIn)
    {
        require_once __DIR__ . "/inc.app/reset-password.php"; // NOSONAR
        exit();
    }
    else
    {
        header("Location: ./index.php");
    }
}
else
{
    require_once __DIR__ . "/inc.app/reset-password.php"; // NOSONAR
    exit();
}